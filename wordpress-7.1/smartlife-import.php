<?php

/**
 * SmartLife WooCommerce Importer
 *
 * Tạo:
 * - Cây danh mục SmartLife
 * - 8 global attributes
 * - Các terms/giá trị thuộc tính
 * - 40 hoặc 1000+ sản phẩm mẫu
 *
 * CÁCH DÙNG:
 * 1. Đặt file này vào thư mục gốc WordPress:
 *    /Shop/wordpress-7.1/smartlife-import.php
 *
 * 2. Mở:
 *    http://localhost/Shop/wordpress-7.1/smartlife-import.php
 *
 * 3. Chọn số sản phẩm và bấm Import.
 *
 * KHUYẾN NGHỊ:
 * - Chạy 40 sản phẩm trước.
 * - Kiểm tra WooCommerce.
 * - Sau đó chạy 1000 sản phẩm.
 *
 * KHÔNG chạy file này trên website production.
 */

$smartlife_wp_load = __DIR__ . '/wp-load.php';
if (!is_file($smartlife_wp_load)) {
    http_response_code(500);
    exit('Không tìm thấy wp-load.php.');
}

require_once $smartlife_wp_load;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/smartlife-import-core.php';
sl11_importer_dispatch();
__halt_compiler();
return;


/*
|--------------------------------------------------------------------------
| CẤU HÌNH
|--------------------------------------------------------------------------
*/

/*
 * Số sản phẩm mặc định.
 *
 * Có thể đổi:
 * 40
 * 100
 * 500
 * 1000
 * 2000
 */
$DEFAULT_PRODUCT_COUNT = 40;


/*
|--------------------------------------------------------------------------
| DỮ LIỆU DANH MỤC
|--------------------------------------------------------------------------
*/

$category_tree = array(

    'Thiết bị chiếu sáng thông minh' => array(
        'Bóng đèn thông minh',
        'Đèn LED thông minh',
        'Đèn ngủ thông minh',
    ),

    'Thiết bị an ninh' => array(
        'Camera thông minh',
        'Chuông cửa thông minh',
        'Khóa cửa thông minh',
        'Cảm biến an ninh',
    ),

    'Thiết bị điều khiển' => array(
        'Công tắc thông minh',
        'Ổ cắm thông minh',
        'Hub điều khiển',
        'Remote thông minh',
    ),

    'Thiết bị gia dụng thông minh' => array(
        'Robot hút bụi',
        'Máy lọc không khí',
        'Máy hút ẩm',
        'Rèm cửa thông minh',
    ),

    'Cảm biến thông minh' => array(
        'Cảm biến chuyển động',
        'Cảm biến cửa',
        'Cảm biến nhiệt độ và độ ẩm',
        'Cảm biến khói',
    ),

    'Thiết bị mạng & phụ kiện' => array(
        'Bộ phát Wi-Fi',
        'Bộ điều khiển trung tâm',
        'Phụ kiện nhà thông minh',
    ),
);


/*
|--------------------------------------------------------------------------
| GLOBAL ATTRIBUTES
|--------------------------------------------------------------------------
|
| slug không dùng dấu tiếng Việt.
|
*/

$attributes = array(

    'Thương hiệu' => array(
        'slug' => 'thuong-hieu',
        'terms' => array(
            'SmartLife',
        ),
    ),

    'Kết nối' => array(
        'slug' => 'ket-noi',
        'terms' => array(
            'Wi-Fi',
            'Bluetooth',
            'Zigbee',
            'Wi-Fi + Bluetooth',
        ),
    ),

    'Màu sắc' => array(
        'slug' => 'mau-sac',
        'terms' => array(
            'Trắng',
            'Đen',
            'Xám',
            'Vàng',
        ),
    ),

    'Công suất' => array(
        'slug' => 'cong-suat',
        'terms' => array(
            '5W',
            '7W',
            '9W',
            '12W',
            '15W',
            '20W',
            '50W',
            '100W',
            '500W',
            '1000W',
        ),
    ),

    'Điện áp' => array(
        'slug' => 'dien-ap',
        'terms' => array(
            '5V',
            '12V',
            '24V',
            '220V',
        ),
    ),

    'Điều khiển' => array(
        'slug' => 'dieu-khien',
        'terms' => array(
            'Ứng dụng',
            'Remote',
            'Giọng nói',
            'Nút nhấn',
        ),
    ),

    'Tương thích' => array(
        'slug' => 'tuong-thich',
        'terms' => array(
            'Google Home',
            'Amazon Alexa',
            'Apple HomeKit',
            'SmartLife App',
        ),
    ),

    'Bảo hành' => array(
        'slug' => 'bao-hanh',
        'terms' => array(
            '12 tháng',
            '24 tháng',
        ),
    ),
);


/*
|--------------------------------------------------------------------------
| TÊN SẢN PHẨM THEO DANH MỤC
|--------------------------------------------------------------------------
*/

$product_names = array(

    'Bóng đèn thông minh' => array(
        'Bóng đèn Wi-Fi SmartLife S1',
        'Bóng đèn RGB SmartLife Color',
        'Bóng đèn LED SmartLife Pro',
        'Bóng đèn thông minh SmartLife Home',
    ),

    'Đèn LED thông minh' => array(
        'Đèn LED SmartLife L1',
        'Đèn LED SmartLife RGB Pro',
        'Đèn LED âm trần SmartLife',
        'Đèn LED dây SmartLife',
    ),

    'Đèn ngủ thông minh' => array(
        'Đèn ngủ SmartLife Night',
        'Đèn ngủ RGB SmartLife',
        'Đèn ngủ cảm ứng SmartLife',
        'Đèn ngủ Wi-Fi SmartLife',
    ),

    'Camera thông minh' => array(
        'Camera Wi-Fi SmartLife 1080P',
        'Camera SmartLife 2K Indoor',
        'Camera SmartLife 3MP AI',
        'Camera SmartLife Outdoor Pro',
    ),

    'Chuông cửa thông minh' => array(
        'Chuông cửa SmartLife Doorbell',
        'Chuông cửa SmartLife Video',
        'Chuông cửa SmartLife Pro',
        'Chuông cửa Wi-Fi SmartLife',
    ),

    'Khóa cửa thông minh' => array(
        'Khóa cửa SmartLife Fingerprint',
        'Khóa cửa SmartLife Pro',
        'Khóa cửa SmartLife Wi-Fi',
        'Khóa cửa SmartLife Home',
    ),

    'Cảm biến an ninh' => array(
        'Cảm biến an ninh SmartLife S1',
        'Cảm biến an ninh SmartLife Pro',
        'Cảm biến chống trộm SmartLife',
        'Cảm biến bảo vệ SmartLife',
    ),

    'Công tắc thông minh' => array(
        'Công tắc SmartLife 1 nút',
        'Công tắc SmartLife 2 nút',
        'Công tắc SmartLife 3 nút',
        'Công tắc cảm ứng SmartLife',
    ),

    'Ổ cắm thông minh' => array(
        'Ổ cắm Wi-Fi SmartLife',
        'Ổ cắm SmartLife Pro',
        'Ổ cắm đo điện SmartLife',
        'Ổ cắm thông minh SmartLife S1',
    ),

    'Hub điều khiển' => array(
        'Hub SmartLife Zigbee',
        'Hub SmartLife Gateway',
        'Hub SmartLife Home',
        'Hub SmartLife Pro',
    ),

    'Remote thông minh' => array(
        'Remote SmartLife Universal',
        'Remote hồng ngoại SmartLife',
        'Remote SmartLife Pro',
        'Remote điều khiển SmartLife',
    ),

    'Robot hút bụi' => array(
        'Robot hút bụi SmartLife S1',
        'Robot hút bụi SmartLife Pro',
        'Robot hút bụi SmartLife AI',
        'Robot hút bụi SmartLife Max',
    ),

    'Máy lọc không khí' => array(
        'Máy lọc không khí SmartLife Air',
        'Máy lọc không khí SmartLife Pro',
        'Máy lọc không khí SmartLife Home',
        'Máy lọc không khí SmartLife Max',
    ),

    'Máy hút ẩm' => array(
        'Máy hút ẩm SmartLife 20L',
        'Máy hút ẩm SmartLife 30L',
        'Máy hút ẩm SmartLife Home',
        'Máy hút ẩm SmartLife Pro',
    ),

    'Rèm cửa thông minh' => array(
        'Động cơ rèm SmartLife Curtain',
        'Rèm cửa SmartLife Wi-Fi',
        'Rèm cửa SmartLife Pro',
        'Bộ điều khiển rèm SmartLife',
    ),

    'Cảm biến chuyển động' => array(
        'Cảm biến chuyển động SmartLife',
        'Cảm biến PIR SmartLife',
        'Cảm biến chuyển động Zigbee SmartLife',
        'Cảm biến chuyển động Pro',
    ),

    'Cảm biến cửa' => array(
        'Cảm biến cửa SmartLife',
        'Cảm biến cửa Zigbee SmartLife',
        'Cảm biến cửa Wi-Fi SmartLife',
        'Cảm biến cửa Pro',
    ),

    'Cảm biến nhiệt độ và độ ẩm' => array(
        'Cảm biến nhiệt độ SmartLife',
        'Cảm biến nhiệt độ độ ẩm SmartLife',
        'Cảm biến môi trường SmartLife',
        'Cảm biến nhiệt độ Zigbee SmartLife',
    ),

    'Cảm biến khói' => array(
        'Cảm biến khói SmartLife',
        'Cảm biến khói Wi-Fi SmartLife',
        'Cảm biến khói Zigbee SmartLife',
        'Cảm biến khói Pro',
    ),

    'Bộ phát Wi-Fi' => array(
        'Bộ phát Wi-Fi SmartLife AX1800',
        'Bộ phát Wi-Fi SmartLife AX3000',
        'Router SmartLife Home',
        'Router SmartLife Pro',
    ),

    'Bộ điều khiển trung tâm' => array(
        'Bộ điều khiển SmartLife Center',
        'SmartLife Home Controller',
        'SmartLife Central Hub',
        'SmartLife Gateway Pro',
    ),

    'Phụ kiện nhà thông minh' => array(
        'Bộ phụ kiện SmartLife Home',
        'Adapter SmartLife',
        'Cáp nguồn SmartLife',
        'Bộ giá đỡ SmartLife',
    ),
);


/*
|--------------------------------------------------------------------------
| HÀM HỖ TRỢ
|--------------------------------------------------------------------------
*/

/**
 * Tạo hoặc lấy term.
 */
function smartlife_get_or_create_term($name, $taxonomy, $parent = 0)
{

    $existing = term_exists($name, $taxonomy, $parent);

    if ($existing) {
        if (is_array($existing)) {
            return (int) $existing['term_id'];
        }

        return (int) $existing;
    }

    $result = wp_insert_term(
        $name,
        $taxonomy,
        array(
            'parent' => (int) $parent,
        )
    );

    if (is_wp_error($result)) {
        return 0;
    }

    return (int) $result['term_id'];
}


/**
 * Tạo global WooCommerce attribute.
 */
function smartlife_create_attribute($name, $slug)
{

    if (!function_exists('wc_create_attribute')) {
        return false;
    }

    $taxonomy = 'pa_' . sanitize_title($slug);

    /*
     * Kiểm tra attribute hiện có.
     */
    $attribute_id = wc_attribute_taxonomy_id_by_name($name);

    if ($attribute_id) {
        return (int) $attribute_id;
    }

    /*
     * Kiểm tra bằng slug.
     */
    global $wpdb;

    $existing_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT attribute_id
             FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
             WHERE attribute_name = %s
             LIMIT 1",
            sanitize_title($slug)
        )
    );

    if ($existing_id) {
        return (int) $existing_id;
    }

    $attribute_id = wc_create_attribute(
        array(
            'name'         => $name,
            'slug'         => sanitize_title($slug),
            'type'         => 'select',
            'order_by'     => 'name',
            'has_archives' => true,
        )
    );

    if (is_wp_error($attribute_id)) {
        return false;
    }

    /*
     * WooCommerce cần flush/rebuild taxonomy.
     */
    delete_transient('wc_attribute_taxonomies');
    if (method_exists('WC_Cache_Helper', 'invalidate_cache_group')) {
        WC_Cache_Helper::invalidate_cache_group('woocommerce-attributes');
    }

    return (int) $attribute_id;
}


/**
 * Lấy taxonomy của global attribute.
 */
function smartlife_attribute_taxonomy($slug)
{
    return 'pa_' . sanitize_title($slug);
}


/**
 * Tạo giá trị thuộc tính.
 */
function smartlife_create_attribute_terms($slug, $terms)
{

    $taxonomy = smartlife_attribute_taxonomy($slug);

    /*
     * Đăng ký taxonomy nếu chưa được WordPress load lại.
     */
    if (!taxonomy_exists($taxonomy)) {

        /*
         * Đọc lại attribute taxonomies của WooCommerce.
         */
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        foreach ($attribute_taxonomies as $attribute) {

            if ('pa_' . $attribute->attribute_name === $taxonomy) {

                register_taxonomy(
                    $taxonomy,
                    array('product'),
                    array(
                        'hierarchical' => false,
                        'show_ui'      => false,
                        'query_var'    => true,
                        'rewrite'      => array(
                            'slug' => $attribute->attribute_name,
                        ),
                    )
                );

                break;
            }
        }
    }

    foreach ($terms as $term_name) {

        if (!term_exists($term_name, $taxonomy)) {

            wp_insert_term(
                $term_name,
                $taxonomy
            );
        }
    }
}


/**
 * Lấy term ID của attribute.
 */
function smartlife_term_id($term_name, $taxonomy)
{

    $term = term_exists($term_name, $taxonomy);

    if (!$term) {
        return 0;
    }

    if (is_array($term)) {
        return (int) $term['term_id'];
    }

    return (int) $term;
}


/**
 * Gán global attribute vào sản phẩm.
 */
function smartlife_assign_attribute(
    $product,
    $attribute_name,
    $attribute_slug,
    $term_names,
    $position
) {

    $taxonomy = smartlife_attribute_taxonomy($attribute_slug);

    $options = array();

    foreach ($term_names as $term_name) {

        $term_id = smartlife_term_id(
            $term_name,
            $taxonomy
        );

        if ($term_id) {
            $options[] = $term_id;
        }
    }

    if (empty($options)) {
        return;
    }

    $attribute = new WC_Product_Attribute();

    $attribute->set_id(
        (int) wc_attribute_taxonomy_id_by_name($attribute_name)
    );

    $attribute->set_name($taxonomy);
    $attribute->set_options($options);
    $attribute->set_position($position);
    $attribute->set_visible(true);
    $attribute->set_variation(false);

    $existing = $product->get_attributes();

    $existing[$taxonomy] = $attribute;

    $product->set_attributes($existing);
}


/**
 * Chọn ngẫu nhiên một phần tử.
 */
function smartlife_random_item($array)
{

    if (empty($array)) {
        return '';
    }

    return $array[array_rand($array)];
}


/**
 * Tạo SKU.
 */
function smartlife_sku($index)
{

    return 'SL-' . str_pad(
        (string) $index,
        5,
        '0',
        STR_PAD_LEFT
    );
}


/**
 * Tạo giá sản phẩm.
 */
function smartlife_price($index)
{

    $base_prices = array(
        149000,
        199000,
        249000,
        299000,
        399000,
        499000,
        599000,
        799000,
        999000,
        1299000,
        1599000,
        1999000,
        2499000,
    );

    return $base_prices[$index % count($base_prices)];
}


/**
 * Lấy danh mục ngẫu nhiên.
 */
function smartlife_random_category($category_map)
{

    if (empty($category_map)) {
        return array(
            'parent_id' => 0,
            'term_id'   => 0,
            'name'      => '',
        );
    }

    $names = array_keys($category_map);

    $name = smartlife_random_item($names);

    return array(
        'parent_id' => $category_map[$name]['parent_id'],
        'term_id'   => $category_map[$name]['term_id'],
        'name'      => $name,
    );
}


/*
|--------------------------------------------------------------------------
| IMPORT
|--------------------------------------------------------------------------
*/

$messages = array();
$errors   = array();

if (
    isset($_POST['smartlife_import'])
    && check_admin_referer('smartlife_import_action', 'smartlife_nonce')
) {

    @set_time_limit(0);
    @ini_set('memory_limit', '512M');

    $product_count = isset($_POST['product_count'])
        ? absint($_POST['product_count'])
        : $DEFAULT_PRODUCT_COUNT;

    /*
     * Giới hạn bảo vệ.
     */
    if ($product_count < 1) {
        $product_count = 40;
    }

    if ($product_count > 5000) {
        $product_count = 5000;
    }


    /*
    |--------------------------------------------------------------------------
    | 1. TẠO DANH MỤC
    |--------------------------------------------------------------------------
    */

    $category_map = array();

    foreach ($category_tree as $parent_name => $children) {

        $parent_id = smartlife_get_or_create_term(
            $parent_name,
            'product_cat'
        );

        if (!$parent_id) {
            $errors[] = 'Không tạo được danh mục: ' . $parent_name;
            continue;
        }

        /*
         * Lưu danh mục cha.
         */
        $category_map[$parent_name] = array(
            'parent_id' => 0,
            'term_id'   => $parent_id,
        );

        /*
         * Tạo danh mục con.
         */
        foreach ($children as $child_name) {

            $child_id = smartlife_get_or_create_term(
                $child_name,
                'product_cat',
                $parent_id
            );

            if (!$child_id) {
                $errors[] = 'Không tạo được danh mục con: ' . $child_name;
                continue;
            }

            $category_map[$child_name] = array(
                'parent_id' => $parent_id,
                'term_id'   => $child_id,
            );
        }
    }

    $messages[] = 'Đã tạo/kiểm tra cây danh mục.';


    /*
    |--------------------------------------------------------------------------
    | 2. TẠO GLOBAL ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    foreach ($attributes as $attribute_name => $attribute_data) {

        $attribute_id = smartlife_create_attribute(
            $attribute_name,
            $attribute_data['slug']
        );

        if (!$attribute_id) {

            $errors[] =
                'Không tạo được thuộc tính: ' .
                $attribute_name;

            continue;
        }

        /*
         * Tạo taxonomy nếu WordPress chưa đăng ký.
         */
        $taxonomy = smartlife_attribute_taxonomy(
            $attribute_data['slug']
        );

        /*
         * WooCommerce có thể cần load lại taxonomy.
         */
        if (!taxonomy_exists($taxonomy)) {

            register_taxonomy(
                $taxonomy,
                array('product'),
                array(
                    'hierarchical' => false,
                    'show_ui'      => false,
                    'query_var'    => true,
                    'rewrite'      => array(
                        'slug' => $attribute_data['slug'],
                    ),
                )
            );
        }

        smartlife_create_attribute_terms(
            $attribute_data['slug'],
            $attribute_data['terms']
        );
    }

    $messages[] = 'Đã tạo/kiểm tra 8 global attributes và các giá trị.';


    /*
    |--------------------------------------------------------------------------
    | 3. TẠO SẢN PHẨM
    |--------------------------------------------------------------------------
    */

    $created_products = 0;

    /*
     * Danh sách danh mục con để phân phối sản phẩm.
     */
    $leaf_categories = array();

    foreach ($category_tree as $parent_name => $children) {

        foreach ($children as $child_name) {

            if (isset($category_map[$child_name])) {

                $leaf_categories[$child_name] =
                    $category_map[$child_name];
            }
        }
    }


    /*
     * Kiểm tra SKU hiện có.
     *
     * Mỗi lần chạy importer sẽ tạo SKU mới nếu index tiếp tục.
     */
    for ($i = 1; $i <= $product_count; $i++) {

        $sku = smartlife_sku($i);

        /*
         * Nếu SKU đã tồn tại thì bỏ qua.
         */
        $existing_id = wc_get_product_id_by_sku($sku);

        if ($existing_id) {
            continue;
        }


        /*
         * Chọn danh mục.
         */
        $category_names = array_keys($leaf_categories);

        $category_name =
            $category_names[($i - 1) % count($category_names)];

        $category_info =
            $leaf_categories[$category_name];


        /*
         * Chọn tên mẫu.
         */
        if (isset($product_names[$category_name])) {

            $templates =
                $product_names[$category_name];

            $base_name =
                $templates[($i - 1) % count($templates)];
        } else {

            $base_name =
                'Sản phẩm SmartLife ' . $i;
        }


        /*
         * Tên sản phẩm.
         *
         * Thêm mã để 1000 sản phẩm không trùng tên.
         */
        $product_name =
            $base_name . ' #' . str_pad(
                (string) $i,
                4,
                '0',
                STR_PAD_LEFT
            );


        /*
         * Tạo product.
         */
        $product = new WC_Product_Simple();

        $product->set_name($product_name);

        $product->set_status('publish');

        $product->set_catalog_visibility('visible');

        $product->set_sku($sku);

        $price = smartlife_price($i);

        $product->set_regular_price(
            (string) $price
        );

        /*
         * Một số sản phẩm có giá sale.
         */
        if ($i % 7 === 0) {

            $sale_price =
                max(
                    99000,
                    $price - 50000
                );

            $product->set_sale_price(
                (string) $sale_price
            );
        }


        /*
         * Tồn kho.
         */
        $stock = 10 + ($i % 90);

        $product->set_manage_stock(true);

        $product->set_stock_quantity($stock);

        $product->set_stock_status(
            'instock'
        );


        /*
         * Mô tả.
         */
        $description =
            '<p><strong>' .
            esc_html($product_name) .
            '</strong> là sản phẩm thuộc hệ sinh thái SmartLife – Thế giới nhà thông minh.</p>' .

            '<p>Sản phẩm được thiết kế để kết nối và điều khiển thuận tiện trong hệ thống nhà thông minh.</p>' .

            '<ul>' .
            '<li>Thương hiệu: SmartLife</li>' .
            '<li>Danh mục: ' . esc_html($category_name) . '</li>' .
            '<li>Hỗ trợ điều khiển thông minh</li>' .
            '<li>Bảo hành chính hãng</li>' .
            '</ul>';

        $short_description =
            'Sản phẩm SmartLife thuộc nhóm ' .
            $category_name .
            ', hỗ trợ hệ sinh thái nhà thông minh.';

        $product->set_description(
            $description
        );

        $product->set_short_description(
            $short_description
        );


        /*
         * Danh mục.
         */
        $product->set_category_ids(
            array(
                (int) $category_info['term_id'],
            )
        );


        /*
         * Thuộc tính.
         */

        $brand = 'SmartLife';

        $connections = array(
            'Wi-Fi',
            'Bluetooth',
            'Zigbee',
            'Wi-Fi + Bluetooth',
        );

        $colors = array(
            'Trắng',
            'Đen',
            'Xám',
            'Vàng',
        );

        $powers = array(
            '5W',
            '7W',
            '9W',
            '12W',
            '15W',
            '20W',
        );

        $voltages = array(
            '5V',
            '12V',
            '24V',
            '220V',
        );

        $controls = array(
            'Ứng dụng',
            'Remote',
            'Giọng nói',
            'Nút nhấn',
        );

        $compatibilities = array(
            'Google Home',
            'Amazon Alexa',
            'Apple HomeKit',
            'SmartLife App',
        );

        $warranties = array(
            '12 tháng',
            '24 tháng',
        );


        smartlife_assign_attribute(
            $product,
            'Thương hiệu',
            'thuong-hieu',
            array($brand),
            0
        );

        smartlife_assign_attribute(
            $product,
            'Kết nối',
            'ket-noi',
            array(
                $connections[($i - 1) % count($connections)]
            ),
            1
        );

        smartlife_assign_attribute(
            $product,
            'Màu sắc',
            'mau-sac',
            array(
                $colors[($i - 1) % count($colors)]
            ),
            2
        );

        smartlife_assign_attribute(
            $product,
            'Công suất',
            'cong-suat',
            array(
                $powers[($i - 1) % count($powers)]
            ),
            3
        );

        smartlife_assign_attribute(
            $product,
            'Điện áp',
            'dien-ap',
            array(
                $voltages[($i - 1) % count($voltages)]
            ),
            4
        );

        smartlife_assign_attribute(
            $product,
            'Điều khiển',
            'dieu-khien',
            array(
                $controls[($i - 1) % count($controls)]
            ),
            5
        );

        smartlife_assign_attribute(
            $product,
            'Tương thích',
            'tuong-thich',
            array(
                $compatibilities[($i - 1) % count($compatibilities)]
            ),
            6
        );

        smartlife_assign_attribute(
            $product,
            'Bảo hành',
            'bao-hanh',
            array(
                $warranties[($i - 1) % count($warranties)]
            ),
            7
        );


        /*
         * Lưu product.
         */
        try {

            $product_id =
                $product->save();

            if ($product_id) {

                $created_products++;

                /*
                 * Đảm bảo taxonomy relationships.
                 */
                clean_post_cache(
                    $product_id
                );
            }
        } catch (Exception $e) {

            $errors[] =
                'Lỗi sản phẩm ' .
                $sku .
                ': ' .
                $e->getMessage();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | 4. FLUSH CACHE / REWRITE
    |--------------------------------------------------------------------------
    */

    flush_rewrite_rules(false);

    delete_transient(
        'wc_attribute_taxonomies'
    );

    /*
     * WooCommerce lookup tables.
     *
     * WooCommerce sẽ cập nhật lookup table thông qua
     * cơ chế của sản phẩm; không tự INSERT trực tiếp.
     */
    if (function_exists('wc_update_product_lookup_tables')) {

        wc_update_product_lookup_tables();
    }


    /*
    |--------------------------------------------------------------------------
    | KẾT QUẢ
    |--------------------------------------------------------------------------
    */

    $messages[] =
        'Đã tạo thành công ' .
        $created_products .
        ' sản phẩm mới.';

    $messages[] =
        'Yêu cầu import: ' .
        $product_count .
        ' sản phẩm.';
}


/*
|--------------------------------------------------------------------------
| HTML
|--------------------------------------------------------------------------
*/

?>
<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SmartLife Importer</title>

    <style>
        body {
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Arial,
                sans-serif;

            background: #f1f1f1;

            margin: 0;

            padding: 40px;
        }

        .container {
            max-width: 900px;

            margin: 0 auto;

            background: #fff;

            padding: 30px;

            border-radius: 10px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, .08);
        }

        h1 {
            margin-top: 0;
        }

        .warning {
            background: #fff3cd;

            border: 1px solid #ffe69c;

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;
        }

        .success {
            background: #d1e7dd;

            border: 1px solid #a3cfbb;

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;

            border: 1px solid #f1aeb5;

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;
        }

        label {
            display: block;

            font-weight: 600;

            margin-bottom: 8px;
        }

        input[type="number"] {
            width: 200px;

            padding: 10px;

            font-size: 16px;

            border: 1px solid #ccc;

            border-radius: 5px;
        }

        button {
            margin-top: 20px;

            padding: 12px 20px;

            font-size: 16px;

            font-weight: 600;

            border: 0;

            border-radius: 5px;

            background: #2271b1;

            color: #fff;

            cursor: pointer;
        }

        button:hover {
            background: #135e96;
        }

        .info {
            background: #f6f7f7;

            padding: 15px;

            border-left: 4px solid #2271b1;

            margin: 20px 0;
        }

        code {
            background: #f0f0f1;

            padding: 2px 5px;

            border-radius: 3px;
        }
    </style>

</head>

<body>

    <div class="container">

        <h1>
            SmartLife – WooCommerce Importer
        </h1>

        <div class="warning">

            <strong>Lưu ý:</strong>

            Chỉ chạy công cụ này trên website
            localhost/development.

            Không sử dụng trực tiếp trên website
            production.

        </div>


        <?php if (!empty($messages)) : ?>

            <?php foreach ($messages as $message) : ?>

                <div class="success">

                    <?php echo esc_html($message); ?>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>


        <?php if (!empty($errors)) : ?>

            <div class="error">

                <strong>Một số lỗi:</strong>

                <ul>

                    <?php foreach ($errors as $error) : ?>

                        <li>
                            <?php echo esc_html($error); ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <div class="info">

            <strong>Importer sẽ tạo:</strong>

            <ul>

                <li>6 danh mục cha</li>

                <li>23 danh mục sản phẩm</li>

                <li>8 global attributes</li>

                <li>Các giá trị thuộc tính</li>

                <li>Sản phẩm SmartLife</li>

                <li>SKU từ SL-00001 trở lên</li>

            </ul>

        </div>


        <form method="post">

            <?php

            wp_nonce_field(
                'smartlife_import_action',
                'smartlife_nonce'
            );

            ?>

            <label for="product_count">

                Số lượng sản phẩm cần tạo

            </label>

            <input type="number" id="product_count" name="product_count"
                value="<?php echo esc_attr($DEFAULT_PRODUCT_COUNT); ?>" min="1" max="5000" required>

            <br>

            <small>

                Khuyến nghị:
                <strong>40</strong>
                để kiểm tra trước.

                Sau đó có thể nhập
                <strong>1000</strong>.

            </small>

            <br>

            <button type="submit" name="smartlife_import" value="1">

                Bắt đầu Import SmartLife

            </button>

        </form>


        <div class="info">

            <strong>Sau khi import 40 sản phẩm:</strong>

            <ol>

                <li>
                    Vào WooCommerce → Sản phẩm
                </li>

                <li>
                    Kiểm tra danh mục
                </li>

                <li>
                    Kiểm tra thuộc tính
                </li>

                <li>
                    Kiểm tra tìm kiếm
                </li>

                <li>
                    Kiểm tra bộ lọc
                </li>

                <li>
                    Kiểm tra phân trang
                </li>

            </ol>

        </div>


        <p>

            Sau khi hoàn thành, hãy xóa file
            <code>smartlife-import.php</code>
            khỏi thư mục WordPress.

        </p>

    </div>

</body>

</html>