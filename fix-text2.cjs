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
  { regex: /text-gray-600(?!\s+dark:text-neutral-400)/g, replacement: 'text-gray-600 dark:text-neutral-400' },
  { regex: /text-gray-700(?!\s+dark:text-neutral-300)/g, replacement: 'text-gray-700 dark:text-neutral-300' },
  { regex: /text-gray-800(?!\s+dark:text-neutral-200)/g, replacement: 'text-gray-800 dark:text-neutral-200' },
  { regex: /text-gray-900(?!\s+dark:text-white)/g, replacement: 'text-gray-900 dark:text-white' },
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
