<?php
/**
 * Plugin Name: WooCommerce Custom Conditional Order Email
 * Plugin URI: https://esmondmccain.com
 * Description: Plugin for adding a custom WooCommerce email that sends admins an email when an order is received with certain shipping categories
 * Author: Esmond
 * Author URI: https://esmondmccain.com
 * Version: 0.1
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 *  Add a custom email to the list of emails WooCommerce should load
 *
 * @since 0.1
 * @param array $email_classes available email classes
 * @return array filtered available email classes
 */
function add_organic_product_order_woocommerce_email( $email_classes ) {

	// include our custom email class
	require_once( 'includes/class-wc-organic-product-order-email.php' );

	// add the email class to the list of email classes that WooCommerce loads
	$email_classes['WC_Organic_Product_Email'] = new WC_Organic_Product_Email();

	return $email_classes;

}
add_filter( 'woocommerce_email_classes', 'add_organic_product_order_woocommerce_email' );
