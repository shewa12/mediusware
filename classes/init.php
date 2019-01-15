<?php
namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_PRO_VERSION;
	public $path;
	public $url;
	public $basename;

	//Components

	function __construct() {

		$this->path = plugin_dir_path(TUTOR_PRO_FILE);
		$this->url = plugin_dir_url(TUTOR_PRO_FILE);
		$this->basename = plugin_basename(TUTOR_PRO_FILE);
		
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));

		do_action('tutor_pro_before_load');
		//Load Component from Class
		do_action('tutor_pro_loaded');
	}
	/**
	 * @param $className
	 *
	 * Auto Load class and the files
	 */
	private function loader($className) {
		if ( ! class_exists($className)){
			$className = preg_replace(
				array('/([a-z])([A-Z])/', '/\\\/'),
				array('$1-$2', DIRECTORY_SEPARATOR),
				$className
			);

			$className = str_replace('TUTOR/', 'classes/', $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	//Run the TUTOR right now
	public function run(){
		do_action('tutor_pro_before_run');

		register_activation_hook( TUTOR_PRO_FILE, array( $this, 'tutor_pro_activate' ) );

		do_action('tutor_pro_after_run');
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_pro_activate(){
		$version = get_option('tutor_pro_version');
		//Save Option
		if ( ! $version){
			update_option('tutor_pro_version', TUTOR_PRO_VERSION);
		}

	}


}