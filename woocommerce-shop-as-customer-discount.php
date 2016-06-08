<?php

/**
 * Plugin Name: WooCommerce Shop as Customer Discount
 * Description: Shop as Customer Discount displays a discount field at checkout when Shopping as a Customer
 * Author: Creative Little Dots
 * Author URI: http://creativelittledots.co.uk
 * Version: 1.0
 * Text Domain: shop-as-customer
 * Domain Path: /languages/
 *
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Instantiate plugin.
 */
$wc_shop_as_customer_discount = WC_Shop_As_Customer_Discount::get_instance();

/**
 * Main Class.
 */
class WC_Shop_As_Customer_Discount {
	
	private static $instance;
	
	public $coupon_code = 'Customer Discount';
	
	/**
	* Get Instance creates a singleton class that's cached to stop duplicate instances
	*/
	public static function get_instance() {
		
		if ( ! self::$instance ) {
			
			self::$instance = new self();
			
		}
		
		return self::$instance;
		
	}
	
	/**
	* Construct empty on purpose
	*/
	private function __construct() {
		
		add_action( 'init', array( $this, 'init' ) );
		
	}

	/**
	* Init behaves like, and replaces, construct
	*/
	public function init() {
		
		if( class_exists('WC_Shop_As_Customer') && WC_Shop_As_Customer::get_original_user() ) {
			
			add_action( 'woocommerce_after_order_notes', array($this, 'display_discount_field') );
			
			add_filter( 'woocommerce_get_shop_coupon_data', array($this, 'customer_discount') );
			
			add_action( 'woocommerce_checkout_update_order_review', array($this, 'maybe_add_cart_discount') );
			
			add_action( 'shop_as_customer', array($this, 'remove_session_var') );
			add_action( 'switch_back_user', array($this, 'remove_session_var') );
			
		}
		
	}
	
	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
		
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
		
	}
	
	public function display_discount_field() {
		
		$discount = apply_filters('wc_shop_as_customer_discount', WC()->checkout()->get_value( '_discount' ) ? WC()->checkout()->get_value( '_discount' ) : WC()->session->get( '_discount' ) );
		
		wc_get_template( 'checkout/discount.php', compact('discount'), '', $this->plugin_path() . '/templates/' );
		
	}
	
	public function customer_discount($get_shop_coupon_data) {
		
		if( ! is_admin() || is_ajax() ) {
			
			$get_shop_coupon_data = array(
				'discount_type'              => 'percent',
				'coupon_amount'              => WC()->session->get('_discount') ? WC()->session->get('_discount') : 0
			);
			
		}
		
		return $get_shop_coupon_data;
		
	}
	
	public function maybe_add_cart_discount($post_string) {
		
		parse_str($post_string, $post_data);
			
		if( ! empty( $post_data['_discount'] ) ) {
			
			WC()->session->set('_discount', $post_data['_discount']);
			
			WC()->cart->remove_coupon( $this->coupon_code );
				
			WC()->cart->add_discount( $this->coupon_code );
			
		}
		
	}
	
	public function remove_session_var() {
		
		WC()->session->set( '_discount', false );
		
	}

}	