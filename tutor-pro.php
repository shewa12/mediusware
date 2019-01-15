<?php
/*
Plugin Name: Tutor Pro
Plugin URI: https://themeum.com/tutor-pro
Description: Power up Tutor LMS plugins by Tutor Pro
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
define('TUTOR_PRO_VERSION', '1.0.0');
define('TUTOR_PRO_FILE', __FILE__);

if ( ! function_exists('tutor')) {
	function tutor_pro() {
		$path = plugin_dir_path( TUTOR_PRO_FILE );
		$isPro = (bool) file_exists($path.'addons/');

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

/**
 * Addons supports
 */
add_action('plugins_loaded', 'tutor_pro_load_addons');
if ( ! function_exists('tutor_pro_load_addons')){
	function tutor_pro_load_addons(){
		$addonsDir = array_filter(glob(tutor_pro()->path.'addons/*'), 'is_dir');

		if (count($addonsDir) > 0) {
			foreach ($addonsDir as $key => $value) {
				$addon_dir_name = str_replace(dirname($value).'/', '', $value);
				$file_name = tutor_pro()->path . 'addons/'.$addon_dir_name.'/'.$addon_dir_name.'.php';
				if ( file_exists($file_name) ){
					include_once $file_name;
				}
			}
		}
	}
}