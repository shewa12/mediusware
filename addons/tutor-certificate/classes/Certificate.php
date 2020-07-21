<?php
/**
 * Tutor Multi Instructor
 */

namespace TUTOR_CERT;

use Dompdf\Dompdf;
use Dompdf\Options;

class Certificate{
	private $template;
	public function __construct() {
		if ( ! function_exists('tutor_utils')){
			return;
		}

		add_action('tutor_options_before_tutor_certificate', array($this, 'generate_options'));

		add_action('tutor_enrolled_box_after', array($this, 'certificate_download_btn'));
		add_action('init', array($this, 'download_certificate'));
		/**
		 * @since v.1.5.1
		 * View and download certificate
		 */
		add_action('init', array($this, 'download_certificate_public'));
		add_action('wp_loaded', array($this, 'view_certificate'));
	}

	public function download_certificate(){
		$download_action = sanitize_text_field(tutor_utils()->avalue_dot('tutor_action', $_GET));
		if ($download_action !== 'download_course_certificate' || ! is_user_logged_in()){
			return;
		}

		//Get the selected template
		$templates = $this->templates();
		$template = tutor_utils()->get_option('certificate_template');
		if ( ! $template){
			$template = 'default';
		}
		$this->template = tutor_utils()->avalue_dot($template, $templates);

		$course_id = (int) sanitize_text_field(tutor_utils()->avalue_dot('course_id', $_GET));
		$is_enrolled = tutor_utils()->is_enrolled($course_id);

		if ( ! $is_enrolled){
			return;
		}
		$is_completed = tutor_utils()->is_completed_course($course_id);
		if ( ! $is_completed){
			return;
		}

		$content = $this->generate_certificate($course_id);
		$this->generate_PDF($content);
	}

	/**
	 * View Certificate
	 * @since v.1.5.1
	 */
	public function view_certificate(){
		$cert_hash = sanitize_text_field(tutils()->array_get('cert_hash', $_GET));
		$show_certificate = (bool) tutils()->get_option('tutor_course_certificate_view');


		if (! $cert_hash || ! $show_certificate){
			return;
		}
		$completed = $this->completed_course($cert_hash);
		if ( ! $completed){
			return;
		}

		if ( ! extension_loaded('imagick') || ! class_exists('Imagick') ){
			die('ImageMagick extension is not installed on your server.');
		}

		$file = $this->get_PDF($completed, true);
		//generate image
		$cert_img = new \Imagick();
		$cert_img->readImageBlob($file);
		$cert_img->setFormat( "jpg" );

		$course = get_post($completed->course_id);
		$this->certificate_header_content($course->post_title, $cert_img);

		ob_start();
		include TUTOR_CERT()->path.'views/certificate.php';
		$content = ob_get_clean();
		echo $content;
		die();
	}

	/**
	 * Download PDF Certificate
	 * @since v.1.5.1
	 */
	public function download_certificate_public(){
		$cert_hash = sanitize_text_field(tutils()->array_get('cert_hash', $_GET));
		$tutor_action = sanitize_text_field(tutor_utils()->avalue_dot('tutor_action', $_GET));
		if ($tutor_action !== 'download_pdf_certificate' || ! $cert_hash){
			return;
		}
		$completed = $this->completed_course($cert_hash);
		if ( ! $completed){
			return;
		}
		$this->get_PDF($completed);
	}

	/**
	 * Get PDF content
	 * @since v.1.5.1
	 */
	public function get_PDF($completed, $debug=false){
		//Get the selected template
		$templates = $this->templates();
		$template = tutor_utils()->get_option('certificate_template');
		if ( ! $template){
			$template = 'default';
		}
		$this->template = tutor_utils()->avalue_dot($template, $templates);
		$oriantation = $this->template['orientation'];
		$width = $oriantation === 'portrait' ? '21cm' : '29.7cm';
		$content = $this->generate_certificate($completed->course_id, $completed);
		$pdf = $this->generate_PDF($content, $debug);
		if($debug) {
			return $pdf;
		}
	}
	

	public function generate_certificate($course_id, $completed=false){
		$duration           = get_post_meta( $course_id, '_course_duration', true );
		$durationHours      = (int) tutor_utils()->avalue_dot( 'hours', $duration );
		$durationMinutes    = (int) tutor_utils()->avalue_dot( 'minutes', $duration );
		$course             = get_post($course_id);
		$completed          = ($completed) ? $completed : tutor_utils()->is_completed_course($course_id);
		$user 				= ($completed) ? get_userdata($completed->completed_user_id) : wp_get_current_user();
		$completed_date		= '';
		if ($completed) {
			$wp_date_format		= get_option('date_format');
			$completed_date 	= date($wp_date_format, strtotime($completed->completion_date));
		}

		ob_start();
		include $this->template['path'].'certificate.php';
		$content = ob_get_clean();

		return $content;
	}

	public function generate_PDF($certificate_content=null, $debug=false){
		if ( ! $certificate_content){
			return;
		}
		require_once TUTOR_CERT()->path.'lib/vendor/autoload.php';

		$options =  new Options( apply_filters( 'tutor_cert_dompdf_options', array(
			'defaultFont'				=> 'Courier',
			'isRemoteEnabled'			=> true,
			'isFontSubsettingEnabled'	=> true,
			// HTML5 parser requires iconv
			'isHtml5ParserEnabled'		=> extension_loaded('iconv') ? true : false,
		) ) );

		$dompdf = new Dompdf($options);
		//Getting Certificate to generate PDF
		$dompdf->loadHtml($certificate_content, 'UTF-8');

		//Setting Paper
		$dompdf->setPaper('A4', $this->template['orientation']);
		$dompdf->render();
		if($debug) {
			return $dompdf->output();
		}
		ob_end_clean();
		$dompdf->stream('certificate'.tutor_time().'.pdf');
	}

	public function pdf_style() {
		$css = $this->template['path'].'pdf.css';

		ob_start();
		if (file_exists($css)) {
			include($css);
		}
		$css = ob_get_clean();
		$css = apply_filters( 'tutor_cer_css', $css, $this );

		echo $css;
	}

	public function certificate_download_btn(){
		$course_id = get_the_ID();
		$is_completed = tutor_utils()->is_completed_course($course_id);
		if ( ! $is_completed){
			return;
		}


		ob_start();
		include TUTOR_CERT()->path.'views/lesson-menu-after.php';
		$content = ob_get_clean();

		echo $content;
	}

	public function generate_options(){
		$templates = $this->templates();

		ob_start();
		include TUTOR_CERT()->path.'views/template_options.php';
		$content = ob_get_clean();

		echo $content;

	}


	public function templates(){
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
		foreach ($templates as $key => $template){
			$templates[$key]['path'] = trailingslashit(TUTOR_CERT()->path.'templates/'.$key);
			$templates[$key]['url'] = trailingslashit(TUTOR_CERT()->url.'templates/'.$key);
		}

		return apply_filters('tutor_certificate_templates', $templates);
	}

	/**
	 * Get completed course data
	 * @since v.1.5.1
	 */
	public function completed_course($cert_hash){
		global $wpdb;
		$is_completed = $wpdb->get_row(
			"SELECT comment_ID, 
					comment_post_ID as course_id, 
					comment_author as completed_user_id, 
					comment_date as completion_date, 
					comment_content as completed_hash 
			FROM	$wpdb->comments
			WHERE 	comment_agent = 'TutorLMSPlugin' 
					AND comment_type = 'course_completed' 
					AND comment_content = '$cert_hash';"
		);

		if ($is_completed){
			return $is_completed;
		}

		return false;
	}


	/**
	 * Certificate header og content
	 * @since v.1.5.1
	 */
	public function certificate_header_content($course_title, $cert_img){
		add_action( 'wp_head', function() use ($course_title, $cert_img) {
			$title = __('Course Completion Certificate', 'tutor-pro');
			$description = __('My course completion certificate for', 'tutor-pro').' "'.$course_title.'"';
			echo '
				<meta property=”og:title” content=”'.$title.'”/>
				<meta property=”og:description” content=”'.$description.'”/>
				<meta property=”og:image” content="data:image/jpg;base64,'.base64_encode($cert_img).'"/>
				<meta name=”twitter:title” content=”Your title here”/>
				<meta name=”twitter:description” content=”'.$description.'”/>
				<meta name=”twitter:image” content="data:image/jpg;base64,'.base64_encode($cert_img).'"/>
			';
		});
	}
}