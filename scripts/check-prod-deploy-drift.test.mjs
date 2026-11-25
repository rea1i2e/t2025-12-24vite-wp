import assert from 'node:assert/strict';
import { mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import test from 'node:test';
import {
  BASELINE_NAME,
  SYNC_STATE_NAME,
  asPosix,
  bootstrapBaseline,
  checkDrift,
  classifyFtpError,
  createLocalStore,
  isExcluded,
  listLocalFiles,
  loadBaseline,
  makeBaselineDoc,
  parseExclude,
  parseFtpExcludeFromWorkflow,
  runCli,
  sha256Buffer,
} from './check-prod-deploy-drift.mjs';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const WORKFLOW = path.join(ROOT, '.github/workflows/deploy-production.yml');

async function withTemp(fn) {
  const dir = await mkdtemp(path.join(os.tmpdir(), 'prod-drift-'));
  try {
    return await fn(dir);
  } finally {
    await rm(dir, { recursive: true, force: true });
  }
}

test('asPosix keeps leading dot files (does not strip .github)', () => {
  assert.equal(asPosix('.github/workflows/a.yml'), '.github/workflows/a.yml');
  assert.equal(asPosix('./header.php'), 'header.php');
  assert.equal(asPosix('.env.deploy'), '.env.deploy');
});

test('workflow FTP_EXCLUDE excludes dot paths and keeps theme files', async () => {
  const yaml = await readFile(WORKFLOW, 'utf8');
  const patterns = parseFtpExcludeFromWorkflow(yaml);

  const excluded = [
    '.github/workflows/deploy-production.yml',
    '.gitignore',
    '.env.deploy',
    '.DS_Store',
    'src/assets/sass/a.scss',
    'scripts/check-prod-deploy-drift.mjs',
    'node_modules/basic-ftp/package.json',
    BASELINE_NAME,
    SYNC_STATE_NAME,
    'package.json',
    'README.md',
    'ai-docs/git-branch-and-deploy.md',
    'manual/index.html',
  ];
  for (const p of excluded) {
    assert.equal(isExcluded(p, patterns), true, `should exclude ${p}`);
  }

  const kept = ['header.php', 'style.css', 'dist/assets/main.css', 'components/foo.php', 'functions.php'];
  for (const p of kept) {
    assert.equal(isExcluded(p, patterns), false, `should keep ${p}`);
  }
});

test('listLocalFiles with real FTP_EXCLUDE skips .github and .gitignore', async () => {
  const yaml = await readFile(WORKFLOW, 'utf8');
  const patterns = parseFtpExcludeFromWorkflow(yaml);

  await withTemp(async (dir) => {
    await mkdir(path.join(dir, '.github/workflows'), { recursive: true });
    await mkdir(path.join(dir, 'src'), { recursive: true });
    await mkdir(path.join(dir, 'scripts'), { recursive: true });
    await mkdir(path.join(dir, 'node_modules/x'), { recursive: true });
    await mkdir(path.join(dir, 'dist/assets'), { recursive: true });
    await writeFile(path.join(dir, '.github/workflows/a.yml'), 'x\n');
    await writeFile(path.join(dir, '.gitignore'), 'x\n');
    await writeFile(path.join(dir, '.env.local'), 'x\n');
    await writeFile(path.join(dir, '.DS_Store'), 'x\n');
    await writeFile(path.join(dir, BASELINE_NAME), '{}\n');
    await writeFile(path.join(dir, SYNC_STATE_NAME), '{}\n');
    await writeFile(path.join(dir, 'src/a.scss'), 'x\n');
    await writeFile(path.join(dir, 'scripts/a.mjs'), 'x\n');
    await writeFile(path.join(dir, 'node_modules/x/a.js'), 'x\n');
    await writeFile(path.join(dir, 'header.php'), 'php\n');
    await writeFile(path.join(dir, 'style.css'), 'css\n');
    await writeFile(path.join(dir, 'dist/assets/a.css'), 'd\n');

    const files = await listLocalFiles(dir, patterns);
    assert.deepEqual(Object.keys(files).sort(), ['dist/assets/a.css', 'header.php', 'style.css']);
  });
});

test('planned changes and drift findings', async () => {
  await withTemp(async (dir) => {
    const local = path.join(dir, 'local');
    const prod = path.join(dir, 'prod');
    await mkdir(local);
    await mkdir(prod);
    await writeFile(path.join(local, 'header.php'), 'beta\n');
    await writeFile(path.join(local, 'style.css'), 'keep\n');
    await writeFile(path.join(local, 'brand-new.php'), 'new\n');
    await writeFile(path.join(prod, 'header.php'), 'alpha\n');
    await writeFile(path.join(prod, 'style.css'), 'keep\n');

    const baseline = makeBaselineDoc({
      'header.php': sha256Buffer(Buffer.from('alpha\n')),
      'style.css': sha256Buffer(Buffer.from('keep\n')),
    });
    await writeFile(path.join(prod, BASELINE_NAME), `${JSON.stringify(baseline, null, 2)}\n`);

    const patterns = parseExcludeMinimal();
    const localFiles = await listLocalFiles(local, patterns);
    const store = createLocalStore(prod);
    const findings = await checkDrift(store, localFiles);
    assert.equal(findings.length, 0);

    await writeFile(path.join(prod, 'header.php'), 'HACKED\n');
    const findings2 = await checkDrift(store, localFiles);
    assert.ok(findings2.some((f) => f.path === 'header.php'));

    await writeFile(path.join(prod, 'header.php'), 'alpha\n');
    await writeFile(path.join(prod, 'brand-new.php'), 'orphan\n');
    const findings3 = await checkDrift(store, localFiles);
    assert.ok(findings3.some((f) => f.path === 'brand-new.php' && /衝突/.test(f.reason)));
  });
});

test('delete path stops when remote differs from baseline', async () => {
  await withTemp(async (dir) => {
    const local = path.join(dir, 'local');
    const prod = path.join(dir, 'prod');
    await mkdir(local);
    await mkdir(prod);
    await writeFile(path.join(local, 'header.php'), 'alpha\n');
    await writeFile(path.join(prod, 'header.php'), 'alpha\n');
    await writeFile(path.join(prod, 'style.css'), 'EDITED\n');
    const baseline = makeBaselineDoc({
      'header.php': sha256Buffer(Buffer.from('alpha\n')),
      'style.css': sha256Buffer(Buffer.from('keep\n')),
    });
    await writeFile(path.join(prod, BASELINE_NAME), `${JSON.stringify(baseline, null, 2)}\n`);
    const localFiles = await listLocalFiles(local, parseExcludeMinimal());
    const findings = await checkDrift(createLocalStore(prod), localFiles);
    assert.ok(findings.some((f) => f.path === 'style.css' && /削除予定/.test(f.reason)));
  });
});

test('missing baseline fails', async () => {
  await withTemp(async (dir) => {
    const local = path.join(dir, 'local');
    const prod = path.join(dir, 'prod');
    await mkdir(local);
    await mkdir(prod);
    await writeFile(path.join(local, 'header.php'), 'a\n');
    await writeFile(path.join(prod, 'header.php'), 'a\n');
    await assert.rejects(
      () => checkDrift(createLocalStore(prod), { 'header.php': sha256Buffer(Buffer.from('a\n')) }),
      /比較基準がありません/,
    );
  });
});

test('FTP permission denied is not treated as missing file', () => {
  assert.equal(classifyFtpError({ code: 550, message: '550 Permission denied' }), 'permission');
  assert.equal(classifyFtpError({ code: 550, message: '550 No such file or directory' }), 'not_found');
  assert.equal(classifyFtpError({ code: 550, message: '550 Failed' }), 'ftp_error');
  assert.equal(classifyFtpError({ code: 530, message: '530 Login incorrect' }), 'auth');
});

test('create-path read permission error stops (does not pass as absent)', async () => {
  const store = {
    async readBaseline() {
      return loadBaseline(
        makeBaselineDoc({
          'header.php': sha256Buffer(Buffer.from('a\n')),
        }),
      );
    },
    async readFile(rel) {
      if (rel === 'brand-new.php') {
        const err = new Error('550 Permission denied');
        err.code = 550;
        throw err;
      }
      return Buffer.from('a\n');
    },
    async writeBaseline() {},
  };
  await assert.rejects(
    () =>
      checkDrift(store, {
        'header.php': sha256Buffer(Buffer.from('a\n')),
        'brand-new.php': sha256Buffer(Buffer.from('n\n')),
      }),
    /読めません|Permission denied|判定できません/,
  );
});

test('bootstrap succeeds when local and prod sets and contents match', async () => {
  const patterns = await workflowPatterns();
  await withTemp(async (dir) => {
    const local = path.join(dir, 'local');
    const prod = path.join(dir, 'prod');
    await mkdir(path.join(local, 'dist'), { recursive: true });
    await mkdir(path.join(prod, 'dist'), { recursive: true });
    await writeFile(path.join(local, 'header.php'), 'same\n');
    await writeFile(path.join(local, 'style.css'), 'css\n');
    await writeFile(path.join(local, 'dist/a.css'), 'd\n');
    await writeFile(path.join(prod, 'header.php'), 'same\n');
    await writeFile(path.join(prod, 'style.css'), 'css\n');
    await writeFile(path.join(prod, 'dist/a.css'), 'd\n');
    // excluded noise on prod only — must be ignored
    await mkdir(path.join(prod, 'src'), { recursive: true });
    await writeFile(path.join(prod, 'src/ignore.scss'), 'noise\n');
    await writeFile(path.join(prod, '.DS_Store'), 'x\n');

    const files = await listLocalFiles(local, patterns);
    await bootstrapBaseline(createLocalStore(prod), files, patterns);
    const raw = JSON.parse(await readFile(path.join(prod, BASELINE_NAME), 'utf8'));
    assert.equal(Object.keys(raw.files).sort().join(','), 'dist/a.css,header.php,style.css');
  });
});

test('bootstrap fails when local file missing on prod', async () => {
  const patterns = await workflowPatterns();
  await withTemp(async (dir) => {
    const local = path.join(dir, 'local');
    const prod = path.join(dir, 'prod');
    await mkdir(local);
    await mkdir(prod);
    await writeFile(path.join(local, 'header.php'), 'a\n');
    await writeFile(path.join(local, 'style.css'), 'b\n');
    await writeFile(path.join(prod, 'header.php'), 'a\n');
    const files = await listLocalFiles(local, patterns);
    await assert.rejects(
      () => bootstrapBaseline(createLocalStore(prod), files, patterns),
      /ローカルにはあるが本番に無い/,
    );
  });
});

test('bootstrap fails when prod-only deployable file exists', async () => {
  const patterns = await workflowPatterns();
  await withTemp(async (dir) => {
    const local = path.join(dir, 'local');
    const prod = path.join(dir, 'prod');
    await mkdir(local);
    await mkdir(prod);
    await writeFile(path.join(local, 'header.php'), 'a\n');
    await writeFile(path.join(prod, 'header.php'), 'a\n');
    await writeFile(path.join(prod, 'direct-added.php'), 'orphan\n');
    const files = await listLocalFiles(local, patterns);
    await assert.rejects(
      () => bootstrapBaseline(createLocalStore(prod), files, patterns),
      /本番側だけに存在する/,
    );
  });
});

test('bootstrap ignores prod-only excluded paths', async () => {
  const patterns = await workflowPatterns();
  await withTemp(async (dir) => {
    const local = path.join(dir, 'local');
    const prod = path.join(dir, 'prod');
    await mkdir(local);
    await mkdir(path.join(prod, 'src'), { recursive: true });
    await mkdir(path.join(prod, '.github'), { recursive: true });
    await writeFile(path.join(local, 'header.php'), 'a\n');
    await writeFile(path.join(prod, 'header.php'), 'a\n');
    await writeFile(path.join(prod, 'src/x.scss'), 'x\n');
    await writeFile(path.join(prod, '.github/x.yml'), 'y\n');
    await writeFile(path.join(prod, '.env.deploy'), 'z\n');
    const files = await listLocalFiles(local, patterns);
    await bootstrapBaseline(createLocalStore(prod), files, patterns);
  });
});

test('bootstrap fails when shared path content differs', async () => {
  const patterns = await workflowPatterns();
  await withTemp(async (dir) => {
    const local = path.join(dir, 'local');
    const prod = path.join(dir, 'prod');
    await mkdir(local);
    await mkdir(prod);
    await writeFile(path.join(local, 'header.php'), 'local\n');
    await writeFile(path.join(prod, 'header.php'), 'prod\n');
    const files = await listLocalFiles(local, patterns);
    await assert.rejects(
      () => bootstrapBaseline(createLocalStore(prod), files, patterns),
      /本番とローカルが異なる/,
    );
  });
});

test('bootstrap stops when remote listing fails', async () => {
  const patterns = await workflowPatterns();
  const store = {
    async listDeployableFiles() {
      throw new Error('550 Permission denied');
    },
    async readFile() {
      return Buffer.from('a\n');
    },
    async writeBaseline() {},
  };
  await assert.rejects(
    () => bootstrapBaseline(store, { 'header.php': sha256Buffer(Buffer.from('a\n')) }, patterns),
    /一覧取得に失敗|Permission denied/,
  );
});

test('bootstrap stops when listDeployableFiles is missing', async () => {
  const patterns = await workflowPatterns();
  const store = {
    async readFile() {
      return Buffer.from('a\n');
    },
    async writeBaseline() {},
  };
  await assert.rejects(
    () => bootstrapBaseline(store, { 'header.php': sha256Buffer(Buffer.from('a\n')) }, patterns),
    /一覧取得に対応していません/,
  );
});

test('runCli check passes when clean', async () => {
  await withTemp(async (dir) => {
    const local = path.join(dir, 'local');
    const prod = path.join(dir, 'prod');
    await mkdir(local);
    await mkdir(prod);
    await writeFile(path.join(local, 'header.php'), 'beta\n');
    await writeFile(path.join(prod, 'header.php'), 'alpha\n');
    const baseline = makeBaselineDoc({
      'header.php': sha256Buffer(Buffer.from('alpha\n')),
    });
    await writeFile(path.join(prod, BASELINE_NAME), `${JSON.stringify(baseline)}\n`);
    const excludeFile = path.join(dir, 'exclude.txt');
    await writeFile(excludeFile, '**/src/**\n');
    const code = await runCli([
      'check',
      '--local-dir',
      local,
      '--prod-root',
      prod,
      '--exclude-file',
      excludeFile,
    ]);
    assert.equal(code, 0);
  });
});

async function workflowPatterns() {
  const yaml = await readFile(WORKFLOW, 'utf8');
  return parseFtpExcludeFromWorkflow(yaml);
}

function parseExcludeMinimal() {
  return parseExclude('**/src/**\n**/node_modules/**\n**/.DS_Store\n');
}
