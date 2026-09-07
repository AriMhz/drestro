const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
  fs.readdirSync(dir).forEach(f => {
    let dirPath = path.join(dir, f);
    let isDirectory = fs.statSync(dirPath).isDirectory();
    isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
  });
}

const replacements = [
  { regex: /bg-\[#FFFFFF\](?!\s+dark:bg-\[#0a0a0a\])/g, replacement: 'bg-[#FFFFFF] dark:bg-[#0a0a0a]' },
  { regex: /bg-white(?!\s+dark:bg-\[#0a0a0a\])/g, replacement: 'bg-white dark:bg-[#0a0a0a]' },
  { regex: /text-\[#111111\](?!\s+dark:text-white)/g, replacement: 'text-[#111111] dark:text-white' },
  { regex: /bg-\[#FAFAFA\](?!\s+dark:bg-\[#111111\])/g, replacement: 'bg-[#FAFAFA] dark:bg-[#111111]' },
  { regex: /text-\[#555555\](?!\s+dark:text-neutral-400)/g, replacement: 'text-[#555555] dark:text-neutral-400' },
  { regex: /border-\[#E2E2E7\](?!\s+dark:border-neutral-800)/g, replacement: 'border-[#E2E2E7] dark:border-neutral-800' }
];

walkDir('src', function(filePath) {
  if (filePath.endsWith('.tsx') || filePath.endsWith('.ts')) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;
    
    replacements.forEach(rule => {
      content = content.replace(rule.regex, rule.replacement);
    });

    if (content !== original) {
      fs.writeFileSync(filePath, content, 'utf8');
      console.log(`Updated ${filePath}`);
    }
  }
});
