<?php  

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MfnWoocommerceCartTmpl{
	public $post_id = false;
	public $strings = false;

	public function __construct($id = false) {

		if( !function_exists('is_woocommerce') ) return;
		global $mfn_global;

		$this->post_id = $id ?? false;
		$this->post_id = apply_filters( 'wpml_object_id', $this->post_id , get_post_type($this->post_id) , TRUE  );
		
		if( $this->post_id ) {
			$this->strings = !empty( get_post_meta($this->post_id, 'mfn-cart-template-data', true) ) ? json_decode(get_post_meta($this->post_id, 'mfn-cart-template-data', true)) : array();
		}
	}

	public function echo_all_cart_strings() {
		add_action('woocommerce_before_cart_totals', array($this, 'mfn_cart_totals_heading') ); // cart totals heading
		add_action('mfn_woocommerce_continue_shopping_string', array($this, 'mfn_continue_shopping_string') ); // cart totals heading
		add_action('mfn_woocommerce_proceed_to_checkout_button', array($this, 'mfn_proceed_to_checkout_button') ); // cart total proceed to checkout
		add_action('mfn_woocommerce_update_cart_label', array($this, 'mfn_update_cart_label') ); // cart table update cart button
		add_action('mfn_woocommerce_apply_coupon_label', array($this, 'mfn_apply_coupon_label') ); // cart table apply coupon label
		add_action('mfn_woocommerce_coupon_code_placeholder', array($this, 'mfn_coupon_code_placeholder') ); // cart table coupon code placeholder
	}

	// cart totals

	public function mfn_cart_totals_heading() {

		/*echo '<pre>';
		print_r($this->strings);
		echo '</pre>';*/

		$cart_totals_heading = $this->post_id && !empty( $this->strings->cart_totals->cart_totals_heading ) ? $this->strings->cart_totals->cart_totals_heading : esc_html__( 'Cart totals', 'woocommerce' );
		echo '<h4 class="title">'.$cart_totals_heading.'</h4>';
	}

	public function mfn_continue_shopping_string() {
		$continue_shopping_string = $this->post_id && !empty( $this->strings->cart_totals->continue_shopping_string ) ? $this->strings->cart_totals->continue_shopping_string : esc_html__( 'Continue shopping', 'woocommerce' );
		echo $continue_shopping_string;
	}

	public function mfn_proceed_to_checkout_button() {
		$proceed_checkout_label = $this->post_id && !empty( $this->strings->cart_totals->proceed_checkout_label ) ? $this->strings->cart_totals->proceed_checkout_label : esc_html__( 'Proceed to checkout', 'woocommerce' );
		echo $proceed_checkout_label;	
	}

	// cart table

	public function mfn_update_cart_label() {
		$update_cart_label = $this->post_id && !empty( $this->strings->cart_table->update_cart_label ) ? $this->strings->cart_table->update_cart_label : esc_html__( 'Update cart', 'woocommerce' );
		echo $update_cart_label;	
	}

	public function mfn_apply_coupon_label() {
		$apply_coupon_label = $this->post_id && !empty( $this->strings->cart_table->apply_coupon_label ) ? $this->strings->cart_table->apply_coupon_label : esc_html__( 'Apply coupon', 'woocommerce' );
		echo $apply_coupon_label;	
	}

	public function mfn_coupon_code_placeholder() {
		$coupon_code_placeholder = $this->post_id && !empty( $this->strings->cart_table->coupon_code_placeholder ) ? $this->strings->cart_table->coupon_code_placeholder : esc_html__( 'Coupon code', 'woocommerce' );
		echo $coupon_code_placeholder;	
	}



}