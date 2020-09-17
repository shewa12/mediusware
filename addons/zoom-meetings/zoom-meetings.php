<?php
/*
Plugin Name: Tutor Zoom Meetings
Plugin URI: https://www.themeum.com/product/tutor-lms
Description: Send email on various tutor events
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-pro
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_ZOOM_VERSION', '1.0.0');
define('TUTOR_ZOOM_FILE', __FILE__);

/**
 * Showing config for addons central lists
 */
add_filter('tutor_addons_lists_config', 'tutor_email_config');
function tutor_email_config($config){
	$newConfig = array(
		'name'          => __('E-Mail', 'tutor-pro'),
		'description'   => 'Send email on various tutor events',
	);
	$basicConfig = (array) TUTOR_EMAIL();
	$newConfig = array_merge($newConfig, $basicConfig);

	$config[plugin_basename( TUTOR_ZOOM_FILE )] = $newConfig;
	return $config;
}

if ( ! function_exists('TUTOR_ZOOM')) {
	function TUTOR_ZOOM() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_ZOOM_FILE ),
			'url'               => plugin_dir_url( TUTOR_ZOOM_FILE ),
			'basename'          => plugin_basename( TUTOR_ZOOM_FILE ),
			'version'           => TUTOR_ZOOM_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/Init.php';
$tutor = new TUTOR_ZOOM\Init();
$tutor->run(); //Boom