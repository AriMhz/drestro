const fs = require('fs');

let code = fs.readFileSync('src/app/page.tsx', 'utf8');

// 1. Add imports
if (!code.includes('FaqSection')) {
  code = code.replace(
    /import ClientLogoMarquee from '@\/src\/components\/ClientLogoMarquee';/,
    `import ClientLogoMarquee from '@/src/components/ClientLogoMarquee';\nimport FaqSection from '@/src/components/FaqSection';`
  );
}

// 2. Add ClientLogoMarquee and FAQ section if not present
if (!code.includes('<FaqSection />')) {
  code = code.replace(
    /\{\/\* 3\. OFFLINE LOCAL SUBNET ARCHITECTURE \*\/\}/,
    `<ClientLogoMarquee />\n\n      {/* 3. OFFLINE LOCAL SUBNET ARCHITECTURE */}`
  );
  
  code = code.replace(
    /\{\/\* 7\. CTA BOTTOM BANNER \*\/\}/,
    `{/* 7. FAQ SECTION */}\n      <FaqSection />\n\n      {/* 8. CTA BOTTOM BANNER */}`
  );
}

// 3. Fix dark mode text colors in Enterprise section
code = code.replace(/text-\[\#111111\](?!\s+dark:text-white)/g, 'text-[#111111] dark:text-white');
code = code.replace(/text-\[\#555555\](?!\s+dark:text-neutral-400)/g, 'text-[#555555] dark:text-neutral-400');

fs.writeFileSync('src/app/page.tsx', code, 'utf8');
console.log('Fixed page.tsx');
