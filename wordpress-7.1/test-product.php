<?php

// Nạp WordPress trước
require_once __DIR__ . '/wp-load.php';

if (PHP_SAPI === 'cli') {
    fwrite(STDERR, "This legacy endpoint is disabled. Use smartlife-import.php --dry-run|--apply.\n");
    exit(1);
}

if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) {
    wp_die('Không có quyền truy cập.', 'SmartLife', array('response' => 403));
}

wp_die('Script test legacy đã bị vô hiệu hóa.', 'SmartLife', array('response' => 410));
__halt_compiler();

// =====================================================
// 1. TÌM DANH MỤC
// =====================================================

$category = get_term_by(
    'name',
    'Bóng đèn thông minh',
    'product_cat'
);

if (! $category) {
    die('Không tìm thấy danh mục "Bóng đèn thông minh".');
}


// =====================================================
// 2. TÌM THƯƠNG HIỆU
// =====================================================

$brand = get_term_by(
    'name',
    'SmartLife',
    'product_brand'
);

if (! $brand) {
    die('Không tìm thấy thương hiệu SmartLife.');
}


// =====================================================
// 3. KIỂM TRA CÁC TAXONOMY THUỘC TÍNH
// =====================================================

$attributes = array(
    'thuong-hieu' => array(
        'SmartLife'
    ),

    'ket-noi' => array(
        'Wi-Fi'
    ),

    'mau-sac' => array(
        'Trắng'
    ),

    'cong-suat' => array(
        '9W'
    ),

    'dien-ap' => array(
        '220V'
    ),

    'dieu-khien' => array(
        'Ứng dụng'
    ),

    'tuong-thich' => array(
        'Google Home',
        'Amazon Alexa',
        'SmartLife App'
    ),

    'bao-hanh' => array(
        '24 tháng'
    )
);


// =====================================================
// 4. TẠO SẢN PHẨM
// =====================================================

$product = new WC_Product_Simple();

$product->set_name(
    'Bóng đèn LED thông minh SmartLife 9W'
);

$product->set_status('publish');

$product->set_catalog_visibility('visible');

$product->set_description(
    '<p>Bóng đèn LED thông minh SmartLife 9W điều khiển qua ứng dụng, hỗ trợ Google Home và Amazon Alexa.</p>
     <p>Thiết kế tiết kiệm điện, phù hợp cho phòng khách, phòng ngủ và các không gian trong nhà thông minh.</p>'
);

$product->set_short_description(
    'Bóng đèn LED thông minh 9W, kết nối Wi-Fi, điều khiển bằng ứng dụng.'
);

$product->set_regular_price('199000');

$product->set_sku('SL-BULB-0001');

$product->set_manage_stock(true);

$product->set_stock_quantity(100);

$product->set_stock_status('instock');

$product->set_weight('0.15');

$product->set_category_ids(
    array($category->term_id)
);

$product_id = $product->save();


// =====================================================
// 5. GÁN THƯƠNG HIỆU
// =====================================================

wp_set_object_terms(
    $product_id,
    array($brand->term_id),
    'product_brand'
);


// =====================================================
// 6. TẠO / LẤY GIÁ TRỊ THUỘC TÍNH
// =====================================================

foreach ($attributes as $attribute_name => $values) {

    $taxonomy = 'pa_' . $attribute_name;

    // Nếu taxonomy chưa tồn tại
    if (! taxonomy_exists($taxonomy)) {
        echo '<p style="color:red">';
        echo 'Taxonomy chưa tồn tại: ' . esc_html($taxonomy);
        echo '</p>';
        continue;
    }

    $term_ids = array();

    foreach ($values as $value) {

        // Tìm term
        $term = get_term_by(
            'name',
            $value,
            $taxonomy
        );

        // Nếu chưa có thì tạo
        if (! $term) {

            $result = wp_insert_term(
                $value,
                $taxonomy
            );

            if (is_wp_error($result)) {
                echo '<p style="color:red">';
                echo 'Lỗi tạo term ' . esc_html($value);
                echo ': ' . esc_html($result->get_error_message());
                echo '</p>';
                continue;
            }

            $term_id = $result['term_id'];
        } else {

            $term_id = $term->term_id;
        }

        $term_ids[] = $term_id;
    }

    // Gán term cho sản phẩm
    if (! empty($term_ids)) {

        wp_set_object_terms(
            $product_id,
            $term_ids,
            $taxonomy
        );
    }
}


// =====================================================
// 7. TẠO CÁC THUỘC TÍNH CHO SẢN PHẨM
// =====================================================

$product = wc_get_product($product_id);

$product_attributes = array();

foreach ($attributes as $attribute_name => $values) {

    $taxonomy = 'pa_' . $attribute_name;

    if (! taxonomy_exists($taxonomy)) {
        continue;
    }

    $term_slugs = array();

    foreach ($values as $value) {

        $term = get_term_by(
            'name',
            $value,
            $taxonomy
        );

        if ($term) {
            $term_slugs[] = $term->slug;
        }
    }

    if (empty($term_slugs)) {
        continue;
    }

    $attribute = new WC_Product_Attribute();

    $attribute_id = wc_attribute_taxonomy_id_by_name(
        $taxonomy
    );

    $attribute->set_id($attribute_id);

    $attribute->set_name($taxonomy);

    $attribute->set_options(
        $term_slugs
    );

    $attribute->set_position(
        count($product_attributes)
    );

    $attribute->set_visible(true);

    $attribute->set_variation(false);

    $product_attributes[] = $attribute;
}

$product->set_attributes($product_attributes);

$product->save();


// =====================================================
// 8. XÓA CACHE
// =====================================================

clean_post_cache($product_id);

wc_delete_product_transients($product_id);


// =====================================================
// 9. HIỂN THỊ KẾT QUẢ
// =====================================================

echo '<h1 style="color:green;">Đã tạo sản phẩm thành công!</h1>';

echo '<p><strong>ID:</strong> ' .
    esc_html($product_id) .
    '</p>';

echo '<p><strong>Tên:</strong> Bóng đèn LED thông minh SmartLife 9W</p>';

echo '<p><strong>SKU:</strong> SL-BULB-0001</p>';

echo '<p><strong>Giá:</strong> 199.000 ₫</p>';

echo '<p>';

echo '<a href="' .
    esc_url(
        admin_url(
            'post.php?post=' .
                $product_id .
                '&action=edit'
        )
    ) .
    '" target="_blank">';

echo 'Mở sản phẩm trong quản trị';

echo '</a>';

echo '</p>';
