<?php

namespace TUTOR_ZOOM;

if (!defined('ABSPATH'))
	exit;

class Init {
	public $version = TUTOR_ZOOM_VERSION;
	public $path;
	public $url;
	public $basename;

	function __construct() {
		if (!function_exists('tutor')) {
			return;
		}
		$addonConfig = tutor_utils()->get_addon_config(TUTOR_ZOOM()->basename);
		$isEnable = (bool) tutor_utils()->avalue_dot('is_enable', $addonConfig);
		if (!$isEnable) {
			return;
		}

		$this->path = plugin_dir_path(TUTOR_ZOOM_FILE);
		$this->url = plugin_dir_url(TUTOR_ZOOM_FILE);
		$this->basename = plugin_basename(TUTOR_ZOOM_FILE);

		$this->load_tutor_zoom();
	}

	public function load_tutor_zoom() {
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->zoom = new Zoom();

		//add_filter('tutor/options/attr', array($this, 'add_options'));
	}

	/**
	 * @param $className
	 *
	 * Auto Load class and the files
	 */
	private function loader($className) {
		if (!class_exists($className)) {
			$className = preg_replace(
				array('/([a-z])([A-Z])/', '/\\\/'),
				array('$1$2', DIRECTORY_SEPARATOR),
				$className
			);

			$className = str_replace('TUTOR_ZOOM' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $className);
			$file_name = $this->path . $className . '.php';

			if (file_exists($file_name) && is_readable($file_name)) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run() {
		register_activation_hook(TUTOR_ZOOM_FILE, array($this, 'tutor_activate'));
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate() {
		$version = get_option('tutor_zoom_version');
		//Save Option
		if (!$version) {
			update_option('tutor_zoom_version', TUTOR_ZOOM_VERSION);
		}
	}

	public function add_options($attr) {
		$attr['zoom'] = array(
			'label' => __( 'Zoom', 'tutor-pro' ),
			'sections'    => array(
				'api' => array(
					'label' => __('Main settings', 'tutor-pro'),
					'desc' => __('Tutor Zoom Settings', 'tutor-pro'),
					'fields' => array(
						'api_key' => array(
							'type'      => 'text',
							'label'     => __('API Key', 'tutor'),
							'default'   => '',
							'desc'      => __('The name under which all the emails will be sent',	'tutor'),
						),
						'api_secret' => array(
							'type'      => 'text',
							'label'     => __('API Secret Key', 'tutor'),
							'default'   => '',
							'desc'      => __('The name under which all the emails will be sent',	'tutor'),
						),
					),
				),
				'meeting' => array(
					'label' => __('Meeting settings', 'tutor-pro'),
					'desc' => __('Tutor Zoom Settings', 'tutor-pro'),
					'fields' => array(
						'join_before_host' => array(
							'type'      	=> 'checkbox',
							'label'     	=> __('Join Before Host', 'tutor-pro'),
							'label_title' 	=> __('Enable', 'tutor-pro'),
							'desc'      	=> __('Join meeting before host start the meeting. Only for scheduled or recurring mettings', 'tutor-pro'),
						),
						'host_video' => array(
							'type'      	=> 'checkbox',
							'label'     	=> __('Host video', 'tutor-pro'),
							'label_title' 	=> __('Enable', 'tutor-pro'),
							'desc'      	=> __('By enabling this option, the student will be able to verify and share their certificates URL which is publicly accessible', 'tutor-pro'),
						),
						'participants_video' => array(
							'type'      	=> 'checkbox',
							'label'     	=> __('Participants video', 'tutor-pro'),
							'label_title' 	=> __('Enable', 'tutor-pro'),
							'desc'      	=> __('By enabling this option, the student will be able to verify and share their certificates URL which is publicly accessible', 'tutor-pro'),
						),
						'mute_participants' => array(
							'type'      	=> 'checkbox',
							'label'     	=> __('Mute Participants', 'tutor-pro'),
							'label_title' 	=> __('Enable', 'tutor-pro'),
							'desc'      	=> __('By enabling this option, the student will be able to verify and share their certificates URL which is publicly accessible', 'tutor-pro'),
						),
						'enforce_login' => array(
							'type'      	=> 'checkbox',
							'label'     	=> __('Enforce Login', 'tutor-pro'),
							'label_title' 	=> __('Enable', 'tutor-pro'),
							'desc'      	=> __('By enabling this option, the student will be able to verify and share their certificates URL which is publicly accessible', 'tutor-pro'),
						),
					),
				),
			),
		);
		
		return $attr;
	}
}
