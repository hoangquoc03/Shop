# SmartLife Product Data Schema

## WooCommerce product fields

| Business field      | WooCommerce storage/API                             | Requirement and audit                                        |
| ------------------- | --------------------------------------------------- | ------------------------------------------------------------ |
| SKU                 | `_sku` / `WC_Product::get_sku()`                    | Unique grouped SKU; 40/40. Never uses Product ID.            |
| Product name        | `wp_posts.post_title` / `WC_Product::get_name()`    | Required, no placeholder; 40/40.                             |
| Selling price       | `_regular_price`, `_price`; sale price when present | Positive numeric VND amount; current prices preserved.       |
| Inventory           | `_manage_stock`, `_stock`, `_stock_status`          | Managed inventory and quantity; 40/40.                       |
| Featured image      | `_thumbnail_id` → attachment post                   | Unique local Media Library attachment per SKU; 40/40.        |
| Main category       | `product_cat` term relationship                     | One leaf category per product, under one of 6 roots.         |
| Brand taxonomy      | `product_brand` term relationship                   | SmartLife assigned to 40/40. Separate from `pa_thuong-hieu`. |
| Cost                | `_cost_of_goods`                                    | Demo numeric cost > 0 and below selling price; 40/40.        |
| Short description   | `wp_posts.post_excerpt`                             | Product/category/attributes-specific; 40 unique values.      |
| Full description    | `wp_posts.post_content`                             | Product/category/attributes-specific; 40 unique values.      |
| Product tags        | `product_tag` term relationships                    | SmartLife, category, connection and control; 40/40.          |
| Weight              | `_weight`                                           | Store unit kg; 40/40, demo values by product type.           |
| Dimensions          | `_length`, `_width`, `_height`                      | Store unit cm; all three set for 40/40.                      |
| Featured image file | `_wp_attached_file` and attachment MIME             | `smartlife-products/{SKU}.webp`; 40 distinct attachments.    |

## Global attributes

Global definitions are held by WooCommerce. Product options use taxonomy term IDs with `WC_Product_Attribute::set_options()`, and term assignment uses WordPress/WooCommerce APIs.

| Label       | Taxonomy         | Usage                          |
| ----------- | ---------------- | ------------------------------ |
| Thương hiệu | `pa_thuong-hieu` | Product information            |
| Kết nối     | `pa_ket-noi`     | Filter and product information |
| Màu sắc     | `pa_mau-sac`     | Product information            |
| Công suất   | `pa_cong-suat`   | Filter and product information |
| Điện áp     | `pa_dien-ap`     | Product information            |
| Điều khiển  | `pa_dieu-khien`  | Filter and product information |
| Tương thích | `pa_tuong-thich` | Product information            |
| Bảo hành    | `pa_bao-hanh`    | Product information            |

Brand `product_brand` is a separate taxonomy; it is not replaced by the global attribute `pa_thuong-hieu`.

## Categories and filter query

`product_cat` contains 6 root terms and 22 child terms, maximum depth 2. Every product has one main leaf category. Price filtering uses WooCommerce `min_price` and `max_price`; it is not a product attribute. Other filter parameters are `filter_brand`, `filter_ket-noi`, `filter_cong-suat` and `filter_dieu-khien`.

## Image artifacts

- Original local file: `wp-content/uploads/smartlife-products/{SKU}.webp`.
- Image mapping and optimization facts: `wp-content/uploads/smartlife-products/image-manifest.json`.
- Demo images are 900×900 WebP illustrations with visible DEMO/not-official-photo labeling; maximum original size 14,486 bytes.# SmartLife Product Data Schema

## Core WooCommerce product fields

| Business field    | WooCommerce storage/API                                         | Validation                                                                  |
| ----------------- | --------------------------------------------------------------- | --------------------------------------------------------------------------- |
| SKU               | `_sku` / `WC_Product::get_sku()`                                | Unique, `SL-{GROUP}-{NUMBER}`; 40/40 populated.                             |
| Product name      | `wp_posts.post_title` / `WC_Product::get_name()`                | Non-empty, no placeholder; 40/40.                                           |
| Selling price     | `_regular_price`, `_price`; sale price when present             | Positive numeric VND amount; retained from existing products.               |
| Inventory         | `_manage_stock`, `_stock`, `_stock_status`                      | Managed stock and quantity present; 40/40.                                  |
| Featured image    | `_thumbnail_id` → attachment post                               | One unique local Media Library attachment per product; 40/40.               |
| Main category     | `product_cat` term relationship                                 | One leaf category per product; leaf belongs to one of 6 parents.            |
| Brand taxonomy    | `product_brand` term relationship                               | SmartLife assigned to 40/40.                                                |
| Cost              | `_cost_of_goods`                                                | Numeric demo amount > 0 and below selling price; 40/40.                     |
| Short description | `wp_posts.post_excerpt` / `WC_Product::get_short_description()` | Product/category-specific copy; 40 unique values.                           |
| Full description  | `wp_posts.post_content` / `WC_Product::get_description()`       | Product/category/attribute-aware copy; 40 unique values.                    |
| Tags              | `product_tag` relationships                                     | SmartLife, category, connection and control tags; 40/40.                    |
| Weight            | `_weight`                                                       | WooCommerce unit: kg; 40/40. Demo values by product type.                   |
| Dimensions        | `_length`, `_width`, `_height`                                  | WooCommerce unit: cm; all three set for 40/40. Demo values by product type. |

## Global attributes

Global attributes are registered in WooCommerce’s attribute taxonomy table; product values are stored as taxonomy terms and product-term relationships. `WC_Product_Attribute::set_options()` receives term IDs.

| Label       | Taxonomy         | Purpose                      |
| ----------- | ---------------- | ---------------------------- |
| Thương hiệu | `pa_thuong-hieu` | Product information          |
| Kết nối     | `pa_ket-noi`     | Filter + product information |
| Màu sắc     | `pa_mau-sac`     | Product information          |
| Công suất   | `pa_cong-suat`   | Filter + product information |
| Điện áp     | `pa_dien-ap`     | Product information          |
| Điều khiển  | `pa_dieu-khien`  | Filter + product information |
| Tương thích | `pa_tuong-thich` | Product information          |
| Bảo hành    | `pa_bao-hanh`    | Product information          |

`product_brand` is a separate brand taxonomy and is not a substitute for `pa_thuong-hieu`.

## Category schema

`product_cat` contains 6 root terms and 22 child terms. Maximum depth is two. Products are assigned one main leaf category. No product price attribute exists; price range uses WooCommerce `min_price`/`max_price` query parameters.

## Image schema

- Local source: `wp-content/uploads/smartlife-products/{SKU}.webp`.
- Media Library attachment is created through WordPress APIs and assigned to `_thumbnail_id`.
- One distinct attachment per SKU; image manifest records SKU, filename, dimensions, byte size and demo-image designation.
- Demo images are 900×900 WebP illustrations with visible DEMO/not-official-photo labeling.
