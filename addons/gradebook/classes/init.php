<?php
namespace TUTOR_GB;

if ( ! defined( 'ABSPATH' ) )
	exit;

class init{
	public $version = TUTOR_GB_VERSION;
	public $path;
	public $url;
	public $basename;

	//Module
	public $multi_instructors;

	function __construct() {
		if ( ! function_exists('tutor-pro')){
			return;
		}
		$addonConfig = tutor_utils()->get_addon_config(TUTOR_GB()->basename);
		$isEnable = (bool) tutils()->array_get('is_enable', $addonConfig);
		if ( ! $isEnable){
			return;
		}

		$this->path = plugin_dir_path(TUTOR_GB_FILE);
		$this->url = plugin_dir_url(TUTOR_GB_FILE);
		$this->basename = plugin_basename(TUTOR_GB_FILE);

		$this->load_TUTOR_GB();
	}

	public function load_TUTOR_GB(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));
		$this->multi_instructors = new GradeBook();

		add_filter('tutor/options/attr', array($this, 'add_options'));
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
				array('$1$2', DIRECTORY_SEPARATOR),
				$className
			);

			$className = str_replace('TUTOR_GB'.DIRECTORY_SEPARATOR, 'classes'.DIRECTORY_SEPARATOR, $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}

	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_GB_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('TUTOR_GB_version');
		//Save Option
		if ( ! $version){
			update_option('TUTOR_GB_version', TUTOR_GB_VERSION);
		}
	}

	/**
	 * @desc Add Greadbook Settings in Option Panel
	 * @since v 1.0.0
	 */
	public function add_options($attr){
		$attr['tutor_gradebook'] = array(
			'label' => __( 'Gradebook', 'tutor-pro' ),
			'sections'    => array(
				'general' => array(
					'label' => __('Gradebook', 'tutor-pro'),
					'desc' => __('Gradebook Settings', 'tutor-pro'),
					'fields' => array(
						'gradebook_enable_grade_point' => array(
							'type'      => 'checkbox',
							'label'     => __('Grade Point', 'tutor-pro'),
							'label_title' => __('Enable', 'tutor-pro'),
							'default' => '0',
							'desc'      => __('After enabling, grade point will be show it in the gradding besides grade name', 'tutor-pro'),
						),
						'gradebook_show_grade_scale' => array(
							'type'      => 'checkbox',
							'label'     => __('Grade Scale', 'tutor-pro'),
							'label_title' => __('Show', 'tutor-pro'),
							'default' => '0',
							'desc'      => sprintf(__('This option will allow you to show/view grade scale, such as 3.8%s', 'tutor-pro'), '<code>/4.0</code>'),
						),
						'gradebook_scale_separator' => array(
							'type'      => 'text',
							'label'     => __('Grade scale separator', 'tutor-pro'),
							'default'   => '/',
							'desc'      => sprintf(__('This will change grade label separating text, example, 3.6%s4.0 or 3.6%s4.0', 'tutor-pro'), '<code>/</code>', '<code>out of</code>'),
						),

						'gradebook_scale' => array(
							'type'      => 'text',
							'label'     => __('Grade Scale', 'tutor-pro'),
							'default'   => '4.0',
							'desc'      => __('This is gradebook scale', 'tutor-pro'),
						),

					),
				),
			),
		);
		return $attr;
	}

}