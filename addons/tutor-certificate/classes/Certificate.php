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
		$this->create_certificate();
	}

	public function create_certificate(){
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

		$this->generat_certificate($course_id);
	}

	public function generat_certificate($course_id, $debug = false){
		$duration           = get_post_meta( $course_id, '_course_duration', true );
		$durationHours      = (int) tutor_utils()->avalue_dot( 'hours', $duration );
		$durationMinutes    = (int) tutor_utils()->avalue_dot( 'minutes', $duration );
		$course             = get_post($course_id);
		$completed          = tutor_utils()->is_completed_course($course_id);

		ob_start();
		include $this->template['path'].'certificate.php';
		$content = ob_get_clean();

		if ($debug){
			echo $content;
			die();
		}
		$this->generate_PDF($content);
	}

	public function generate_PDF($certificate_content = null){
		if ( ! $certificate_content){
			return;
		}
		require_once TUTOR_CERT()->path.'lib/vendor/autoload.php';

		$options =  new Options( apply_filters( 'tutor_cert_dompdf_options', array(
			'defaultFont'				=> 'sans',
			'isRemoteEnabled'			=> true,
			'isFontSubsettingEnabled'	=> true,
			// HTML5 parser requires iconv
			'isHtml5ParserEnabled'		=> extension_loaded('iconv') ? true : false,
		) ) );

		$dompdf = new Dompdf($options);
		//Getting Certificate to generate PDF
		$dompdf->loadHtml($certificate_content);

		//Setting Paper
		$dompdf->setPaper('A4', $this->template['orientation']);
		$dompdf->render();
		$dompdf->stream('certificate'.time().'.pdf');
	}

	public function pdf_style() {
		//$css = TUTOR_CERT()->path.'assets/css/pdf.css';
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


}