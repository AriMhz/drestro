import re

file_path = 'src/app/pricing/PricingClient.tsx'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Remove the useState
content = re.sub(r"  const \[billingCycle, setBillingCycle\] = useState\<'yearly' \| 'half-yearly'\>\('yearly'\);\n", "", content)
content = re.sub(r"import React, { useState } from 'react';", "import React from 'react';", content)

# 2. Remove the billing toggle block
# We'll replace it with just the p tag
toggle_regex = r"""        {/\* Billing Toggle \*/}
        <div className="inline-flex items-center bg-\[\#F4F4F5\] dark:bg-neutral-900 p-1\.5 rounded-xl relative shadow-inner">
          <button
            onClick=\{\(\) => setBillingCycle\('half-yearly'\)\}
            className={`relative z-10 px-8 py-3 rounded-lg text-sm font-bold transition-colors \$\{billingCycle === 'half-yearly' \? 'text-\[\#111111\] dark:text-white' : 'text-\[\#666666\] hover:text-\[\#111111\] dark:text-white'\}`}
          >
            6 Months
          </button>
          <button
            onClick=\{\(\) => setBillingCycle\('yearly'\)\}
            className={`relative z-10 px-8 py-3 rounded-lg text-sm font-bold transition-colors \$\{billingCycle === 'yearly' \? 'text-\[\#111111\] dark:text-white' : 'text-\[\#666666\] hover:text-\[\#111111\] dark:text-white'\}`}
          >
            Yearly
            <span className="absolute -top-3 -right-4 bg-emerald-500 text-white text-\[10px\] font-black px-2\.5 py-1 rounded-full shadow-sm">SAVE 20%</span>
          </button>
          <div 
            className={`absolute top-1\.5 bottom-1\.5 w-\[calc\(50%-4px\)\] bg-white dark:bg-\[\#0a0a0a\] rounded-lg shadow-sm transition-transform duration-300 ease-in-out \$\{billingCycle === 'yearly' \? 'translate-x-full' : 'translate-x-0'\}`}
          ></div>
        </div>"""
content = re.sub(toggle_regex, "", content)

# 3. Replace all dynamic pricing displays
content = content.replace("{((billingCycle === 'yearly' && plan.oldPriceYearly) || (billingCycle === 'half-yearly' && plan.oldPriceHalfYearly)) && (", "{plan.oldPriceYearly && (")
content = content.replace("{(billingCycle === 'yearly' ? plan.oldPriceYearly : plan.oldPriceHalfYearly).toLocaleString()}", "{plan.oldPriceYearly.toLocaleString()}")
content = content.replace("{(billingCycle === 'yearly' ? plan.priceYearly : plan.priceHalfYearly).toLocaleString()}", "{plan.priceYearly.toLocaleString()}")
content = content.replace("{(billingCycle === 'yearly' ? plan.priceYearly : plan.priceHalfYearly) > 0 &&", "{plan.priceYearly > 0 &&")
content = content.replace("/{billingCycle === 'yearly' ? 'yr' : '6mo'}", "/yr")


with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated PricingClient.tsx")
