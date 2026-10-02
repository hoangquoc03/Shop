<?php

require_once __DIR__ . '/wp-load.php';

if (PHP_SAPI === 'cli') {
    fwrite(STDERR, "This legacy endpoint is disabled. Use smartlife-import.php --dry-run|--apply.\n");
    exit(1);
}

if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) {
    wp_die('Không có quyền truy cập.', 'SmartLife', array('response' => 403));
}

wp_die('Script legacy đã bị vô hiệu hóa. Hãy dùng importer SmartLife có nonce.', 'SmartLife', array('response' => 410));
__halt_compiler();

/*
|--------------------------------------------------------------------------
| CẤU HÌNH
|--------------------------------------------------------------------------
*/

$start = 2;
$end   = 40;


/*
|--------------------------------------------------------------------------
| DANH MỤC SẢN PHẨM
|--------------------------------------------------------------------------
*/

$categories = array(
    'Bóng đèn thông minh',
    'Đèn LED thông minh',
    'Đèn ngủ thông minh',

    'Camera thông minh',
    'Chuông cửa thông minh',
    'Khóa cửa thông minh',
    'Cảm biến an ninh',

    'Công tắc thông minh',
    'Ổ cắm thông minh',
    'Hub điều khiển',
    'Remote thông minh',

    'Robot hút bụi',
    'Máy lọc không khí',
    'Máy hút ẩm',
    'Rèm cửa thông minh',

    'Cảm biến chuyển động',
    'Cảm biến cửa',
    'Cảm biến nhiệt độ và độ ẩm',
    'Cảm biến khói',

    'Bộ phát Wi-Fi',
    'Bộ điều khiển trung tâm',
    'Phụ kiện nhà thông minh'
);


/*
|--------------------------------------------------------------------------
| DỮ LIỆU SẢN PHẨM
|--------------------------------------------------------------------------
*/

$product_names = array(

    'Bóng đèn LED thông minh SmartLife 7W',
    'Bóng đèn LED thông minh SmartLife 12W',
    'Bóng đèn LED thông minh SmartLife 15W',

    'Đèn LED thông minh SmartLife RGB',
    'Đèn LED thông minh SmartLife 20W',
    'Đèn ngủ thông minh SmartLife',

    'Camera Wi-Fi thông minh SmartLife Full HD',
    'Camera trong nhà SmartLife 2MP',
    'Camera ngoài trời SmartLife',
    'Camera an ninh SmartLife 3MP',

    'Chuông cửa thông minh SmartLife',
    'Chuông cửa camera SmartLife',

    'Khóa cửa thông minh SmartLife',
    'Khóa cửa vân tay SmartLife',

    'Cảm biến an ninh SmartLife',
    'Công tắc thông minh SmartLife 1 nút',
    'Công tắc thông minh SmartLife 2 nút',
    'Công tắc thông minh SmartLife 3 nút',

    'Ổ cắm thông minh SmartLife Wi-Fi',
    'Ổ cắm thông minh SmartLife 16A',

    'Hub điều khiển SmartLife Zigbee',
    'Hub nhà thông minh SmartLife',

    'Remote thông minh SmartLife',

    'Robot hút bụi SmartLife',
    'Robot hút bụi lau nhà SmartLife',

    'Máy lọc không khí SmartLife',
    'Máy lọc không khí SmartLife Pro',

    'Máy hút ẩm thông minh SmartLife',

    'Rèm cửa thông minh SmartLife',

    'Cảm biến chuyển động SmartLife',
    'Cảm biến cửa SmartLife',
    'Cảm biến nhiệt độ SmartLife',
    'Cảm biến nhiệt độ và độ ẩm SmartLife',
    'Cảm biến khói SmartLife',

    'Bộ phát Wi-Fi SmartLife',
    'Bộ phát Wi-Fi Mesh SmartLife',

    'Bộ điều khiển trung tâm SmartLife',

    'Bộ phụ kiện nhà thông minh SmartLife'
);


/*
|--------------------------------------------------------------------------
| GIÁ SẢN PHẨM
|--------------------------------------------------------------------------
*/

$prices = array(
    149000,
    179000,
    199000,
    229000,
    249000,
    299000,
    499000,
    599000,
    699000,
    799000,
    899000,
    999000,
    1299000,
    1499000,
    1699000,
    1899000,
    2199000,
    2499000,
    299000,
    349000,
    399000,
    499000,
    599000,
    699000,
    799000,
    999000,
    1299000,
    1499000,
    1799000,
    1990000,
    2290000,
    2490000,
    2990000,
    3490000,
    3990000,
    4990000,
    5990000,
    6990000,
    7990000
);


/*
|--------------------------------------------------------------------------
| GIÁ TRỊ THUỘC TÍNH
|--------------------------------------------------------------------------
*/

$attribute_values = array(

    'thuong-hieu' => array(
        'SmartLife'
    ),

    'ket-noi' => array(
        'Wi-Fi',
        'Bluetooth',
        'Zigbee',
        'Wi-Fi + Bluetooth'
    ),

    'mau-sac' => array(
        'Trắng',
        'Đen',
        'Xám',
        'Vàng'
    ),

    'cong-suat' => array(
        '5W',
        '7W',
        '9W',
        '12W',
        '15W',
        '20W',
        '50W',
        '100W',
        '500W',
        '1000W'
    ),

    'dien-ap' => array(
        '5V',
        '12V',
        '24V',
        '220V'
    ),

    'dieu-khien' => array(
        'Ứng dụng',
        'Remote',
        'Giọng nói',
        'Nút nhấn'
    ),

    'tuong-thich' => array(
        'Google Home',
        'Amazon Alexa',
        'Apple HomeKit',
        'SmartLife App'
    ),

    'bao-hanh' => array(
        '12 tháng',
        '24 tháng'
    )
);


/*
|--------------------------------------------------------------------------
| TÌM THƯƠNG HIỆU
|--------------------------------------------------------------------------
*/

$brand = get_term_by(
    'name',
    'SmartLife',
    'product_brand'
);

if (! $brand) {
    die('Không tìm thấy thương hiệu SmartLife.');
}


/*
|--------------------------------------------------------------------------
| HÀM TẠO / LẤY TERM
|--------------------------------------------------------------------------
*/

function smartlife_get_term_id($value, $taxonomy)
{

    if (! taxonomy_exists($taxonomy)) {
        return false;
    }

    $term = get_term_by(
        'name',
        $value,
        $taxonomy
    );

    if ($term) {
        return $term->term_id;
    }

    $result = wp_insert_term(
        $value,
        $taxonomy
    );

    if (is_wp_error($result)) {
        return false;
    }

    return $result['term_id'];
}


/*
|--------------------------------------------------------------------------
| TẠO 39 SẢN PHẨM
|--------------------------------------------------------------------------
*/

$created = 0;
$skipped = 0;

for ($i = $start; $i <= $end; $i++) {

    $index = $i - 2;

    /*
     * SKU
     */
    $sku = 'SL-PROD-' . str_pad(
        $i,
        4,
        '0',
        STR_PAD_LEFT
    );


    /*
     * KIỂM TRA SKU
     */

    $existing_id = wc_get_product_id_by_sku($sku);

    if ($existing_id) {

        $skipped++;

        echo '<p>';
        echo 'Đã tồn tại: ';
        echo esc_html($sku);
        echo '</p>';

        continue;
    }


    /*
     * TÊN
     */

    $name = $product_names[$index];


    /*
     * DANH MỤC
     */

    $category_name = $categories[$index % count($categories)];

    $category = get_term_by(
        'name',
        $category_name,
        'product_cat'
    );

    if (!$category) {

        echo '<p style="color:red">';
        echo 'Không tìm thấy danh mục: ';
        echo esc_html($category_name);
        echo '</p>';

        continue;
    }


    /*
     * TẠO SẢN PHẨM
     */

    $product = new WC_Product_Simple();

    $product->set_name($name);

    $product->set_status('publish');

    $product->set_catalog_visibility('visible');

    $product->set_sku($sku);

    $price = $prices[$index % count($prices)];

    $product->set_regular_price(
        (string) $price
    );

    $product->set_manage_stock(true);

    $product->set_stock_quantity(
        20 + ($i * 3)
    );

    $product->set_stock_status('instock');


    /*
     * MÔ TẢ
     */

    $product->set_short_description(
        $name .
            ' - thiết bị nhà thông minh thuộc hệ sinh thái SmartLife.'
    );

    $product->set_description(
        '<p>' .
            $name .
            ' là sản phẩm thuộc hệ sinh thái SmartLife.</p>' .

            '<p>Sản phẩm hỗ trợ kết nối và điều khiển thông minh, ' .
            'phù hợp cho gia đình và hệ thống nhà thông minh.</p>'
    );


    /*
     * DANH MỤC
     */

    $product->set_category_ids(
        array(
            $category->term_id
        )
    );


    /*
     * LƯU SẢN PHẨM
     */

    $product_id = $product->save();


    /*
     * GÁN THƯƠNG HIỆU
     */

    wp_set_object_terms(
        $product_id,
        array($brand->term_id),
        'product_brand'
    );


    /*
     * THUỘC TÍNH
     */

    $product_attributes = array();

    foreach (
        $attribute_values
        as $attribute_name => $values
    ) {

        $taxonomy = 'pa_' . $attribute_name;

        if (
            ! taxonomy_exists($taxonomy)
        ) {
            continue;
        }


        /*
         * Chọn giá trị khác nhau theo sản phẩm
         */

        $selected_values = array();

        if ($attribute_name === 'tuong-thich') {

            $selected_values[] =
                $values[$index % count($values)];
        } elseif ($attribute_name === 'thuong-hieu') {

            $selected_values[] = 'SmartLife';
        } else {

            $selected_values[] =
                $values[$index % count($values)];
        }


        /*
         * Tạo term
         */

        $term_slugs = array();

        foreach ($selected_values as $value) {

            $term_id = smartlife_get_term_id(
                $value,
                $taxonomy
            );

            if (!$term_id) {
                continue;
            }

            $term = get_term(
                $term_id,
                $taxonomy
            );

            if ($term && !is_wp_error($term)) {

                $term_slugs[] =
                    $term->slug;
            }
        }


        /*
         * Gán term
         */

        if (empty($term_slugs)) {
            continue;
        }


        /*
         * Tạo attribute cho sản phẩm
         */

        $attribute = new WC_Product_Attribute();

        $attribute_id =
            wc_attribute_taxonomy_id_by_name(
                $taxonomy
            );

        $attribute->set_id(
            $attribute_id
        );

        $attribute->set_name(
            $taxonomy
        );

        $attribute->set_options(
            $term_slugs
        );

        $attribute->set_position(
            count($product_attributes)
        );

        $attribute->set_visible(true);

        $attribute->set_variation(false);

        $product_attributes[] =
            $attribute;
    }


    /*
     * LƯU THUỘC TÍNH
     */

    $product->set_attributes(
        $product_attributes
    );

    $product->save();


    /*
     * XÓA CACHE
     */

    clean_post_cache($product_id);

    wc_delete_product_transients(
        $product_id
    );


    /*
     * KẾT QUẢ
     */

    $created++;

    echo '<p style="color:green">';
    echo 'Đã tạo: ';
    echo '<strong>' .
        esc_html($sku) .
        '</strong>';
    echo ' - ';
    echo esc_html($name);
    echo '</p>';
}


/*
|--------------------------------------------------------------------------
| TỔNG KẾT
|--------------------------------------------------------------------------
*/

echo '<hr>';

echo '<h2>Hoàn tất</h2>';

echo '<p>';
echo 'Đã tạo: <strong>' .
    $created .
    '</strong> sản phẩm';
echo '</p>';

echo '<p>';
echo 'Đã bỏ qua: <strong>' .
    $skipped .
    '</strong> sản phẩm đã tồn tại';
echo '</p>';

echo '<p>';
echo '<a href="' .
    esc_url(
        admin_url(
            'edit.php?post_type=product'
        )
    ) .
    '">';

echo 'Mở danh sách sản phẩm';

echo '</a>';
echo '</p>';
