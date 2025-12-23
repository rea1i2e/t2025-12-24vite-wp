/**
 * 除外ページチェック関数
 * プレフィックスマッチングと正規表現ライクなパターンに対応
 * 
 * @param {string} key - チェックするページキー
 * @param {string[]} excludePages - 除外パターンの配列
 * @returns {boolean} 除外対象の場合true
 * 
 * @example
 * // プレフィックスマッチング: "demo*" → demo, demoFadein, demoDialog などすべて除外
 * // 正規表現ライク: "demo[A-Z]*" → demoFadein, demoDialog などは除外、demo は除外しない
 * // 完全一致: "contact" → contact のみ除外
 */
export const isExcluded = (key, excludePages) => {
  return excludePages.some(pattern => {
    if (!pattern) return false;

    // 正規表現ライクなパターン（例: demo[A-Z]*）
    if (pattern.includes('[') && pattern.includes(']')) {
      const regexPattern = pattern.replace(/\*/g, '.*');
      const regex = new RegExp('^' + regexPattern + '$');
      return regex.test(key);
    }

    // プレフィックスマッチング（例: demo*）
    if (pattern.endsWith('*')) {
      const prefix = pattern.slice(0, -1);
      return key.startsWith(prefix);
    }

    // 完全一致
    return key === pattern;
  });
};

