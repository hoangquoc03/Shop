# TH12 SmartLife Shipping Implementation Report

## Scope and backup

- Coursework/demo shipping configuration only; rates are not real business pricing.
- Fresh database backup: `C:/laragon/backups/data_shop_before_th12_shipping_20261002_214721.sql`.
- Backup verification: readable, 3,681,911 bytes; SHA-256 `b3d59878247d507f1eb623012edb502502a649d4488473e470eb1081b1a60e83`.
- Configuration changes used WordPress/WooCommerce APIs; no direct SQL writes.

## Shipping zones

| Priority | Zone             | Coverage                                                             | Configured methods                                   |
| -------: | ---------------- | -------------------------------------------------------------------- | ---------------------------------------------------- |
|        0 | TP. Hồ Chí Minh  | Exactly the 168 supplied postcodes; postcode entries verified unique | SmartLife demo weight shipping; native Free Shipping |
|        1 | Việt Nam còn lại | Country `VN`; fallback after the postcode zone                       | SmartLife demo weight shipping; native Free Shipping |

The Zone 1 postcode list was stored exactly as supplied. Zone 1 is before Zone 2.

## Rates and free shipping

| Zone             | Up to and including 2 kg | Over 2 kg through 5 kg |  Over 5 kg |
| ---------------- | -----------------------: | ---------------------: | ---------: |
| TP. Hồ Chí Minh  |               25,000 VND |             35,000 VND | 50,000 VND |
| Việt Nam còn lại |               35,000 VND |             50,000 VND | 70,000 VND |

Native Free Shipping is configured in both zones with `requires = min_amount`, minimum `210000`, and `ignore_discounts = no`. WooCommerce's `woocommerce_shipping_hide_rates_when_free` is enabled. Taxes remain disabled.

## Custom method

- File: `wp-content/plugins/smartlife-demo-shipping/smartlife-demo-shipping.php`
- Plugin: SmartLife Demo Weight Shipping, active.
- The method calculates `SUM(product weight converted to kg × quantity)` from shippable package contents and selects the configured zone-specific band. Missing/invalid item weight yields no custom rate rather than an invented weight.
- No third-party plugin, WooCommerce core, theme, catalog, or shipping class was changed.

## Runtime tests

Tests exercised WooCommerce's live zone matcher, package-rate calculation, native Free Shipping availability, and hide-paid-rates logic using in-memory cart/package data and actual catalog product objects. No order or product data was written.

| Case | Test input                              | Expected                            | Actual                   | Result |
| ---- | --------------------------------------- | ----------------------------------- | ------------------------ | ------ |
| A    | HCMC; 0.12 kg; subtotal 100,000         | Paid rate 25,000 VND                | 25,000 VND               | PASS   |
| B    | HCMC; 3.20 kg; subtotal 100,000         | Paid rate 35,000 VND                | 35,000 VND               | PASS   |
| C    | HCMC; 8.00 kg; subtotal 100,000         | Paid rate 50,000 VND                | 50,000 VND               | PASS   |
| D    | VN fallback; 0.12 kg; subtotal 100,000  | Paid rate 35,000 VND                | 35,000 VND               | PASS   |
| E    | VN fallback; 3.20 kg; subtotal 100,000  | Paid rate 50,000 VND                | 50,000 VND               | PASS   |
| F    | VN fallback; 8.00 kg; subtotal 100,000  | Paid rate 70,000 VND                | 70,000 VND               | PASS   |
| G    | HCMC; subtotal 210,000; no discount     | Free shipping available             | Free shipping, 0 VND     | PASS   |
| H    | HCMC; subtotal 209,999; no discount     | Paid rate shown                     | Paid rate 25,000 VND     | PASS   |
| I    | HCMC; subtotal 250,000; discount 50,000 | No free shipping                    | Paid rate 25,000 VND     | PASS   |
| J    | HCMC; subtotal 250,000; discount 30,000 | Free shipping available             | Free shipping, 0 VND     | PASS   |
| K    | HCMC; free shipping eligible            | Paid rates hidden                   | Only Free Shipping shown | PASS   |
| L    | HCMC; 3.20 kg product × quantity 2      | Total 6.40 kg; paid rate 50,000 VND | 6.40 kg; 50,000 VND      | PASS   |

Additional exact-boundary checks: 2.00 kg and 5.00 kg select the lower inclusive band; 2.10 kg and 5.25 kg select the next band. All six Zone 1/Zone 2 boundary checks passed.

## Final audit

- Approved catalog: 40 products and 40 unique SKUs; SKU, name, prices, stock, weights, categories, and attributes match the pre-change snapshot.
- Product records: 40 before and 40 after; no products added or deleted.
- Orders and order-item metadata: unchanged by full-data hashes.
- Payment settings, theme, taxes, and all existing plugin entries/order: unchanged; only the SmartLife shipping plugin was added.
- Tax setting remains disabled.
- All configuration guards and runtime tests: PASS.
