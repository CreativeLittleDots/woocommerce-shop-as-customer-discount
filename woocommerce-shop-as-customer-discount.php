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
		
		add_action( 'wp', array($this, 'maybe_create_coupon' ) );
		
		if( class_exists('WC_Shop_As_Customer') && WC_Shop_As_Customer::get_original_user() ) {
			
			add_action( 'woocommerce_after_order_notes', array($this, 'display_discount_field') );
			
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
	
	public function maybe_create_coupon() {
		
		$coupon = new WC_Coupon( $this->coupon_code );
		
		if( ! $coupon_id = $coupon->id ) {
			
			$coupon_id = wp_insert_post(array(
				'post_type' => 'shop_coupon',
				'post_status' => 'publish',
				'post_title' => $this->coupon_code
			));
			
			update_post_meta( $coupon_id, 'discount_type', 'percent_product' );
			
		}
		
	}
	
	public function maybe_add_cart_discount($post_string) {
		
		parse_str($post_string, $post_data);
			
		if( ! empty( $post_data['_discount'] ) ) {
			
			WC()->session->set('_discount', $post_data['_discount']);
			
			WC()->cart->remove_coupon( $this->coupon_code );
			
			if( ! is_admin() || is_ajax() ) {
			
				if( $coupon_id = wc_get_coupon_id_by_code( $this->coupon_code ) ) {
					
					update_post_meta( $coupon_id, 'coupon_amount', WC()->session->get('_discount') ? WC()->session->get('_discount') : 0 );
				
				}
				
			}
				
			WC()->cart->add_discount( $this->coupon_code );
			
		}
		
	}
	
	public function remove_session_var() {
		
		WC()->session->set( '_discount', false );
		
	}

}	