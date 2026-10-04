<?php

defined('ABSPATH') || exit;

function sl11_enqueue_child_stylesheet()
{
    wp_enqueue_style(
        'twentytwentyfive-smartlife',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'sl11_enqueue_child_stylesheet', 20);

function sl11_theme_is_local()
{
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    return in_array($host, array('localhost', '127.0.0.1', '::1'), true);
}

function sl11_theme_filter_value($key)
{
    return isset($_GET[$key]) ? sanitize_text_field(wp_unslash($_GET[$key])) : '';
}

function sl11_theme_render_filters()
{
    if (!function_exists('wc_get_page_permalink')) {
        return '';
    }

    $queried = get_queried_object();
    $action = is_product_category() && $queried instanceof WP_Term
        ? get_term_link($queried, 'product_cat')
        : wc_get_page_permalink('shop');
    if (is_wp_error($action)) {
        $action = wc_get_page_permalink('shop');
    }

    $filters = array(
        'filter_brand' => array('label' => 'Thương hiệu', 'taxonomy' => 'product_brand'),
        'filter_ket-noi' => array('label' => 'Kết nối', 'taxonomy' => 'pa_ket-noi'),
        'filter_cong-suat' => array('label' => 'Công suất', 'taxonomy' => 'pa_cong-suat'),
        'filter_dieu-khien' => array('label' => 'Điều khiển', 'taxonomy' => 'pa_dieu-khien'),
    );

    ob_start();
?>
    <form class="smartlife-filters" method="get" action="<?php echo esc_url($action); ?>" aria-label="Lọc sản phẩm SmartLife">
        <label>Giá từ
            <input type="number" name="min_price" min="0" step="1000" value="<?php echo esc_attr(sl11_theme_filter_value('min_price')); ?>">
        </label>
        <label>Giá đến
            <input type="number" name="max_price" min="0" step="1000" value="<?php echo esc_attr(sl11_theme_filter_value('max_price')); ?>">
        </label>
        <?php foreach ($filters as $query_key => $filter) : ?>
            <label><?php echo esc_html($filter['label']); ?>
                <select name="<?php echo esc_attr($query_key); ?>">
                    <option value="">Tất cả</option>
                    <?php foreach (get_terms(array('taxonomy' => $filter['taxonomy'], 'hide_empty' => true)) as $term) : ?>
                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(sl11_theme_filter_value($query_key), $term->slug); ?>><?php echo esc_html($term->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endforeach; ?>
        <button type="submit">Lọc sản phẩm</button>
        <a class="smartlife-filter-clear" href="<?php echo esc_url($action); ?>">Xóa lọc</a>
    </form>
<?php
    return (string) ob_get_clean();
}
add_shortcode('smartlife_filters', 'sl11_theme_render_filters');

function sl11_theme_apply_brand_filter($tax_query, $query)
{
    $brand_slug = sl11_theme_filter_value('filter_brand');
    if (!$brand_slug || !is_array($tax_query)) {
        return $tax_query;
    }

    $brand = get_term_by('slug', sanitize_title($brand_slug), 'product_brand');
    if ($brand && !is_wp_error($brand)) {
        $tax_query[] = array(
            'taxonomy' => 'product_brand',
            'field' => 'term_id',
            'terms' => array((int) $brand->term_id),
        );
    }
    return $tax_query;
}
add_filter('woocommerce_product_query_tax_query', 'sl11_theme_apply_brand_filter', 20, 2);

function sl11_theme_product_policies()
{
    if (!is_product()) {
        return;
    }
    echo '<section class="smartlife-product-policies" aria-label="Giao hàng, đổi trả và hỗ trợ">';
    echo '<h2>Giao hàng, đổi trả & hỗ trợ</h2>';
    echo '<p>Giao hàng nội thành: 1–2 ngày làm việc. Các tỉnh/thành khác: 2–5 ngày làm việc.</p>';
    echo '<p>Đổi trả trong 7 ngày đối với lỗi kỹ thuật theo điều kiện của cửa hàng.</p>';
    echo '<p>Hỗ trợ: SmartLife – Thế giới nhà thông minh.</p>';
    echo '<small>Thông tin demo phục vụ bài thực hành, không phải cam kết kinh doanh thực tế.</small>';
    echo '</section>';
}
add_action('woocommerce_after_add_to_cart_form', 'sl11_theme_product_policies', 20);

function sl11_theme_payment_gateways($gateways)
{
    if (!is_array($gateways)) {
        return $gateways;
    }
    $labels = array(
        'bacs' => 'Chuyển khoản ngân hàng (demo, không phát sinh giao dịch)',
        'cheque' => 'Thanh toán demo/offline',
        'cod' => 'Thanh toán khi nhận hàng (demo)',
    );
    foreach ($labels as $id => $label) {
        if (isset($gateways[$id])) {
            $gateways[$id]->title = $label;
        }
    }
    return $gateways;
}
add_filter('woocommerce_available_payment_gateways', 'sl11_theme_payment_gateways', 20);

function sl11_theme_gateway_title($title, $gateway_id)
{
    $labels = array(
        'bacs' => 'Chuyển khoản thủ công (demo)',
        'cheque' => 'Thanh toán demo/offline',
        'cod' => 'Thanh toán khi nhận hàng (demo)',
    );
    return $labels[$gateway_id] ?? $title;
}
add_filter('woocommerce_gateway_title', 'sl11_theme_gateway_title', 20, 2);

function sl11_theme_gateway_description($description, $gateway_id)
{
    $descriptions = array(
        'bacs' => 'Phương thức demo/offline cho bài thực hành. Không chuyển tiền thật và không phát sinh giao dịch.',
        'cheque' => 'Thanh toán demo/offline cho bài thực hành. Không kết nối MoMo, thẻ ATM hoặc cổng thanh toán thật.',
        'cod' => 'Tùy chọn minh họa cho bài thực hành; không tạo giao dịch thanh toán thật.',
    );
    return $descriptions[$gateway_id] ?? $description;
}
add_filter('woocommerce_gateway_description', 'sl11_theme_gateway_description', 20, 2);

function sl11_theme_order_support()
{
    echo '<p class="smartlife-order-support">Hỗ trợ đơn hàng: SmartLife – Thế giới nhà thông minh. Thông tin demo phục vụ bài thực hành.</p>';
}
add_action('woocommerce_thankyou', 'sl11_theme_order_support', 20);
