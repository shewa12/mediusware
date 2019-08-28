<?php
/*
Plugin Name: Tutor Enrolments
Plugin URI: https://www.themeum.com/product/tutor-pmpro
Description: Take advanced control on enrolments. Enroll student manually.
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-pmpro
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_ENROLMENTS_VERSION', '1.0.0');
define('TUTOR_ENROLMENTS_FILE', __FILE__);

/**
 * Showing config for addons central lists
 */
add_filter('tutor_addons_lists_config', 'tutor_enrolments_config');
function tutor_enrolments_config($config){
	$newConfig = array(
		'name'          => __('Enrolments', 'tutor-pmpro'),
		'description'   => 'Take advanced control on enrolments. Enroll the student manually.',
	);
	$basicConfig = (array) TUTOR_ENROLMENTS();
	$newConfig = array_merge($newConfig, $basicConfig);

	$config[plugin_basename( TUTOR_ENROLMENTS_FILE )] = $newConfig;
	return $config;
}

if ( ! function_exists('TUTOR_ENROLMENTS')) {
	function TUTOR_ENROLMENTS() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_ENROLMENTS_FILE ),
			'url'               => plugin_dir_url( TUTOR_ENROLMENTS_FILE ),
			'basename'          => plugin_basename( TUTOR_ENROLMENTS_FILE ),
			'version'           => TUTOR_ENROLMENTS_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new \TUTOR_ENROLMENTS\init();
$tutor->run(); //Boom