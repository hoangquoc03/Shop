<?php

$smartlife_wp_load = __DIR__ . '/wp-load.php';
if (!is_file($smartlife_wp_load)) {
    http_response_code(500);
    exit('Không tìm thấy wp-load.php.');
}
require_once $smartlife_wp_load;
require_once __DIR__ . '/smartlife-import-core.php';

if (PHP_SAPI !== 'cli' && (!is_user_logged_in() || !current_user_can('manage_woocommerce'))) {
    wp_die('Chỉ quản trị viên WooCommerce được xem audit.', 'SmartLife Audit', array('response' => 403));
}

if (!class_exists('WooCommerce')) {
    exit("WooCommerce active: NO\nFINAL: FAIL\n");
}

global $wpdb;

$catalog = require __DIR__ . '/smartlife-catalog.php';
$products = array();
$sku_counts = array();
$missing = array(
    'sku' => 0,
    'price' => 0,
    'stock' => 0,
    'cost' => 0,
    'image' => 0,
    'weight' => 0,
    'dimensions' => 0,
    'short_description' => 0,
    'description' => 0,
    'tags' => 0,
    'brand' => 0,
    'category' => 0,
    'attributes' => 0,
    'placeholder_name' => 0,
);
$category_assignments = array();
$attribute_assignments = array();
$image_bytes = array();
$image_attachment_ids = array();
$image_filename_issues = array();
$image_not_optimized = array();
$optimized_images = 0;
$cost_issues = array();
$sku_issues = array();

foreach ($catalog as $entry) {
    $product = sl11_find_catalog_product($entry);
    if (!$product) {
        $sku_issues[] = 'Product missing: ' . $entry['legacy_sku'];
        continue;
    }
    $products[] = $product;
    $id = $product->get_id();
    $sku = trim((string) $product->get_sku());
    if ($sku === '') {
        $missing['sku']++;
    } else {
        $sku_counts[strtoupper($sku)] = ($sku_counts[strtoupper($sku)] ?? 0) + 1;
    }
    if ($sku !== ($entry['sku']) && !get_post_meta($id, '_smartlife_preserved_order_sku', true)) {
        $sku_issues[] = 'SKU differs from catalog: ' . $sku . ' expected ' . $entry['sku'];
    }
    if (trim($product->get_name()) === '' || in_array(mb_strtolower(trim($product->get_name())), array('sản phẩm', 'product', 'test product'), true)) {
        $missing['placeholder_name']++;
    }
    if (!is_numeric($product->get_regular_price()) || (float) $product->get_regular_price() <= 0) {
        $missing['price']++;
    }
    if (!$product->managing_stock() || $product->get_stock_quantity() === null || $product->get_stock_status() === '') {
        $missing['stock']++;
    }

    $cost = get_post_meta($id, '_cost_of_goods', true);
    $selling_price = (float) $product->get_regular_price();
    if ($product->get_sale_price() !== '' && (float) $product->get_sale_price() > 0) {
        $selling_price = min($selling_price, (float) $product->get_sale_price());
    }
    if (!is_numeric($cost) || (float) $cost <= 0 || (float) $cost >= $selling_price) {
        $missing['cost']++;
        $cost_issues[] = array('sku' => $sku, 'cost' => $cost, 'selling_price' => $selling_price);
    }

    $thumbnail_id = (int) $product->get_image_id();
    if (!$thumbnail_id || get_post_type($thumbnail_id) !== 'attachment') {
        $missing['image']++;
    } else {
        $path = get_attached_file($thumbnail_id);
        if (!$path || !is_file($path)) {
            $missing['image']++;
        } else {
            $image_attachment_ids[] = $thumbnail_id;
            $bytes = filesize($path);
            $image_bytes[] = $bytes;
            $relative_path = str_replace('\\', '/', (string) get_post_meta($thumbnail_id, '_wp_attached_file', true));
            $expected_path = 'smartlife-products/' . sanitize_file_name($sku) . '.webp';
            if ($relative_path !== $expected_path) {
                $image_filename_issues[] = array('sku' => $sku, 'actual' => $relative_path, 'expected' => $expected_path);
            }
            $dimensions = getimagesize($path);
            $is_optimized = get_post_mime_type($thumbnail_id) === 'image/webp'
                && $dimensions
                && $dimensions[0] <= 1200
                && $dimensions[1] <= 1200
                && $bytes <= 150000;
            if ($is_optimized) {
                $optimized_images++;
            } else {
                $image_not_optimized[] = $sku;
            }
        }
    }
    if ($product->get_weight() === '') {
        $missing['weight']++;
    }
    if ($product->get_length() === '' || $product->get_width() === '' || $product->get_height() === '') {
        $missing['dimensions']++;
    }
    if (trim($product->get_short_description()) === '') {
        $missing['short_description']++;
    }
    if (trim($product->get_description()) === '') {
        $missing['description']++;
    }

    $tags = wp_get_object_terms($id, 'product_tag', array('fields' => 'ids'));
    if (is_wp_error($tags) || !$tags) {
        $missing['tags']++;
    }
    $brands = taxonomy_exists('product_brand') ? wp_get_object_terms($id, 'product_brand', array('fields' => 'ids')) : array();
    if (is_wp_error($brands) || !$brands) {
        $missing['brand']++;
    }
    $categories = wp_get_object_terms($id, 'product_cat', array('fields' => 'ids'));
    if (is_wp_error($categories) || !$categories) {
        $missing['category']++;
    }
    foreach (sl11_attribute_definitions() as $definition) {
        $taxonomy = sl11_attribute_taxonomy($definition['slug']);
        $terms = taxonomy_exists($taxonomy) ? wp_get_object_terms($id, $taxonomy, array('fields' => 'ids')) : array();
        if (is_wp_error($terms) || !$terms) {
            $missing['attributes']++;
        }
        $attribute_assignments[$taxonomy] = ($attribute_assignments[$taxonomy] ?? 0) + ((!is_wp_error($terms) && $terms) ? 1 : 0);
    }
    $category_assignments['assigned'] = ($category_assignments['assigned'] ?? 0) + ((!is_wp_error($categories) && $categories) ? 1 : 0);
}

$duplicate_skus = array_filter($sku_counts, function ($count) {
    return $count > 1;
});
$tree = sl11_category_tree();
$parent_names = array_keys($tree);
$child_names = array();
foreach ($tree as $children) {
    $child_names = array_merge($child_names, $children);
}
$category_counts = array('parents' => 0, 'children' => 0, 'depth3' => 0, 'duplicates' => array(), 'html_entity_names' => array());
$raw_category_rows = $wpdb->get_results($wpdb->prepare(
    "SELECT terms.term_id, terms.name, taxonomy.parent FROM {$wpdb->terms} terms INNER JOIN {$wpdb->term_taxonomy} taxonomy ON taxonomy.term_id = terms.term_id WHERE taxonomy.taxonomy = %s",
    'product_cat'
), ARRAY_A);
$category_rows_by_id = array();
$expected_parent_by_child = array();
foreach ($tree as $parent_name => $children) {
    foreach ($children as $child_name) {
        $expected_parent_by_child[$child_name] = $parent_name;
    }
}
$category_name_counts = array();
foreach ($raw_category_rows as $row) {
    $term_id = (int) $row['term_id'];
    $parent_id = (int) $row['parent'];
    $category_rows_by_id[$term_id] = $row;
    $category_name_counts[$parent_id . ':' . $row['name']] = ($category_name_counts[$parent_id . ':' . $row['name']] ?? 0) + 1;
    if ($parent_id === 0 && in_array($row['name'], $parent_names, true)) {
        $category_counts['parents']++;
    }
    if ($parent_id > 0 && in_array($row['name'], $child_names, true)) {
        $category_counts['children']++;
        $actual_parent = $category_rows_by_id[$parent_id]['name'] ?? '';
        if (isset($expected_parent_by_child[$row['name']]) && $actual_parent !== $expected_parent_by_child[$row['name']]) {
            $category_counts['duplicates'][] = $row['name'] . ' has wrong parent ' . $actual_parent;
        }
    }
    if (strpos($row['name'], '&amp;') !== false) {
        $category_counts['html_entity_names'][] = $row['name'];
    }
}
foreach ($raw_category_rows as $row) {
    $parent_id = (int) $row['parent'];
    if ($parent_id > 0 && isset($category_rows_by_id[$parent_id]) && (int) $category_rows_by_id[$parent_id]['parent'] > 0) {
        $category_counts['depth3']++;
    }
}
foreach ($category_name_counts as $key => $count) {
    if ($count > 1) {
        $category_counts['duplicates'][] = $key;
    }
}
$all_attributes = function_exists('wc_get_attribute_taxonomies') ? wc_get_attribute_taxonomies() : array();
$required_attribute_names = array_map(function ($definition) {
    return $definition['slug'];
}, sl11_attribute_definitions());
$registered_attribute_names = array_map(function ($attribute) {
    return $attribute->attribute_name;
}, $all_attributes);
$attribute_definitions_ok = count($all_attributes) === 8 && !array_diff($required_attribute_names, $registered_attribute_names);
$smartlife_brand = taxonomy_exists('product_brand') ? get_term_by('name', 'SmartLife', 'product_brand') : false;
$front_page_id = (int) get_option('page_on_front');
$front_page = $front_page_id ? get_post($front_page_id) : false;
$cart_id = (int) get_option('woocommerce_cart_page_id');
$checkout_id = (int) get_option('woocommerce_checkout_page_id');
$filters_code = function_exists('sl11_theme_render_filters');
$browser_evidence = get_option('smartlife_browser_evidence', array());
$screenshot_names = array('homepage.png', 'category.png', 'product-detail.png', 'cart.png', 'checkout.png', 'order-confirmation.png');
$screenshot_directory = __DIR__ . '/submission/screenshots/';
$screenshots_present = count(array_filter($screenshot_names, function ($name) use ($screenshot_directory) {
    return is_file($screenshot_directory . $name) && filesize($screenshot_directory . $name) > 0;
}));
$browser_flow_verified = is_array($browser_evidence)
    && !empty($browser_evidence['filters_combined_and_refresh'])
    && !empty($browser_evidence['cart_quantity_and_remove'])
    && !empty($browser_evidence['checkout_submit'])
    && !empty($browser_evidence['order_confirmation'])
    && $screenshots_present === 6;

$report = array(
    'woocommerce_active' => class_exists('WooCommerce'),
    'catalog_products_found' => count($products),
    'products_published' => count(array_filter($products, function ($product) {
        return $product->get_status() === 'publish';
    })),
    'sku_unique_count' => count($sku_counts),
    'sku_duplicates' => $duplicate_skus,
    'sku_issues' => $sku_issues,
    'missing' => $missing,
    'cost_issues' => $cost_issues,
    'categories' => $category_counts,
    'attribute_definitions_expected_8' => $attribute_definitions_ok,
    'attribute_assignments' => $attribute_assignments,
    'brand_smartlife_exists' => (bool) $smartlife_brand,
    'brand_product_assignments' => $missing['brand'] === 0 ? count($products) : count($products) - $missing['brand'],
    'category_product_assignments' => $category_assignments['assigned'] ?? 0,
    'brand_terms' => taxonomy_exists('product_brand') ? wp_count_terms(array('taxonomy' => 'product_brand', 'hide_empty' => false)) : 0,
    'connection_terms' => taxonomy_exists('pa_ket-noi') ? wp_count_terms(array('taxonomy' => 'pa_ket-noi', 'hide_empty' => false)) : 0,
    'power_terms' => taxonomy_exists('pa_cong-suat') ? wp_count_terms(array('taxonomy' => 'pa_cong-suat', 'hide_empty' => false)) : 0,
    'control_terms' => taxonomy_exists('pa_dieu-khien') ? wp_count_terms(array('taxonomy' => 'pa_dieu-khien', 'hide_empty' => false)) : 0,
    'products_with_featured_image' => count($image_bytes),
    'unique_featured_attachments' => count(array_unique($image_attachment_ids)),
    'image_filename_issues' => $image_filename_issues,
    'optimized_images' => $optimized_images,
    'images_not_optimized' => $image_not_optimized,
    'max_image_bytes' => $image_bytes ? max($image_bytes) : 0,
    'homepage' => array('is_static_page' => get_option('show_on_front') === 'page', 'title' => $front_page ? $front_page->post_title : '', 'status' => $front_page ? $front_page->post_status : ''),
    'cart_page_publish' => $cart_id && get_post_status($cart_id) === 'publish',
    'checkout_page_publish' => $checkout_id && get_post_status($checkout_id) === 'publish',
    'guest_checkout' => get_option('woocommerce_enable_guest_checkout') === 'yes',
    'filters_code_loaded' => $filters_code,
    'browser_flow_verified' => $browser_flow_verified,
    'browser_screenshots_present' => $screenshots_present,
    'browser_evidence' => $browser_evidence,
);

$blocking_missing = $missing;
unset($blocking_missing['image']);
$has_data_failures = count($products) !== 40 || $report['products_published'] !== 40 || $report['sku_unique_count'] !== 40 || $duplicate_skus || $sku_issues || array_sum($blocking_missing) > 0 || count(array_unique($image_attachment_ids)) !== 40 || $image_filename_issues || $image_not_optimized || !$attribute_definitions_ok || $category_counts['parents'] !== 6 || $category_counts['children'] !== 22 || $category_counts['depth3'] > 0 || $category_counts['html_entity_names'] || !$smartlife_brand;
$report['final'] = $has_data_failures ? 'NEED FIX' : 'PARTIAL';
if (!$has_data_failures && !$missing['image'] && $report['browser_flow_verified']) {
    $report['final'] = 'PASS';
}

$lines = array(
    '=== WORDPRESS ===',
    'WooCommerce active: ' . ($report['woocommerce_active'] ? 'YES' : 'NO'),
    '',
    '=== CATEGORIES ===',
    'Parent categories: ' . $category_counts['parents'],
    'Child categories: ' . $category_counts['children'],
    'Depth 3 categories: ' . $category_counts['depth3'],
    'HTML entity names: ' . count($category_counts['html_entity_names']),
    '',
    '=== ATTRIBUTES ===',
    'Exactly 8 global attributes: ' . ($attribute_definitions_ok ? 'YES' : 'NO'),
    'Products missing attribute assignments: ' . $missing['attributes'],
    '',
    '=== BRAND ===',
    'SmartLife product_brand: ' . ($smartlife_brand ? 'YES' : 'NO'),
    'Brand terms: ' . $report['brand_terms'],
    '',
    '=== PRODUCTS ===',
    'Manifest products found: ' . count($products),
    'Published: ' . $report['products_published'],
    'Placeholder names: ' . $missing['placeholder_name'],
    '',
    '=== SKU ===',
    'Unique SKU: ' . $report['sku_unique_count'],
    'Duplicate SKU values: ' . count($duplicate_skus),
    'SKU issues: ' . count($sku_issues),
    '',
    '=== DATA COMPLETENESS ===',
    'Missing price: ' . $missing['price'],
    'Missing stock: ' . $missing['stock'],
    'Invalid/missing cost: ' . $missing['cost'],
    'Missing featured image: ' . $missing['image'],
    'Featured images: ' . count($image_bytes) . '/40',
    'Unique attachments: ' . count(array_unique($image_attachment_ids)) . '/40',
    'Image filename issues: ' . count($image_filename_issues),
    'Optimized images: ' . $optimized_images . '/40',
    'Missing weight: ' . $missing['weight'],
    'Missing dimensions: ' . $missing['dimensions'],
    'Missing short description: ' . $missing['short_description'],
    'Missing description: ' . $missing['description'],
    'Missing tags: ' . $missing['tags'],
    '',
    '=== ASSIGNMENTS ===',
    'Missing brand assignment: ' . $missing['brand'],
    'Missing category assignment: ' . $missing['category'],
    'Attribute assignment coverage: ' . wp_json_encode($attribute_assignments, JSON_UNESCAPED_UNICODE),
    'Brand terms: ' . $report['brand_terms'],
    'Connection terms: ' . $report['connection_terms'],
    'Power terms: ' . $report['power_terms'],
    'Control terms: ' . $report['control_terms'],
    '',
    '=== STOREFRONT ===',
    'Homepage: ' . ($report['homepage']['is_static_page'] ? $report['homepage']['title'] : 'not configured'),
    'Filters code loaded: ' . ($filters_code ? 'YES' : 'NO'),
    'Cart page published: ' . ($report['cart_page_publish'] ? 'YES' : 'NO'),
    'Checkout page published: ' . ($report['checkout_page_publish'] ? 'YES' : 'NO'),
    'Guest checkout: ' . ($report['guest_checkout'] ? 'YES' : 'NO'),
    'Browser flow verified: ' . ($browser_flow_verified ? 'YES' : 'NO'),
    'Submission screenshots: ' . $screenshots_present . '/6',
    'Browser evidence: ' . ($browser_flow_verified ? 'recorded' : 'not recorded'),
    '',
    '=== FINAL ===',
    $report['final'],
    '',
    'JSON:',
    wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
);

if (PHP_SAPI === 'cli') {
    echo implode(PHP_EOL, $lines) . PHP_EOL;
} else {
    nocache_headers();
    echo '<pre>' . esc_html(implode("\n", $lines)) . '</pre>';
}
