<?php

defined('ABSPATH') || exit;

function sl11_category_tree()
{
    return array(
        'Thiết bị chiếu sáng thông minh' => array('Bóng đèn thông minh', 'Đèn LED thông minh', 'Đèn ngủ thông minh'),
        'Thiết bị an ninh' => array('Camera thông minh', 'Chuông cửa thông minh', 'Khóa cửa thông minh', 'Cảm biến an ninh'),
        'Thiết bị điều khiển' => array('Công tắc thông minh', 'Ổ cắm thông minh', 'Hub điều khiển', 'Remote thông minh'),
        'Thiết bị gia dụng thông minh' => array('Robot hút bụi', 'Máy lọc không khí', 'Máy hút ẩm', 'Rèm cửa thông minh'),
        'Cảm biến thông minh' => array('Cảm biến chuyển động', 'Cảm biến cửa', 'Cảm biến nhiệt độ và độ ẩm', 'Cảm biến khói'),
        'Thiết bị mạng & phụ kiện' => array('Bộ phát Wi-Fi', 'Bộ điều khiển trung tâm', 'Phụ kiện nhà thông minh'),
    );
}

function sl11_attribute_definitions()
{
    return array(
        'Thương hiệu' => array('slug' => 'thuong-hieu', 'terms' => array('SmartLife')),
        'Kết nối' => array('slug' => 'ket-noi', 'terms' => array('Wi-Fi', 'Bluetooth', 'Zigbee', 'Wi-Fi + Bluetooth')),
        'Màu sắc' => array('slug' => 'mau-sac', 'terms' => array('Trắng', 'Đen', 'Xám', 'Vàng')),
        'Công suất' => array('slug' => 'cong-suat', 'terms' => array('5W', '7W', '9W', '12W', '15W', '20W', '50W', '100W', '500W', '1000W')),
        'Điện áp' => array('slug' => 'dien-ap', 'terms' => array('5V', '12V', '24V', '220V')),
        'Điều khiển' => array('slug' => 'dieu-khien', 'terms' => array('Ứng dụng', 'Remote', 'Giọng nói', 'Nút nhấn')),
        'Tương thích' => array('slug' => 'tuong-thich', 'terms' => array('Google Home', 'Amazon Alexa', 'Apple HomeKit', 'SmartLife App')),
        'Bảo hành' => array('slug' => 'bao-hanh', 'terms' => array('12 tháng', '24 tháng')),
    );
}

function sl11_attribute_taxonomy($slug)
{
    return 'pa_' . sanitize_title($slug);
}

function sl11_invalidate_attribute_cache()
{
    delete_transient('wc_attribute_taxonomies');
    if (class_exists('WC_Cache_Helper') && method_exists('WC_Cache_Helper', 'invalidate_cache_group')) {
        WC_Cache_Helper::invalidate_cache_group('woocommerce-attributes');
    }
}

function sl11_get_or_create_term($name, $taxonomy, $parent = 0)
{
    $existing = term_exists($name, $taxonomy, (int) $parent);
    if ($existing) {
        return (int) (is_array($existing) ? $existing['term_id'] : $existing);
    }

    $result = wp_insert_term($name, $taxonomy, array('parent' => (int) $parent));
    return is_wp_error($result) ? 0 : (int) $result['term_id'];
}

function sl11_ensure_attribute($label, $slug)
{
    global $wpdb;

    $attribute_id = $wpdb->get_var($wpdb->prepare(
        "SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s LIMIT 1",
        sanitize_title($slug)
    ));

    if (!$attribute_id) {
        $attribute_id = wc_create_attribute(array(
            'name' => $label,
            'slug' => sanitize_title($slug),
            'type' => 'select',
            'order_by' => 'name',
            'has_archives' => false,
        ));
        if (is_wp_error($attribute_id)) {
            return $attribute_id;
        }
        sl11_invalidate_attribute_cache();
    }

    $taxonomy = sl11_attribute_taxonomy($slug);
    if (!taxonomy_exists($taxonomy)) {
        register_taxonomy($taxonomy, array('product'), array(
            'hierarchical' => false,
            'show_ui' => false,
            'query_var' => true,
            'rewrite' => false,
        ));
    }

    return (int) $attribute_id;
}

function sl11_find_catalog_product($entry)
{
    global $wpdb;

    $product_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
        '_smartlife_catalog_key',
        $entry['legacy_sku']
    ));
    if ($product_id) {
        return wc_get_product((int) $product_id);
    }

    $product_id = wc_get_product_id_by_sku($entry['legacy_sku']);
    if (!$product_id) {
        $product_id = wc_get_product_id_by_sku($entry['sku']);
    }

    return $product_id ? wc_get_product((int) $product_id) : false;
}

function sl11_product_has_order_reference($product_id, $sku)
{
    global $wpdb;

    $itemmeta = $wpdb->prefix . 'woocommerce_order_itemmeta';
    $references = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$itemmeta} WHERE (meta_key IN (%s, %s) AND meta_value = %s) OR (meta_key = %s AND meta_value = %s)",
        '_product_id',
        '_variation_id',
        (string) $product_id,
        '_sku',
        (string) $sku
    ));

    return (int) $references > 0;
}

function sl11_product_profile($entry, $index)
{
    $category = $entry['category'];
    $power = '5W';
    if (preg_match('/\b(5|7|9|12|15|20)W\b/i', $entry['name'], $matches)) {
        $power = strtoupper($matches[0]);
    } elseif (in_array($category, array('Robot hút bụi', 'Máy lọc không khí', 'Máy hút ẩm', 'Bộ phát Wi-Fi'), true)) {
        $power = '20W';
    } elseif (in_array($category, array('Bóng đèn thông minh', 'Đèn LED thông minh', 'Đèn ngủ thông minh'), true)) {
        $power = '9W';
    } elseif ($category === 'Rèm cửa thông minh') {
        $power = '12W';
    }

    $connection = 'Wi-Fi';
    if (in_array($category, array('Hub điều khiển', 'Cảm biến an ninh', 'Cảm biến chuyển động', 'Cảm biến cửa', 'Cảm biến nhiệt độ và độ ẩm', 'Cảm biến khói', 'Bộ điều khiển trung tâm'), true)) {
        $connection = 'Zigbee';
    } elseif ($category === 'Remote thông minh') {
        $connection = 'Bluetooth';
    } elseif ($category === 'Đèn LED thông minh' && $entry['sku'] === 'SL-LED-0001') {
        $connection = 'Wi-Fi + Bluetooth';
    }

    $voltage = '12V';
    if (in_array($category, array('Bóng đèn thông minh', 'Đèn LED thông minh', 'Đèn ngủ thông minh', 'Công tắc thông minh', 'Ổ cắm thông minh'), true)) {
        $voltage = '220V';
    } elseif (in_array($category, array('Robot hút bụi', 'Máy lọc không khí', 'Máy hút ẩm'), true)) {
        $voltage = '24V';
    } elseif (in_array($category, array('Cảm biến an ninh', 'Cảm biến chuyển động', 'Cảm biến cửa', 'Cảm biến nhiệt độ và độ ẩm', 'Cảm biến khói', 'Remote thông minh', 'Phụ kiện nhà thông minh'), true)) {
        $voltage = '5V';
    }

    $control = 'Ứng dụng';
    if ($category === 'Remote thông minh') {
        $control = 'Remote';
    } elseif ($category === 'Công tắc thông minh') {
        $control = 'Nút nhấn';
    } elseif (in_array($category, array('Bóng đèn thông minh', 'Đèn LED thông minh', 'Đèn ngủ thông minh', 'Camera thông minh', 'Chuông cửa thông minh', 'Khóa cửa thông minh', 'Ổ cắm thông minh', 'Robot hút bụi', 'Máy lọc không khí', 'Máy hút ẩm', 'Rèm cửa thông minh', 'Cảm biến an ninh', 'Cảm biến chuyển động', 'Cảm biến cửa', 'Cảm biến nhiệt độ và độ ẩm', 'Cảm biến khói', 'Bộ phát Wi-Fi', 'Bộ điều khiển trung tâm', 'Phụ kiện nhà thông minh'), true)) {
        $control = 'Ứng dụng';
    }
    $compatibilities = array('SmartLife App', 'Google Home', 'Amazon Alexa', 'Apple HomeKit');
    $colors = array('Trắng', 'Đen', 'Xám', 'Vàng');

    return array(
        'connection' => $connection,
        'power' => $power,
        'voltage' => $voltage,
        'control' => $control,
        'compatibility' => $compatibilities[$index % count($compatibilities)],
        'color' => $colors[$index % count($colors)],
        'warranty' => $index % 3 === 0 ? '24 tháng' : '12 tháng',
    );
}

function sl11_measurements($category)
{
    $measurements = array(
        'Bóng đèn thông minh' => array('0.12', '6', '6', '12'),
        'Đèn LED thông minh' => array('0.30', '18', '12', '7'),
        'Đèn ngủ thông minh' => array('0.35', '12', '12', '18'),
        'Camera thông minh' => array('0.45', '12', '8', '8'),
        'Chuông cửa thông minh' => array('0.30', '14', '6', '3'),
        'Khóa cửa thông minh' => array('1.50', '30', '10', '8'),
        'Cảm biến an ninh' => array('0.15', '7', '7', '3'),
        'Công tắc thông minh' => array('0.20', '12', '8', '4'),
        'Ổ cắm thông minh' => array('0.18', '8', '6', '6'),
        'Hub điều khiển' => array('0.25', '10', '10', '3'),
        'Remote thông minh' => array('0.12', '15', '4', '2'),
        'Robot hút bụi' => array('3.20', '35', '35', '10'),
        'Máy lọc không khí' => array('4.00', '30', '20', '45'),
        'Máy hút ẩm' => array('8.00', '30', '25', '50'),
        'Rèm cửa thông minh' => array('1.50', '25', '8', '8'),
        'Cảm biến chuyển động' => array('0.10', '7', '7', '3'),
        'Cảm biến cửa' => array('0.08', '6', '4', '2'),
        'Cảm biến nhiệt độ và độ ẩm' => array('0.08', '7', '7', '3'),
        'Cảm biến khói' => array('0.18', '10', '10', '4'),
        'Bộ phát Wi-Fi' => array('0.40', '20', '15', '5'),
        'Bộ điều khiển trung tâm' => array('0.30', '12', '10', '4'),
        'Phụ kiện nhà thông minh' => array('0.12', '15', '10', '4'),
    );

    return $measurements[$category] ?? array('0.20', '12', '8', '4');
}

function sl11_category_copy($category)
{
    $copy = array(
        'Bóng đèn thông minh' => 'Điều chỉnh ánh sáng trong phòng theo nhu cầu sinh hoạt và lịch sử dụng thiết bị.',
        'Đèn LED thông minh' => 'Tạo vùng chiếu sáng linh hoạt cho bàn làm việc, trần nhà hoặc góc sinh hoạt.',
        'Đèn ngủ thông minh' => 'Mang lại ánh sáng dịu cho khu vực nghỉ ngơi và thao tác thuận tiện trước giờ ngủ.',
        'Camera thông minh' => 'Hỗ trợ quan sát không gian gia đình và kiểm tra hình ảnh từ ứng dụng tương thích.',
        'Chuông cửa thông minh' => 'Giúp nhận biết khách đến cửa và kết hợp chuông báo với nhu cầu quan sát lối vào.',
        'Khóa cửa thông minh' => 'Bổ sung phương thức kiểm soát lối vào cho gia đình trong hệ thống nhà thông minh.',
        'Cảm biến an ninh' => 'Theo dõi trạng thái an ninh tại khu vực lắp đặt và gửi thông tin tới hệ sinh thái kết nối.',
        'Công tắc thông minh' => 'Điều khiển mạch điện gia dụng thuận tiện bằng thao tác tại chỗ hoặc qua ứng dụng.',
        'Ổ cắm thông minh' => 'Quản lý thiết bị cắm điện theo lịch và trạng thái sử dụng trong không gian gia đình.',
        'Hub điều khiển' => 'Kết nối thiết bị tương thích thành một hệ thống điều khiển tập trung.',
        'Remote thông minh' => 'Tập hợp thao tác điều khiển thiết bị trong một phụ kiện cầm tay nhỏ gọn.',
        'Robot hút bụi' => 'Hỗ trợ làm sạch sàn theo lịch phù hợp với nếp sinh hoạt trong gia đình.',
        'Máy lọc không khí' => 'Phù hợp bố trí trong phòng sinh hoạt để hỗ trợ quản lý chất lượng không khí trong nhà.',
        'Máy hút ẩm' => 'Hỗ trợ kiểm soát độ ẩm tại phòng kín và không gian lưu trữ gia đình.',
        'Rèm cửa thông minh' => 'Hỗ trợ đóng mở rèm theo lịch hoặc thao tác điều khiển của người dùng.',
        'Cảm biến chuyển động' => 'Phát hiện chuyển động tại khu vực lắp đặt để kích hoạt kịch bản nhà thông minh.',
        'Cảm biến cửa' => 'Theo dõi trạng thái đóng mở cửa và hỗ trợ tạo thông báo hoặc kịch bản tự động.',
        'Cảm biến nhiệt độ và độ ẩm' => 'Theo dõi điều kiện môi trường trong phòng để người dùng chủ động điều chỉnh thiết bị.',
        'Cảm biến khói' => 'Bổ sung lớp cảnh báo khói tại khu vực sinh hoạt theo cấu hình hệ thống tương thích.',
        'Bộ phát Wi-Fi' => 'Cung cấp kết nối mạng cho thiết bị gia đình và mở rộng khả năng quản lý thiết bị.',
        'Bộ điều khiển trung tâm' => 'Làm điểm điều phối thiết bị tương thích và các kịch bản tự động trong nhà.',
        'Phụ kiện nhà thông minh' => 'Bổ sung phụ kiện lắp đặt hoặc cấp nguồn cho thiết bị trong hệ thống SmartLife.',
    );

    return $copy[$category] ?? 'Bổ sung tiện ích kết nối cho không gian nhà thông minh.';
}

function sl11_term_id($name, $taxonomy)
{
    $term = get_term_by('name', $name, $taxonomy);
    return $term && !is_wp_error($term) ? (int) $term->term_id : 0;
}

function sl11_assign_attributes($product, $profile, $attribute_ids)
{
    $values = array(
        'thuong-hieu' => 'SmartLife',
        'ket-noi' => $profile['connection'],
        'mau-sac' => $profile['color'],
        'cong-suat' => $profile['power'],
        'dien-ap' => $profile['voltage'],
        'dieu-khien' => $profile['control'],
        'tuong-thich' => $profile['compatibility'],
        'bao-hanh' => $profile['warranty'],
    );
    $attributes = array();
    $position = 0;

    foreach ($values as $slug => $value) {
        $taxonomy = sl11_attribute_taxonomy($slug);
        $term_id = sl11_term_id($value, $taxonomy);
        if (!$term_id) {
            continue;
        }

        $attribute = new WC_Product_Attribute();
        $attribute->set_id((int) ($attribute_ids[$slug] ?? 0));
        $attribute->set_name($taxonomy);
        $attribute->set_options(array($term_id));
        $attribute->set_position($position++);
        $attribute->set_visible(true);
        $attribute->set_variation(false);
        $attributes[$taxonomy] = $attribute;
    }

    $product->set_attributes($attributes);
}

function sl11_attach_local_product_image($product)
{
    $upload = wp_upload_dir();
    $base_dir = trailingslashit($upload['basedir']) . 'smartlife-products/';
    $extensions = array('webp', 'jpg', 'jpeg', 'png');

    foreach ($extensions as $extension) {
        $path = $base_dir . sanitize_file_name($product->get_sku()) . '.' . $extension;
        if (!is_file($path)) {
            continue;
        }

        $relative_path = 'smartlife-products/' . basename($path);
        $attachment_id = attachment_url_to_postid(trailingslashit($upload['baseurl']) . $relative_path);
        if (!$attachment_id) {
            $filetype = wp_check_filetype(basename($path), null);
            $attachment_id = wp_insert_attachment(array(
                'post_mime_type' => $filetype['type'],
                'post_title' => $product->get_name() . ' - demo image',
                'post_status' => 'inherit',
            ), $path, 0);
            if ($attachment_id && !is_wp_error($attachment_id)) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $path));
            }
        }

        if ($attachment_id && !is_wp_error($attachment_id)) {
            $product->set_image_id((int) $attachment_id);
        }
        return;
    }
}

function sl11_has_order_reference($product_id, $sku)
{
    global $wpdb;
    $table = $wpdb->prefix . 'woocommerce_order_itemmeta';
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE (meta_key IN (%s, %s) AND meta_value = %s) OR (meta_key = %s AND meta_value = %s)",
        '_product_id',
        '_variation_id',
        (string) $product_id,
        '_sku',
        (string) $sku
    ));
    return (int) $count > 0;
}

function sl11_sync_homepage($product_ids, $category_map)
{
    $page = get_page_by_path('smartlife-home');
    $category_cards = '';
    foreach (sl11_category_tree() as $parent_name => $children) {
        $term_id = $category_map[$parent_name]['term_id'] ?? 0;
        $url = $term_id ? get_term_link((int) $term_id, 'product_cat') : '';
        if (is_wp_error($url) || !$url) {
            continue;
        }
        $category_cards .= '<a class="smartlife-category-link" href="' . esc_url($url) . '"><strong>' . esc_html($parent_name) . '</strong><span>' . esc_html(implode(' · ', $children)) . '</span></a>';
    }

    $featured_ids = implode(',', array_map('absint', array_slice($product_ids, 0, 8)));
    $content = '<!-- wp:html -->'
        . '<section class="smartlife-home-hero"><p class="smartlife-eyebrow">NHÀ THÔNG MINH, DỄ BẮT ĐẦU</p><h1>SmartLife – Thế giới nhà thông minh</h1><p>Thiết bị chiếu sáng, an ninh, điều khiển và gia dụng thông minh cho ngôi nhà kết nối.</p><a class="smartlife-primary-link" href="' . esc_url(wc_get_page_permalink('shop')) . '">Khám phá cửa hàng</a><small>Nội dung cửa hàng demo phục vụ bài thực hành.</small></section>'
        . '<section class="smartlife-home-categories"><h2>Khám phá theo danh mục</h2><div class="smartlife-category-grid">' . $category_cards . '</div></section>'
        . '<!-- /wp:html -->'
        . '<!-- wp:heading --><h2 class="wp-block-heading">Sản phẩm SmartLife</h2><!-- /wp:heading -->'
        . '<!-- wp:shortcode -->[products ids="' . esc_attr($featured_ids) . '" columns="4"]<!-- /wp:shortcode -->';

    $post_data = array(
        'post_title' => 'SmartLife – Thế giới nhà thông minh',
        'post_name' => 'smartlife-home',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => $content,
    );
    if ($page) {
        $post_data['ID'] = $page->ID;
        $page_id = wp_update_post($post_data, true);
    } else {
        $page_id = wp_insert_post($post_data, true);
    }

    if (!is_wp_error($page_id) && $page_id) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $page_id);
    }
}

function sl11_configure_demo_gateways()
{
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    if (!in_array($host, array('localhost', '127.0.0.1', '::1'), true)) {
        return;
    }

    $sepay = get_option('woocommerce_sepay_settings', array());
    if (is_array($sepay) && isset($sepay['enabled'])) {
        $sepay['enabled'] = 'no';
        update_option('woocommerce_sepay_settings', $sepay);
    }

    $demo_gateways = array(
        'bacs' => array(
            'title' => 'Chuyển khoản thủ công (demo)',
            'description' => 'Phương thức demo/offline cho bài thực hành. Không chuyển tiền thật và không phát sinh giao dịch.',
        ),
        'cheque' => array(
            'title' => 'Thanh toán demo/offline',
            'description' => 'Thanh toán demo/offline cho bài thực hành. Không kết nối MoMo, thẻ ATM hoặc cổng thanh toán thật.',
        ),
        'cod' => array(
            'title' => 'Thanh toán khi nhận hàng (demo)',
            'description' => 'Tùy chọn minh họa cho bài thực hành; không tạo giao dịch thanh toán thật.',
        ),
    );
    foreach ($demo_gateways as $gateway_id => $values) {
        $settings = get_option('woocommerce_' . $gateway_id . '_settings', array());
        if (is_array($settings)) {
            update_option('woocommerce_' . $gateway_id . '_settings', array_merge($settings, $values));
        }
    }
}

function sl11_import($apply = false)
{
    $catalog = require __DIR__ . '/smartlife-catalog.php';
    $categories = sl11_category_tree();
    $definitions = sl11_attribute_definitions();
    $errors = array();
    $products = array();
    $seen_ids = array();
    $seen_skus = array();

    if (count($catalog) !== 40 || count($categories) !== 6 || array_sum(array_map('count', $categories)) !== 22 || count($definitions) !== 8) {
        return array('ok' => false, 'errors' => array('Manifest/category/attribute counts do not match the agreed rubric.'));
    }

    foreach ($catalog as $index => $entry) {
        if (isset($seen_skus[$entry['sku']])) {
            $errors[] = 'Duplicate target SKU in manifest: ' . $entry['sku'];
        }
        $seen_skus[$entry['sku']] = true;

        $product = sl11_find_catalog_product($entry);
        if (!$product) {
            $errors[] = 'Existing product not found for catalog key ' . $entry['legacy_sku'] . '; importer will not create a 41st product.';
            continue;
        }
        if (isset($seen_ids[$product->get_id()])) {
            $errors[] = 'Two catalog rows resolve to the same product ID ' . $product->get_id();
            continue;
        }
        $seen_ids[$product->get_id()] = true;

        $sku_owner = wc_get_product_id_by_sku($entry['sku']);
        if ($sku_owner && (int) $sku_owner !== $product->get_id()) {
            $errors[] = 'Target SKU is already owned by another product: ' . $entry['sku'];
        }

        $entry['product'] = $product;
        $entry['keep_sku'] = sl11_has_order_reference($product->get_id(), $product->get_sku());
        $products[] = $entry;
    }

    if (count($products) !== 40) {
        $errors[] = 'Preflight resolved ' . count($products) . ' products; exactly 40 are required.';
    }
    if ($errors) {
        return array('ok' => false, 'errors' => $errors, 'resolved_products' => count($products));
    }

    if (!$apply) {
        return array(
            'ok' => true,
            'mode' => 'dry-run',
            'products_to_update' => count($products),
            'new_products' => 0,
            'sku_references_to_preserve' => array_values(array_map(function ($entry) {
                return $entry['keep_sku'] ? $entry['product']->get_sku() : null;
            }, array_filter($products, function ($entry) {
                return $entry['keep_sku'];
            }))),
            'categories' => array('parents' => 6, 'children' => 22),
            'attributes' => count($definitions),
        );
    }

    $category_map = array();
    foreach ($categories as $parent_name => $children) {
        $parent_id = sl11_get_or_create_term($parent_name, 'product_cat');
        if (!$parent_id) {
            $errors[] = 'Could not create category parent: ' . $parent_name;
            continue;
        }
        $category_map[$parent_name] = array('term_id' => $parent_id, 'parent_id' => 0);
        foreach ($children as $child_name) {
            $child_id = sl11_get_or_create_term($child_name, 'product_cat', $parent_id);
            if (!$child_id) {
                $errors[] = 'Could not create category child: ' . $child_name;
                continue;
            }
            $category_map[$child_name] = array('term_id' => $child_id, 'parent_id' => $parent_id);
        }
    }

    foreach (get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false)) as $existing_category) {
        $decoded_name = html_entity_decode($existing_category->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded_name === 'Thiết bị mạng & phụ kiện' && $existing_category->name !== $decoded_name) {
            remove_filter('pre_term_name', 'wp_filter_kses', 10);
            remove_filter('pre_term_name', '_wp_specialchars', 30);
            $updated_category = wp_update_term((int) $existing_category->term_id, 'product_cat', array(
                'name' => $decoded_name,
                'slug' => 'thiet-bi-mang-phu-kien',
            ));
            add_filter('pre_term_name', 'wp_filter_kses', 10);
            add_filter('pre_term_name', '_wp_specialchars', 30);
            if (is_wp_error($updated_category)) {
                $errors[] = 'Could not normalize encoded category name: ' . $updated_category->get_error_message();
            }
        }
    }

    $attribute_ids = array();
    foreach ($definitions as $label => $definition) {
        $attribute_id = sl11_ensure_attribute($label, $definition['slug']);
        if (is_wp_error($attribute_id)) {
            $errors[] = 'Could not create attribute ' . $label . ': ' . $attribute_id->get_error_message();
            continue;
        }
        $attribute_ids[$definition['slug']] = (int) $attribute_id;
        $taxonomy = sl11_attribute_taxonomy($definition['slug']);
        foreach ($definition['terms'] as $term_name) {
            if (!sl11_get_or_create_term($term_name, $taxonomy)) {
                $errors[] = 'Could not create attribute term ' . $term_name . ' in ' . $taxonomy;
            }
        }
    }

    if (!taxonomy_exists('product_brand')) {
        $errors[] = 'WooCommerce product_brand taxonomy is not registered; brand assignment was not attempted.';
    }
    $brand_id = taxonomy_exists('product_brand') ? sl11_get_or_create_term('SmartLife', 'product_brand') : 0;
    if (!$brand_id) {
        $errors[] = 'SmartLife brand term is unavailable in product_brand.';
    }
    if ($errors) {
        return array('ok' => false, 'errors' => $errors, 'stage' => 'taxonomy setup');
    }

    $product_ids = array();
    $order_skus_preserved = array();
    foreach ($products as $index => $entry) {
        $product = $entry['product'];
        $profile = sl11_product_profile($entry, $index);
        $category_id = $category_map[$entry['category']]['term_id'] ?? 0;
        if (!$category_id) {
            $errors[] = 'Missing category mapping for ' . $entry['category'];
            continue;
        }

        $product->set_name($entry['name']);
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        if (in_array($product->get_slug(), array('san-pham', 'product', 'test-product'), true)) {
            $product->set_slug(sanitize_title($entry['name']));
        }
        if (!empty($entry['slug'])) {
            $product->set_slug(sanitize_title($entry['slug']));
        }
        if (!$entry['keep_sku']) {
            $product->set_sku($entry['sku']);
        } else {
            $order_skus_preserved[] = array('product_id' => $product->get_id(), 'sku' => $product->get_sku());
            $product->update_meta_data('_smartlife_preserved_order_sku', $product->get_sku());
        }
        if (!$product->get_regular_price()) {
            $product->set_regular_price((string) $entry['price']);
        }
        if (!$product->managing_stock() || $product->get_stock_quantity() === null) {
            $product->set_manage_stock(true);
            $product->set_stock_quantity(20);
        }
        $product->set_stock_status('instock');
        $product->set_category_ids(array((int) $category_id));
        $product->set_weight(sl11_measurements($entry['category'])[0]);
        $dimensions = sl11_measurements($entry['category']);
        $product->set_length($dimensions[1]);
        $product->set_width($dimensions[2]);
        $product->set_height($dimensions[3]);

        $copy = sl11_category_copy($entry['category']);
        $short = $entry['name'] . ' thuộc danh mục ' . $entry['category'] . '. ' . $copy . ' Kết nối ' . $profile['connection'] . ', điều khiển bằng ' . $profile['control'] . '.';
        $description = '<p><strong>' . esc_html($entry['name']) . '</strong> được thiết kế cho nhóm ' . esc_html($entry['category']) . ' trong hệ sinh thái SmartLife.</p>'
            . '<p>' . esc_html($copy) . ' Thiết bị hỗ trợ kết nối ' . esc_html($profile['connection']) . ', điều khiển bằng ' . esc_html($profile['control']) . ' và tương thích với ' . esc_html($profile['compatibility']) . '.</p>'
            . '<p>Thông số demo: công suất ' . esc_html($profile['power']) . ', điện áp ' . esc_html($profile['voltage']) . ', bảo hành tham khảo ' . esc_html($profile['warranty']) . '.</p>'
            . '<p>Nội dung và thông số là dữ liệu demo phục vụ bài thực hành, không phải cam kết kinh doanh thực tế.</p>';
        $product->set_short_description($short);
        $product->set_description($description);

        sl11_assign_attributes($product, $profile, $attribute_ids);
        sl11_attach_local_product_image($product);

        $product_id = $product->save();
        if (!$product_id) {
            $errors[] = 'Could not save product ' . $entry['legacy_sku'];
            continue;
        }
        update_post_meta($product_id, '_smartlife_catalog_key', $entry['legacy_sku']);

        wp_set_object_terms($product_id, array((int) $brand_id), 'product_brand', false);
        $tags = array('SmartLife', $entry['category'], $profile['connection'], $profile['control']);
        wp_set_object_terms($product_id, array_values(array_unique($tags)), 'product_tag', false);

        $sale_price = $product->get_sale_price();
        $selling_price = (float) $product->get_regular_price();
        if ($sale_price !== '' && (float) $sale_price > 0) {
            $selling_price = min($selling_price, (float) $sale_price);
        }
        $cost = max(1, (int) (floor(($selling_price * 0.45) / 1000) * 1000));
        $product->update_meta_data('_cost_of_goods', (string) $cost);
        $product->update_meta_data('_smartlife_cost_type', 'demo');
        $product->save();
        $product_ids[] = (int) $product_id;
    }

    $junk_names = array('wi-fi-bluetooth', 'ung-dung', 'giong-noi', 'nut-nhan', 'google-home', 'amazon-alexa', 'apple-homekit', 'smartlife-app', '12-thang', '24-thang');
    foreach (array_keys($definitions) as $label) {
        $taxonomy = sl11_attribute_taxonomy($definitions[$label]['slug']);
        foreach (get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false)) as $term) {
            if (in_array($term->name, $junk_names, true) && (int) $term->count === 0) {
                wp_delete_term((int) $term->term_id, $taxonomy);
            }
        }
    }

    if (count($product_ids) === 40) {
        sl11_sync_homepage($product_ids, $category_map);
        sl11_configure_demo_gateways();
        $child_theme = 'twentytwentyfive-smartlife';
        if (wp_get_theme($child_theme)->exists()) {
            switch_theme($child_theme);
        }
    }

    sl11_invalidate_attribute_cache();
    flush_rewrite_rules(false);
    foreach ($product_ids as $product_id) {
        clean_post_cache($product_id);
        wc_delete_product_transients($product_id);
    }

    return array(
        'ok' => !$errors && count($product_ids) === 40,
        'mode' => 'apply',
        'products_updated' => count($product_ids),
        'products_created' => 0,
        'order_skus_preserved' => $order_skus_preserved,
        'errors' => $errors,
        'homepage_page' => get_page_by_path('smartlife-home') ? get_page_by_path('smartlife-home')->ID : 0,
    );
}

function sl11_importer_dispatch()
{
    if (!class_exists('WooCommerce') || !function_exists('wc_get_product')) {
        wp_die('WooCommerce chưa được kích hoạt.', 'SmartLife Importer', array('response' => 503));
    }

    $is_cli = PHP_SAPI === 'cli';
    if ($is_cli) {
        global $argv;
        $mode = $argv[1] ?? '';
        if (!in_array($mode, array('--dry-run', '--apply'), true)) {
            fwrite(STDERR, "Usage: php smartlife-import.php --dry-run|--apply\n");
            exit(2);
        }
        $result = sl11_import($mode === '--apply');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        exit(!empty($result['ok']) ? 0 : 1);
    }

    if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) {
        wp_die('Chỉ quản trị viên WooCommerce được dùng importer.', 'SmartLife Importer', array('response' => 403));
    }

    $result = null;
    if (isset($_POST['smartlife_import'])) {
        check_admin_referer('smartlife_import_action', 'smartlife_nonce');
        $result = sl11_import(true);
    }

    status_header(200);
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SmartLife Importer</title><body style="font:16px/1.5 system-ui,sans-serif;max-width:850px;margin:40px auto;padding:0 20px">';
    echo '<h1>SmartLife — đồng bộ 40 sản phẩm</h1><p>Chỉ cập nhật manifest cố định, không tạo sản phẩm thứ 41. Cost/kích thước là dữ liệu demo. Ảnh riêng sẽ được nhận từ <code>wp-content/uploads/smartlife-products/{SKU}.webp|jpg|png</code>.</p>';
    if ($result) {
        echo '<pre>' . esc_html(wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    }
    echo '<form method="post">';
    wp_nonce_field('smartlife_import_action', 'smartlife_nonce');
    echo '<button type="submit" name="smartlife_import" value="1">Đồng bộ manifest SmartLife</button></form></body></html>';
}
