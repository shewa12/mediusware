<?php
/*
Plugin Name: Tutor WooCommerce
Plugin URI: http://https://themeum.com/tutor
Description: Tutor WooCommerce help to you to sell the course in smart way
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-woocommerce
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;


/**
 * Defined the tutor main file
 */
define('TUTOR_WC_VERSION', '1.0.0');
define('TUTOR_WC_FILE', __FILE__);

/**
 * Showing config for addons central lists
 */
add_filter('tutor_addons_lists_config', 'tutor_woocommerce_config');
function tutor_woocommerce_config($config){
	$newConfig = array(
		'name'          => __('Tutor WooCommerce', 'tutor-woocommerce'),
		'description'   => 'Tutor WooCommerce help to you to sell the course in smart way',
	);
	$basicConfig = (array) tutor_wc();
	$newConfig = array_merge($newConfig, $basicConfig);

	$config[plugin_basename( TUTOR_WC_FILE )] = $newConfig;
	return $config;
}

if ( ! function_exists('tutor_wc')) {
	function tutor_wc() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_WC_FILE ),
			'url'               => plugin_dir_url( TUTOR_WC_FILE ),
			'basename'          => plugin_basename( TUTOR_WC_FILE ),
			'version'           => TUTOR_WC_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new TUTOR_WC\init();
$tutor->run(); //Boom