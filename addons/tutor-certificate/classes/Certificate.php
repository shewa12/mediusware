<?php

/**
 * Tutor Multi Instructor
 */

namespace TUTOR_CERT;

class Certificate {
	private $template;
	private $certificates_dir_name = 'tutor-certificates';
	private $certificate_stored_key = 'tutor_certificate_has_image';

	public function __construct() {
		if (!function_exists('tutor_utils')) {
			return;
		}

		add_action('tutor_options_before_tutor_certificate', array($this, 'generate_options'));
		add_action('tutor_enrolled_box_after', array($this, 'certificate_download_btn'));

		add_action('wp_loaded', array($this, 'get_fonts'));
		add_action('wp_loaded', array($this, 'view_certificate'));
		add_action('wp_loaded', array($this, 'send_certificate_html'));
		add_action('wp_loaded', array($this, 'check_if_certificate_generated'));
		add_action('wp_loaded', array($this, 'store_certificate_image'));
	}

	public function get_fonts() {
		if(($_GET['tutor_action'] ?? '') !== 'get_fonts') { return; }

		$url_base = tutor_pro()->url .'addons/tutor-certificate/assets/fonts/';
		$path_base = $this->cross_platform_path(dirname(__DIR__).'/assets/css/');

		$default_files = $path_base . 'font-loader.css';
		$default_fonts = file_get_contents($default_files);

		$font_faces = str_replace('./fonts/', $url_base, $default_fonts);

		// Now load template fonts
		$this->prepare_template_data();
		$font_css = $this->template['path'].'font.css';
		if (file_exists($font_css)) {
			$faces = file_get_contents($font_css);
			$faces = str_replace('./fonts/', $this->template['url'].'fonts/', $faces);
			$font_faces .= $faces;
		}
		
		exit($font_faces);
	}

	public function send_certificate_html() {
		$id = $_GET['course_id'] ?? '';
		$action = $_GET['tutor_action'] ?? '';

		if (is_numeric($id) && $action == 'generate_course_certificate') {

			$this->prepare_template_data();

			// Get certificate html
			$content = $this->generate_certificate($id);
			exit($content);
		}
	}

	private function cross_platform_path($path) {
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

		return $path;
	}

	private function prepare_template_data() {	
		if (!$this->template) {
			//Get the selected template
			$templates = $this->templates();

			$template = tutor_utils()->get_option('certificate_template');
			!$template ? $template = 'default' : 0;

			$this->template = tutor_utils()->avalue_dot($template, $templates);
		}
	}

	private function get_template_check_sum() {
		$this->prepare_template_data();
		
		$path = $this->template['path'].'certificate.php';
		$path = $this->cross_platform_path($path);
		
		return md5_file($path);
	}

	public function check_if_certificate_generated() {
		$action = $_GET['tutor_action'] ?? '';

		if ($action == 'check_if_certificate_generated') {
			$completed = $this->completed_course($_GET['cert_hash'] ?? '');

			if ($completed) {
				$checksum = get_comment_meta($completed->certificate_id, $this->certificate_stored_key, true);

				$is_string = is_string($checksum);
				$non_numeric = !is_numeric($checksum); // Backward compatible
				$checksum_matched = $checksum==$this->get_template_check_sum();

				exit(($is_string && $non_numeric && $checksum_matched) ? 'yes' : 'no');
			}
		}
	}

	private function delete_old_certificate_image($certificates_dir, $course_id, $certificate_id, $hash) {
		// Delete old one
		$old_checksum = get_comment_meta($certificate_id, $this->certificate_stored_key, true);

		$old_path = $certificates_dir . '/' . $course_id . '/' . $old_checksum . '-' . $hash . '.jpg';
		$old_path = $this->cross_platform_path($old_path);

		file_exists($old_path) ? unlink($old_path) : 0;
	}

	public function store_certificate_image() {
		// Collect post data
		$hash = $_POST['cert_hash'] ?? '';
		$action = $_POST['tutor_action'] ?? '';
		$image = $_POST['certificate_image'] ?? null;
		$completed = $this->completed_course($hash);

		if ($completed && is_string($hash) && $action == 'store_certificate_image') {

			// et the dir from outside of the filter hook. Otherwise infinity loop will coccur.
			$certificates_dir = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $this->certificates_dir_name;
			$checksum = $this->get_template_check_sum();

			// Store new file
			wp_mkdir_p($certificates_dir);
			$decode = base64_decode(str_replace('data:image/jpeg;base64,', '', $image));
			$file_dest = $certificates_dir.DIRECTORY_SEPARATOR.$checksum.'-'.$hash . '.jpg';
			file_put_contents($file_dest, $decode);
			
			// Delete old one
			$this->delete_old_certificate_image($certificates_dir, $completed->course_id, $completed->certificate_id, $hash);

			// Update new 
			update_comment_meta($completed->certificate_id, $this->certificate_stored_key, $checksum);

			exit('ok');
		}
	}

	/**
	 * View Certificate
	 * @since v.1.5.1
	 */
	public function view_certificate() {
		$cert_hash = sanitize_text_field(tutils()->array_get('cert_hash', $_GET));
		$show_certificate = (bool) tutils()->get_option('tutor_course_certificate_view');

		if (!$cert_hash || !$show_certificate || !empty($_GET['tutor_action'])) {
			return;
		}
		$completed = $this->completed_course($cert_hash);
		if (!$completed) {
			return;
		}

		$course = get_post($completed->course_id);

		$course_id = $completed->course_id;
		$upload_dir = wp_upload_dir();
		
		$certificate_dir = $upload_dir['baseurl'] . '/' . $this->certificates_dir_name;
		$check_sum = get_comment_meta($completed->certificate_id, $this->certificate_stored_key, true);

		$cert_img =  $certificate_dir . '/' . $check_sum . '-' . $cert_hash . '.jpg';
		$this->certificate_header_content($course->post_title, $cert_img);

		ob_start();
		include TUTOR_CERT()->path . 'views/certificate.php';
		$content = ob_get_clean();
		echo $content;
		die();
	}

	public function generate_certificate($course_id, $completed = false) {
		$duration           = get_post_meta($course_id, '_course_duration', true);
		$durationHours      = (int) tutor_utils()->avalue_dot('hours', $duration);
		$durationMinutes    = (int) tutor_utils()->avalue_dot('minutes', $duration);
		$course             = get_post($course_id);
		$completed          = ($completed) ? $completed : tutor_utils()->is_completed_course($course_id);
		$user 				= ($completed) ? get_userdata($completed->completed_user_id) : wp_get_current_user();
		$completed_date		= '';
		if ($completed) {
			$wp_date_format		= get_option('date_format');
			$completed_date 	= date($wp_date_format, strtotime($completed->completion_date));

			// Translate month name
			$converter = function ($matches) {
				$month = __($matches[0]);

				// Make first letter uppercase if it's not unicode character.
				strlen($month) == strlen(utf8_decode($month)) ? $month = ucfirst($month) : 0;

				return $month;
			};
			$completed_date		= preg_replace_callback('/[a-z]+/i', $converter, $completed_date);

			// Translate day and year digits
			$completed_date		= preg_replace_callback('/[0-9]/', function ($m) {
				return __($m[0]);
			}, $completed_date);
		}

		ob_start();
		include $this->template['path'] . 'certificate.php';
		$content = ob_get_clean();

		return $content;
	}

	public function pdf_style() {
		$css = $this->template['path'] . 'pdf.css';

		ob_start();
		if (file_exists($css)) {
			include($css);
		}
		$css = ob_get_clean();
		$css = apply_filters('tutor_cer_css', $css, $this);

		echo $css;
	}

	public function certificate_download_btn() {
		$course_id = get_the_ID();
		$is_completed = tutor_utils()->is_completed_course($course_id);
		if (!$is_completed) {
			return;
		}

		ob_start();
		include TUTOR_CERT()->path . 'views/lesson-menu-after.php';
		$content = ob_get_clean();

		echo $content;
	}

	public function generate_options() {
		$templates = $this->templates();

		ob_start();
		include TUTOR_CERT()->path . 'views/template_options.php';
		$content = ob_get_clean();

		echo $content;
	}


	public function templates() {
		$templates = array(
			'default'       => array('name' => 'Default', 'orientation' => 'landscape'),
			'template_1'    => array('name' => 'Abstract Landscape', 'orientation' => 'landscape'),
			'template_2'    => array('name' => 'Abstract Portrait', 'orientation' => 'portrait'),
			'template_3'    => array('name' => 'Decorative Landscape', 'orientation' => 'landscape'),
			'template_4'    => array('name' => 'Decorative Portrait', 'orientation' => 'portrait'),
			'template_5'    => array('name' => 'Geometric Landscape', 'orientation' => 'landscape'),
			'template_6'    => array('name' => 'Geometric Portrait', 'orientation' => 'portrait'),
			'template_7'    => array('name' => 'Minimal Landscape', 'orientation' => 'landscape'),
			'template_8'    => array('name' => 'Minimal Portrait', 'orientation' => 'portrait'),
			'template_9'    => array('name' => 'Floating Landscape', 'orientation' => 'landscape'),
			'template_10'   => array('name' => 'Floating Portrait', 'orientation' => 'portrait'),
			'template_11'   => array('name' => 'Stripe Landscape', 'orientation' => 'landscape'),
			'template_12'   => array('name' => 'Stripe Portrait', 'orientation' => 'portrait'),
		);
		foreach ($templates as $key => $template) {
			$templates[$key]['path'] = trailingslashit(TUTOR_CERT()->path . 'templates/' . $key);
			$templates[$key]['url'] = trailingslashit(TUTOR_CERT()->url . 'templates/' . $key);
		}

		return apply_filters('tutor_certificate_templates', $templates);
	}

	/**
	 * Get completed course data
	 * @since v.1.5.1
	 */
	public function completed_course($cert_hash) {
		global $wpdb;
		$is_completed = $wpdb->get_row(
			"SELECT comment_ID as certificate_id, 
					comment_post_ID as course_id, 
					comment_author as completed_user_id, 
					comment_date as completion_date, 
					comment_content as completed_hash 
			FROM	$wpdb->comments
			WHERE 	comment_agent = 'TutorLMSPlugin' 
					AND comment_type = 'course_completed' 
					AND comment_content = '$cert_hash';"
		);

		if ($is_completed) {
			return $is_completed;
		}

		return false;
	}


	/**
	 * Certificate header og content
	 * @since v.1.5.1
	 */
	public function certificate_header_content($course_title, $cert_img) {
		add_action('wp_head', function () use ($course_title, $cert_img) {
			$title = __('Course Completion Certificate', 'tutor-pro');
			$description = __('My course completion certificate for', 'tutor-pro') . ' "' . $course_title . '"';
			echo '
				<meta property="og:title" content="' . $title . '"/>
				<meta property="og:description" content="' . $description . '"/>
				<meta property="og:image" content="' . $cert_img . '"/>
				<meta name="twitter:title" content="Your title here"/>
				<meta name="twitter:description" content="' . $description . '"/>
				<meta name="twitter:image" content="' . $cert_img . '"/>
			';
		});
	}
}
