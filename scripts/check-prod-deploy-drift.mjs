#!/usr/bin/env node
/**
 * 本番テーマの直書き差分を、FTP アップロード／削除の前に検知して止める。
 *
 * 比較基準: 前回正常デプロイ後の .prod-deploy-baseline.json（パス→SHA-256）
 * 転送対象: ローカル（ビルド後）× FTP_EXCLUDE（micromatch / FTP-Deploy と同系）
 *
 * 参照実装: SATOPO / t2026-08-06wp（WP テンプレ）scripts/check-prod-deploy-drift.mjs
 */

import { createHash } from 'node:crypto';
import { access, mkdir, readFile, readdir, stat, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { Readable, Writable } from 'node:stream';
import { fileURLToPath } from 'node:url';
import { Client } from 'basic-ftp';
import micromatch from 'micromatch';

export const BASELINE_NAME = '.prod-deploy-baseline.json';
export const SYNC_STATE_NAME = '.ftp-deploy-sync-state.json';
export const SCHEMA = 1;

const ALWAYS_EXCLUDE = [
  `**/${BASELINE_NAME}`,
  `**/${SYNC_STATE_NAME}`,
  BASELINE_NAME,
  SYNC_STATE_NAME,
];

export class DriftError extends Error {
  constructor(message, { code = 'undecidable' } = {}) {
    super(message);
    this.name = 'DriftError';
    this.code = code;
  }
}

export function asPosix(relPath) {
  return String(relPath).replaceAll('\\', '/').replace(/^\.\//, '');
}

export function parseExclude(text) {
  const patterns = [];
  for (const raw of String(text).split(/\r?\n/)) {
    const line = raw.trim();
    if (!line || line.startsWith('#')) continue;
    patterns.push(line);
  }
  const seen = new Set();
  const out = [];
  for (const p of [...patterns, ...ALWAYS_EXCLUDE]) {
    if (seen.has(p)) continue;
    seen.add(p);
    out.push(p);
  }
  return out;
}

export function loadExcludeFromEnv(env = process.env) {
  return parseExclude(env.FTP_EXCLUDE || '');
}

/** workflow YAML の `FTP_EXCLUDE: |` ブロックを読む（テスト用） */
export function parseFtpExcludeFromWorkflow(yamlText) {
  const lines = String(yamlText).split(/\r?\n/);
  const start = lines.findIndex((l) => /^\s*FTP_EXCLUDE:\s*\|\s*$/.test(l));
  if (start < 0) {
    throw new DriftError('workflow に FTP_EXCLUDE がありません', { code: 'config' });
  }
  const block = [];
  for (let i = start + 1; i < lines.length; i += 1) {
    const line = lines[i];
    if (/^\S/.test(line) || /^[A-Za-z0-9_]+:/.test(line.trim())) break;
    if (line.trim() === '' && block.length > 0) break;
    // インデント付きの内容行のみ
    if (/^\s+\S/.test(line)) {
      block.push(line.replace(/^\s+/, ''));
    } else if (line.trim() === '') {
      continue;
    } else {
      break;
    }
  }
  return parseExclude(block.join('\n'));
}

export function isExcluded(relPath, patterns) {
  const rel = asPosix(relPath);
  const opts = { dot: true };
  if (micromatch.isMatch(rel, patterns, opts)) return true;
  // ディレクトリとして明示されたパターン用
  if (!rel.endsWith('/') && micromatch.isMatch(`${rel}/`, patterns, opts)) return true;
  return false;
}

export async function sha256File(filePath) {
  const data = await readFile(filePath);
  return createHash('sha256').update(data).digest('hex');
}

export function sha256Buffer(buf) {
  return createHash('sha256').update(buf).digest('hex');
}

export async function listLocalFiles(localDir, patterns) {
  const root = path.resolve(localDir);
  const files = new Map();

  async function walk(absDir, relDir) {
    const entries = await readdir(absDir, { withFileTypes: true });
    for (const ent of entries) {
      const rel = relDir ? `${relDir}/${ent.name}` : ent.name;
      const abs = path.join(absDir, ent.name);
      if (ent.isDirectory()) {
        if (isExcluded(rel, patterns) || isExcluded(`${rel}/`, patterns)) continue;
        await walk(abs, rel);
        continue;
      }
      if (!ent.isFile()) continue;
      if (isExcluded(rel, patterns)) continue;
      files.set(asPosix(rel), await sha256File(abs));
    }
  }

  await walk(root, '');
  return Object.fromEntries([...files.entries()].sort(([a], [b]) => a.localeCompare(b)));
}

export function loadBaseline(data) {
  if (!data || typeof data !== 'object' || Array.isArray(data)) {
    throw new DriftError('基準ファイルの JSON がオブジェクトではありません', { code: 'baseline' });
  }
  if (data.schema !== SCHEMA) {
    throw new DriftError(`基準ファイルの schema が未対応です（期待 ${SCHEMA}）`, { code: 'baseline' });
  }
  const files = data.files;
  if (!files || typeof files !== 'object' || Array.isArray(files) || Object.keys(files).length === 0) {
    throw new DriftError('基準ファイルに files がありません', { code: 'baseline' });
  }
  const out = {};
  for (const [p, digest] of Object.entries(files)) {
    if (typeof p !== 'string' || typeof digest !== 'string' || digest.length !== 64) {
      throw new DriftError(`基準ファイルのエントリが不正です: ${p}`, { code: 'baseline' });
    }
    out[asPosix(p)] = digest.toLowerCase();
  }
  return out;
}

export function makeBaselineDoc(files, commit = process.env.GITHUB_SHA || '') {
  return {
    schema: SCHEMA,
    generatedTime: Date.now(),
    commit,
    files,
  };
}

export function plannedChanges(localFiles, baseline) {
  const overwrite = [];
  const create = [];
  for (const [p, digest] of Object.entries(localFiles)) {
    if (!(p in baseline)) create.push(p);
    else if (baseline[p] !== digest) overwrite.push(p);
  }
  const del = Object.keys(baseline).filter((p) => !(p in localFiles));
  return { overwrite, delete: del, create };
}

/**
 * @typedef {{
 *   readBaseline: () => Promise<Record<string,string>>,
 *   readFile: (rel: string) => Promise<Buffer|null>,
 *   writeBaseline: (doc: object) => Promise<void>,
 *   close?: () => Promise<void>,
 * }} ProdStore
 */

export async function checkDrift(store, localFiles) {
  const baseline = await store.readBaseline();
  const plan = plannedChanges(localFiles, baseline);
  const findings = [];

  console.log(
    `Transfer plan: create=${plan.create.length} overwrite=${plan.overwrite.length} delete=${plan.delete.length} (local=${Object.keys(localFiles).length} baseline=${Object.keys(baseline).length})`,
  );

  for (const p of [...plan.overwrite].sort()) {
    let remote;
    try {
      remote = await store.readFile(p);
    } catch (err) {
      throw toUndecidableReadError(p, err);
    }
    if (remote === null) {
      findings.push({ path: p, reason: '上書き予定だが本番に無い（基準とも不一致の欠落）' });
      continue;
    }
    const digest = sha256Buffer(remote);
    if (digest !== baseline[p]) {
      findings.push({
        path: p,
        reason: `上書き予定: 本番が基準と異なる（baseline=${baseline[p].slice(0, 12)}… actual=${digest.slice(0, 12)}…）`,
      });
    }
  }

  for (const p of [...plan.delete].sort()) {
    let remote;
    try {
      remote = await store.readFile(p);
    } catch (err) {
      throw toUndecidableReadError(p, err);
    }
    if (remote === null) {
      findings.push({ path: p, reason: '削除予定だが本番に無い（基準作成後に消えている）' });
      continue;
    }
    const digest = sha256Buffer(remote);
    if (digest !== baseline[p]) {
      findings.push({
        path: p,
        reason: `削除予定: 本番が基準と異なる（baseline=${baseline[p].slice(0, 12)}… actual=${digest.slice(0, 12)}…）`,
      });
    }
  }

  for (const p of [...plan.create].sort()) {
    let remote;
    try {
      remote = await store.readFile(p);
    } catch (err) {
      throw toUndecidableReadError(p, err);
    }
    if (remote !== null) {
      findings.push({ path: p, reason: '新規配信パスに本番側だけのファイルがある（衝突）' });
    }
  }

  return findings;
}

export async function bootstrapBaseline(store, localFiles, patterns) {
  if (typeof store.listDeployableFiles !== 'function') {
    throw new DriftError('本番側の一覧取得に対応していません', { code: 'undecidable' });
  }

  let remoteNames;
  try {
    remoteNames = await store.listDeployableFiles(patterns);
  } catch (err) {
    if (err instanceof DriftError) throw err;
    const kind = classifyFtpError(err);
    throw new DriftError(`本番側の一覧取得に失敗しました: ${err.message || err}`, { code: kind });
  }
  if (!Array.isArray(remoteNames)) {
    throw new DriftError('本番側の一覧取得結果が不正です', { code: 'undecidable' });
  }

  const localSet = new Set(Object.keys(localFiles));
  const remoteSet = new Set(remoteNames.map(asPosix));
  const findings = [];

  for (const p of [...localSet].sort()) {
    if (!remoteSet.has(p)) {
      findings.push({ path: p, reason: 'ローカルにはあるが本番に無い' });
    }
  }
  for (const p of [...remoteSet].sort()) {
    if (!localSet.has(p)) {
      findings.push({ path: p, reason: '本番側だけに存在する（ローカルの転送対象に無い）' });
    }
  }

  for (const p of [...localSet].sort()) {
    if (!remoteSet.has(p)) continue;
    let remote;
    try {
      remote = await store.readFile(p);
    } catch (err) {
      throw toUndecidableReadError(p, err);
    }
    if (remote === null) {
      findings.push({ path: p, reason: 'ローカルにはあるが本番に無い' });
      continue;
    }
    const actual = sha256Buffer(remote);
    const digest = localFiles[p];
    if (actual !== digest) {
      findings.push({
        path: p,
        reason: `本番とローカルが異なる（local=${digest.slice(0, 12)}… actual=${actual.slice(0, 12)}…）`,
      });
    }
  }

  if (findings.length > 0) {
    const lines = findings.map((f) => `  - ${f.path}: ${f.reason}`).join('\n');
    throw new DriftError(`bootstrap 失敗: 本番とローカルが一致しません:\n${lines}`, {
      code: 'mismatch',
    });
  }
  const doc = makeBaselineDoc(localFiles);
  await store.writeBaseline(doc);
  console.log(`Bootstrap OK: baseline written for ${Object.keys(localFiles).length} files.`);
}

function toUndecidableReadError(relPath, err) {
  if (err instanceof DriftError) return err;
  const kind = classifyFtpError(err);
  if (kind === 'not_found') {
    // readFile 実装が throw せず null を返すのが正。ここに来たら実装バグか曖昧 550
    return new DriftError(`本番ファイルの有無を判定できません (${relPath}): ${err.message || err}`, {
      code: 'undecidable',
    });
  }
  return new DriftError(`本番ファイルを読めません (${relPath}): ${err.message || err}`, {
    code: kind,
  });
}

export function classifyFtpError(err) {
  const msg = String(err?.message || err || '');
  const code = Number(err?.code) || null;
  if (code === 530 || /530|login incorrect|authentication|not logged in/i.test(msg)) {
    return 'auth';
  }
  if (/permission denied|access denied|not allowed|553 /i.test(msg) || code === 553) {
    return 'permission';
  }
  if (code === 550 || /\b550\b/.test(msg)) {
    if (/permission denied|access denied|not allowed/i.test(msg)) return 'permission';
    if (/no such file|not found|failed to open file|does not exist|can't find|cannot find/i.test(msg)) {
      return 'not_found';
    }
    // 曖昧な 550 は「無し」扱いしない
    return 'ftp_error';
  }
  if (/ECONNREFUSED|ENOTFOUND|ETIMEDOUT|ECONNRESET|socket|TLS|SSL|certificate/i.test(msg)) {
    return 'network';
  }
  if (/cwd|directory/i.test(msg) && /fail|550|553/i.test(msg)) {
    return 'cwd';
  }
  return 'ftp_error';
}

export function createLocalStore(rootDir, baselinePath = null) {
  const root = path.resolve(rootDir);
  const baselineFile = baselinePath
    ? path.resolve(baselinePath)
    : path.join(root, BASELINE_NAME);

  return {
    async readBaseline() {
      try {
        await access(baselineFile);
      } catch {
        throw new DriftError(`比較基準がありません: ${baselineFile}`, { code: 'no_baseline' });
      }
      let data;
      try {
        data = JSON.parse(await readFile(baselineFile, 'utf8'));
      } catch (err) {
        throw new DriftError(`比較基準を JSON として読めません: ${err.message}`, {
          code: 'baseline',
        });
      }
      return loadBaseline(data);
    },
    async readFile(relPath) {
      const abs = path.join(root, asPosix(relPath));
      try {
        const st = await stat(abs);
        if (!st.isFile()) return null;
        return await readFile(abs);
      } catch (err) {
        if (err && (err.code === 'ENOENT' || err.code === 'ENOTDIR')) return null;
        throw new DriftError(`本番ミラーを読めません (${relPath}): ${err.message}`, {
          code: 'io',
        });
      }
    },
    async listDeployableFiles(patterns) {
      return Object.keys(await listLocalFiles(root, patterns));
    },
    async writeBaseline(doc) {
      await mkdir(path.dirname(baselineFile), { recursive: true });
      await writeFile(baselineFile, `${JSON.stringify(doc, null, 2)}\n`, 'utf8');
      console.log(`Wrote baseline: ${baselineFile} (${Object.keys(doc.files).length} files)`);
    },
  };
}

export function createFtpStore({
  server,
  username,
  password,
  serverDir,
  secure = false,
  timeout = 120_000,
}) {
  if (!server || !username || !password || !serverDir) {
    throw new DriftError('FTP 接続情報が不足しています', { code: 'config' });
  }
  const remoteRoot = serverDir.endsWith('/') ? serverDir : `${serverDir}/`;
  const client = new Client(timeout);
  let ready;

  async function ensure() {
    if (!ready) {
      ready = (async () => {
        try {
          await client.access({
            host: server,
            user: username,
            password,
            secure,
            secureOptions: secure ? { rejectUnauthorized: true } : undefined,
          });
          await client.cd(remoteRoot);
        } catch (err) {
          const kind = classifyFtpError(err);
          throw new DriftError(`本番 FTP に接続できません: ${err.message || err}`, { code: kind });
        }
      })();
    }
    return ready;
  }

  return {
    async readBaseline() {
      await ensure();
      let raw;
      try {
        raw = await downloadBuffer(client, BASELINE_NAME);
      } catch (err) {
        const kind = classifyFtpError(err);
        if (kind === 'not_found') {
          throw new DriftError(
            `比較基準がありません（本番の ${remoteRoot}${BASELINE_NAME}）。初回は bootstrap_baseline で基準を置いてから再実行してください。`,
            { code: 'no_baseline' },
          );
        }
        throw new DriftError(`比較基準を読めません: ${err.message || err}`, { code: kind });
      }
      let data;
      try {
        data = JSON.parse(raw.toString('utf8'));
      } catch (err) {
        throw new DriftError(`比較基準を JSON として読めません: ${err.message}`, {
          code: 'baseline',
        });
      }
      return loadBaseline(data);
    },
    async readFile(relPath) {
      await ensure();
      const remote = asPosix(relPath);
      try {
        return await downloadBuffer(client, remote);
      } catch (err) {
        const kind = classifyFtpError(err);
        if (kind === 'not_found') return null;
        throw new DriftError(`本番ファイルを読めません (${remote}): ${err.message || err}`, {
          code: kind,
        });
      }
    },

    async listDeployableFiles(patterns) {
      await ensure();
      try {
        const names = await listRemoteDeployableFiles(client, '', patterns);
        return names.sort((a, b) => a.localeCompare(b));
      } catch (err) {
        if (err instanceof DriftError) throw err;
        const kind = classifyFtpError(err);
        throw new DriftError(`本番側の一覧取得に失敗しました: ${err.message || err}`, { code: kind });
      }
    },
    async writeBaseline(doc) {
      await ensure();
      const payload = Buffer.from(`${JSON.stringify(doc, null, 2)}\n`, 'utf8');
      try {
        await client.uploadFrom(Readable.from(payload), BASELINE_NAME);
      } catch (err) {
        throw new DriftError(`基準ファイルの書き込みに失敗しました: ${err.message || err}`, {
          code: classifyFtpError(err),
        });
      }
      console.log(`Uploaded baseline ${BASELINE_NAME} (${Object.keys(doc.files).length} files)`);
    },
    async close() {
      client.close();
    },
  };
}

async function listRemoteDeployableFiles(client, relDir, patterns) {
  const remotePath = relDir || '.';
  let listing;
  try {
    listing = await client.list(remotePath === '.' ? undefined : remotePath);
  } catch (err) {
    const kind = classifyFtpError(err);
    throw new DriftError(
      `本番側の一覧取得に失敗しました (${relDir || '/'}): ${err.message || err}`,
      { code: kind },
    );
  }

  const out = [];
  for (const item of listing) {
    const name = item.name;
    if (!name || name === '.' || name === '..') continue;
    const rel = relDir ? `${asPosix(relDir)}/${name}` : name;
    if (item.isDirectory) {
      if (isExcluded(rel, patterns) || isExcluded(`${rel}/`, patterns)) continue;
      out.push(...(await listRemoteDeployableFiles(client, rel, patterns)));
      continue;
    }
    if (item.isFile) {
      if (isExcluded(rel, patterns)) continue;
      out.push(asPosix(rel));
    }
  }
  return out;
}

async function downloadBuffer(client, remotePath) {
  const chunks = [];
  const writable = new Writable({
    write(chunk, _enc, cb) {
      chunks.push(Buffer.from(chunk));
      cb();
    },
  });
  await client.downloadTo(writable, remotePath);
  return Buffer.concat(chunks);
}

function parseArgs(argv) {
  const args = {
    mode: 'check',
    localDir: '.',
    exclude: '',
    excludeFile: '',
    baselineOut: '',
    prodRoot: '',
    baseline: '',
    ftpServer: process.env.PROD_FTP_SERVER || '',
    ftpUsername: process.env.PROD_FTP_USERNAME || '',
    ftpPassword: process.env.PROD_FTP_PASSWORD || '',
    ftpServerDir: process.env.PROD_FTP_SERVER_DIR || '',
    ftpTls: false,
  };
  const rest = [...argv];
  if (rest[0] && !rest[0].startsWith('-')) {
    args.mode = rest.shift();
  }
  while (rest.length) {
    const key = rest.shift();
    const take = () => {
      if (!rest.length) throw new DriftError(`引数が足りません: ${key}`, { code: 'config' });
      return rest.shift();
    };
    switch (key) {
      case '--local-dir':
        args.localDir = take();
        break;
      case '--exclude':
        args.exclude = take();
        break;
      case '--exclude-file':
        args.excludeFile = take();
        break;
      case '--baseline-out':
        args.baselineOut = take();
        break;
      case '--prod-root':
        args.prodRoot = take();
        break;
      case '--baseline':
        args.baseline = take();
        break;
      case '--ftp-server':
        args.ftpServer = take();
        break;
      case '--ftp-username':
        args.ftpUsername = take();
        break;
      case '--ftp-password':
        args.ftpPassword = take();
        break;
      case '--ftp-server-dir':
        args.ftpServerDir = take();
        break;
      case '--ftp-tls':
        args.ftpTls = true;
        break;
      case '--mode':
        args.mode = take();
        break;
      default:
        throw new DriftError(`不明な引数: ${key}`, { code: 'config' });
    }
  }
  return args;
}

async function resolvePatterns(args) {
  if (args.excludeFile) {
    return parseExclude(await readFile(args.excludeFile, 'utf8'));
  }
  if (args.exclude) return parseExclude(args.exclude);
  return loadExcludeFromEnv();
}

async function openStore(args) {
  if (args.prodRoot) {
    try {
      const st = await stat(args.prodRoot);
      if (!st.isDirectory()) {
        throw new DriftError(`本番ミラーを読めません（ディレクトリがありません）: ${args.prodRoot}`, {
          code: 'io',
        });
      }
    } catch (err) {
      if (err instanceof DriftError) throw err;
      throw new DriftError(`本番ミラーを読めません（ディレクトリがありません）: ${args.prodRoot}`, {
        code: 'io',
      });
    }
    return createLocalStore(args.prodRoot, args.baseline || null);
  }
  return createFtpStore({
    server: args.ftpServer,
    username: args.ftpUsername,
    password: args.ftpPassword,
    serverDir: args.ftpServerDir,
    secure: args.ftpTls,
  });
}

export async function runCli(argv = process.argv.slice(2)) {
  const args = parseArgs(argv);
  const patterns = await resolvePatterns(args);
  const localFiles = await listLocalFiles(args.localDir, patterns);
  if (Object.keys(localFiles).length === 0) {
    throw new DriftError('転送対象ファイルが 0 件です（除外が広すぎるか local-dir が空です）', {
      code: 'config',
    });
  }

  if (args.mode === 'make-baseline') {
    const doc = makeBaselineDoc(localFiles);
    const text = `${JSON.stringify(doc, null, 2)}\n`;
    if (args.baselineOut) {
      await writeFile(args.baselineOut, text, 'utf8');
      console.log(`Wrote ${args.baselineOut} (${Object.keys(localFiles).length} files)`);
    } else {
      process.stdout.write(text);
    }
    return 0;
  }

  const store = await openStore(args);
  try {
    if (args.mode === 'write-baseline') {
      await store.writeBaseline(makeBaselineDoc(localFiles));
      return 0;
    }
    if (args.mode === 'bootstrap') {
      await bootstrapBaseline(store, localFiles, patterns);
      return 0;
    }
    if (args.mode !== 'check') {
      throw new DriftError(`不明な mode: ${args.mode}`, { code: 'config' });
    }
    const findings = await checkDrift(store, localFiles);
    if (findings.length > 0) {
      console.error('本番直書き（または基準との不一致）を検知したため、デプロイを中断します:');
      for (const f of findings) {
        console.error(`  - ${f.path}: ${f.reason}`);
      }
      return 1;
    }
    console.log('Drift check passed: production matches baseline for planned transfers.');
    return 0;
  } finally {
    if (store.close) await store.close();
  }
}

const isDirect = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);
if (isDirect) {
  runCli()
    .then((code) => {
      process.exitCode = code;
    })
    .catch((err) => {
      console.error(`ERROR: ${err.message || err}`);
      process.exitCode = 1;
    });
}
