<?php
/*
Plugin Name: Tutor LMS Pro
Plugin URI: https://www.themeum.com/product/tutor-lms/
Description: Power up Tutor LMS plugins by Tutor Pro
Author: Themeum
Version: 1.3.8
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 5.2
Text Domain: tutor-pro
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_PRO_VERSION', '1.3.8');
define('TUTOR_PRO_FILE', __FILE__);

if ( ! function_exists('tutor_pro')) {
	function tutor_pro() {
		$path = plugin_dir_path( TUTOR_PRO_FILE );
		$info = array(
			'path'              => $path,
			'url'               => plugin_dir_url( TUTOR_PRO_FILE ),
			'basename'          => plugin_basename( TUTOR_PRO_FILE ),
			'version'           => TUTOR_PRO_VERSION,
			'nonce_action'      => 'tutor_pro_nonce_action',
			'nonce'             => '_wpnonce',
		);

		return (object) $info;
	}
}

include 'classes/init.php';

$tutorPro = new \TUTOR_PRO\init();
$tutorPro->run(); //Boom
