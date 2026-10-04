<?php

/**
 * Plugin Name: SmartLife Demo Weight Shipping
 * Description: Demo zone-based shipping rates calculated from total shippable cart weight.
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 */

defined('ABSPATH') || exit;

add_action('woocommerce_shipping_init', 'sl11_demo_weight_shipping_init');

function sl11_demo_weight_shipping_init()
{
    if (class_exists('SL11_Demo_Weight_Shipping_Method', false)) {
        return;
    }

    class SL11_Demo_Weight_Shipping_Method extends WC_Shipping_Method
    {
        public function __construct($instance_id = 0)
        {
            $this->id = 'smartlife_demo_weight';
            $this->instance_id = absint($instance_id);
            $this->method_title = 'SmartLife demo weight shipping';
            $this->method_description = 'Demo rates calculated from the total shippable package weight.';
            $this->supports = array('shipping-zones', 'instance-settings', 'instance-settings-modal');
            $this->init();
        }

        public function init()
        {
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title', $this->method_title);
            $this->enabled = $this->get_option('enabled', 'yes');
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields()
        {
            $rate_field = array(
                'type' => 'number',
                'custom_attributes' => array('min' => '0', 'step' => '1'),
                'sanitize_callback' => array($this, 'sanitize_rate'),
            );
            $this->instance_form_fields = array(
                'title' => array('title' => 'Method title', 'type' => 'text', 'default' => 'SmartLife demo weight shipping'),
                'rate_up_to_2kg' => array_merge($rate_field, array('title' => 'Rate up to and including 2 kg', 'default' => '')),
                'rate_over_2kg_up_to_5kg' => array_merge($rate_field, array('title' => 'Rate over 2 kg up to and including 5 kg', 'default' => '')),
                'rate_over_5kg' => array_merge($rate_field, array('title' => 'Rate over 5 kg', 'default' => '')),
            );
        }

        public function sanitize_rate($value)
        {
            $rate = wc_format_decimal($value);
            return $rate === '' ? '' : max(0, (float) $rate);
        }

        public function calculate_shipping($package = array())
        {
            if (empty($package['contents']) || !is_array($package['contents'])) {
                return;
            }
            $total_weight_kg = 0.0;
            $store_weight_unit = get_option('woocommerce_weight_unit', 'kg');
            foreach ($package['contents'] as $item) {
                $product = $item['data'] ?? null;
                $quantity = $item['quantity'] ?? 0;
                if (!$product instanceof WC_Product || !is_numeric($quantity) || (float) $quantity <= 0) {
                    return;
                }
                $weight = $product->get_weight();
                if ($weight === '' || !is_numeric($weight) || (float) $weight < 0) {
                    return;
                }
                $total_weight_kg += (float) wc_get_weight($weight, 'kg', $store_weight_unit) * (float) $quantity;
            }
            if ($total_weight_kg <= 2) {
                $rate_key = 'rate_up_to_2kg';
            } elseif ($total_weight_kg <= 5) {
                $rate_key = 'rate_over_2kg_up_to_5kg';
            } else {
                $rate_key = 'rate_over_5kg';
            }
            $rate = $this->get_option($rate_key, '');
            if ($rate === '' || !is_numeric($rate) || (float) $rate < 0) {
                return;
            }
            $this->add_rate(array(
                'id' => $this->get_rate_id(),
                'label' => $this->title,
                'cost' => (float) $rate,
                'taxes' => false,
                'package' => $package,
            ));
        }
    }
}

function sl11_demo_weight_shipping_method($methods)
{
    $methods['smartlife_demo_weight'] = 'SL11_Demo_Weight_Shipping_Method';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'sl11_demo_weight_shipping_method');
