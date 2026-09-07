const fs = require('fs');

const page = fs.readFileSync('src/app/page.tsx', 'utf8').split('\n');
const missing = fs.readFileSync('missing.txt', 'utf8');

// Find the index of "variants={FADE_IN}" after "{/* Sub-headline */}"
let splitIndex1 = -1;
let splitIndex2 = -1;
for (let i = 0; i < page.length; i++) {
  if (page[i].includes('{/* Sub-headline */}')) {
    splitIndex1 = i + 2; // the line "variants={FADE_IN}"
    break;
  }
}

for (let i = splitIndex1; i < page.length; i++) {
  if (page[i].includes('))}')) {
    splitIndex2 = i;
    break;
  }
}

console.log('Split1:', splitIndex1, 'Split2:', splitIndex2);
console.log('Line at split1:', page[splitIndex1]);
console.log('Line at split2:', page[splitIndex2]);

if (splitIndex1 !== -1 && splitIndex2 !== -1) {
  const newLines = [
    ...page.slice(0, splitIndex1 + 1),
    missing,
    ...page.slice(splitIndex2)
  ];
  fs.writeFileSync('src/app/page.tsx', newLines.join('\n'));
  console.log('Fixed page.tsx');
} else {
  console.log('Could not find split points');
}
