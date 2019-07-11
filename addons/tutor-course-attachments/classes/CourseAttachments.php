<?php
/**
 * Tutor Course attachments Main Class
 */

namespace TUTOR_CA;

use TUTOR\Tutor_Base;

class CourseAttachments extends Tutor_Base {

	public function __construct() {
		parent::__construct();

		add_action( 'add_meta_boxes', array($this, 'register_meta_box') );
		add_action('tutor/dashboard_course_builder_form_field_after', array($this, 'register_meta_box_in_frontend'));

		add_action('save_post_'.$this->course_post_type, array($this, 'save_course_meta'));
	}

	public function register_meta_box(){
		$coursePostType = tutor()->course_post_type;

		/**
		 * Check is allow private file upload
		 */
		add_meta_box( 'tutor-course-attachments', __( 'Attachments, private files', 'tutor' ), array($this, 'course_attachments_metabox'), $coursePostType, 'advanced', 'high' );
	}

	public function register_meta_box_in_frontend(){
		course_builder_section_wrap($this->course_attachments_metabox($echo = false), 'Course Attachments');
	}

	public function course_attachments_metabox($echo = true){
		ob_start();
		include  TUTOR_CA()->path.'views/metabox/course-attachments-metabox.php';
		$content = ob_get_clean();

		if ($echo){
			echo $content;
		}else{
			return $content;
		}
	}

	public function save_course_meta($post_ID){
		//Attachments
		$attachments = array();
		if ( ! empty($_POST['tutor_attachments'])){
			$attachments = tutor_utils()->sanitize_array($_POST['tutor_attachments']);
			$attachments = array_unique($attachments);
		}
		update_post_meta($post_ID, '_tutor_attachments', $attachments);
	}


}