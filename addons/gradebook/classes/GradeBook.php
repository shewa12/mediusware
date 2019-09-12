<?php
/**
 * Tutor Multi Instructor
 */

namespace TUTOR_GB;

class GradeBook{

	private $validation_error;

	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('tutor_admin_register', 	array($this, 'register_menu'));

		add_action('tutor_action_add_new_gradebook', array($this, 'add_new_gradebook'));

		add_action('tutor_quiz/attempt_ended', array($this, 'quiz_attempt_ended'));
		add_action('tutor_assignment/evaluate/after', array($this, 'generate_grade'));
	}

	public function admin_scripts($page){
		if ($page === 'tutor-lms-pro_page_tutor_gradebook') {
			wp_enqueue_script( 'tutor-gradebook', TUTOR_GB()->url . 'assets/js/gradebook.js', array(), TUTOR_GB()->version, true );
		}
	}
	
	public function register_menu(){
		add_submenu_page('tutor', __('Grade Book', 'tutor-pro'), __('Grade Book', 'tutor-pro'), 'manage_tutor', 'tutor_gradebook', array($this, 'tutor_gradebook') );
	}

	public function tutor_gradebook(){
		include TUTOR_GB()->path.'views/pages/grade_book.php';
	}

	public function add_new_gradebook(){
		global $wpdb;

		//Checking nonce
		tutor_utils()->checking_nonce();

		$required_fields = apply_filters('tutor_gradebook_required_fields', array(
			'grade_name'            => __('Grade name field is required', 'tutor'),
			//'number_percent_from'   => __('Number percent from field is required', 'tutor'),
			'number_percent_to'     => __('Number percent to field is required', 'tutor'),
		));

		$validation_errors = array();
		foreach ($required_fields as $required_key => $required_value){
			if (empty($_POST[$required_key])){
				$validation_errors[$required_key] = $required_value;
			}
		}

		if (tutils()->count($validation_errors)){
			$this->validation_error = $validation_errors;
			add_filter('tutor_gradebook_validation_error', array($this, 'return_validation_error'));
			return;
		}

		$number_percent_from = (int) sanitize_text_field(tutils()->array_get('number_percent_from', $_POST));

		$data = array(
			'grade_name'            => sanitize_text_field(tutils()->array_get('grade_name', $_POST)),
			'grade_point'           => sanitize_text_field(tutils()->array_get('grade_point', $_POST)),
			'percent_from'          => $number_percent_from,
			'percent_to'            => sanitize_text_field(tutils()->array_get('number_percent_to', $_POST)),
			'grade_config'          => maybe_serialize(tutils()->array_get('grade_config', $_POST)),
		);

		$wpdb->insert($wpdb->tutor_gradebooks, $data);
		$gradebook_id = (int) $wpdb->insert_id;

		tutor_flash_set('success', __('Gradebook has been added successfully', 'tutor-pro') );
		wp_redirect(admin_url('admin.php?page=tutor_gradebook'));
		exit();
	}

	public function return_validation_error(){
		return $this->validation_error;
	}

	/**
	 * @param $attempt_id
	 *
	 * Generate Quiz Result
	 * @since v.1.4.2
	 */

	public function quiz_attempt_ended($attempt_id){
		global $wpdb;

		$attempt = tutils()->get_attempt($attempt_id);
		$earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;

		$gradebook = $wpdb->get_row("SELECT * FROM {$wpdb->tutor_gradebooks} 
		WHERE percent_from <= {$earned_percentage} 
		AND percent_to >= {$earned_percentage} ORDER BY gradebook_id ASC LIMIT 1  ");

		if ( ! $gradebook){
			return;
		}

		$gradebook_data = array(
			'user_id'   => $attempt->user_id,
			'course_id'   => $attempt->course_id,
			'quiz_id'   => $attempt->quiz_id,
			'gradebook_id'   => $gradebook->gradebook_id,
			'result_for'   => 'quiz',
			'grade_name'   => $gradebook->grade_name,
			'grade_point'   => $gradebook->grade_point,
			'earned_grade_point'   => $gradebook->grade_point,
			'generate_date'   => date("Y-m-d H:i:s"),
			'update_date'   => date("Y-m-d H:i:s"),
		);

		$gradebook_result_id = 0;
		$gradebook_result = $wpdb->get_row("SELECT * FROM {$wpdb->tutor_gradebooks_results} 
			WHERE result_for = 'quiz' 
			AND user_id = {$attempt->user_id} 
			AND course_id = {$attempt->course_id} 
			AND quiz_id = {$attempt->quiz_id} ");

		if ($gradebook_result){
			$gradebook_result_id = $gradebook_result->gradebook_result_id;
			//Update Gradebook Result
			unset($gradebook_data['generate_date']);
			$wpdb->update($wpdb->tutor_gradebooks_results, $gradebook_data, array('gradebook_result_id' => $gradebook_result->gradebook_result_id ) );
		}else{
			$wpdb->insert($wpdb->tutor_gradebooks_results, $gradebook_data);
			$gradebook_result_id = (int) $wpdb->insert_id;
		}

		do_action('tutor_gradebook/quiz_result/after', $gradebook_result_id);
	}

	public function generate_grade($submitted_id){
		global $wpdb;

		do_action('tutor_gradebook/assignment_generate/before', $submitted_id);
		do_action('tutor_gradebook/generate/before');

		$submitted_info = tutor_utils()->get_assignment_submit_info($submitted_id);
		if ( $submitted_info) {
			$max_mark = tutor_utils()->get_assignment_option( $submitted_info->comment_post_ID, 'total_mark' );
			$given_mark = get_comment_meta( $submitted_id, 'assignment_mark', true );

			$earned_percentage = $given_mark > 0 ? ( number_format(($given_mark * 100) / $max_mark)) : 0;
			
			$gradebook = $wpdb->get_row("SELECT * FROM {$wpdb->tutor_gradebooks} 
			WHERE percent_from <= {$earned_percentage} 
			AND percent_to >= {$earned_percentage} ORDER BY gradebook_id ASC LIMIT 1  ");

			$gradebook_data = apply_filters('tutor_gradebook_data', array(
				'user_id'               => $submitted_info->user_id,
				'course_id'             => $submitted_info->comment_parent,
				'assignment_id'         => $submitted_info->comment_post_ID,
				'gradebook_id'          => $gradebook->gradebook_id,
				'result_for'            => 'assignment',
				'grade_name'            => $gradebook->grade_name,
				'grade_point'           => $gradebook->grade_point,
				'earned_grade_point'    => $gradebook->grade_point,
				'generate_date'         => date("Y-m-d H:i:s"),
				'update_date'           => date("Y-m-d H:i:s"),
			));

			$gradebook_result_id = 0;
			$gradebook_result = $wpdb->get_row("SELECT * FROM {$wpdb->tutor_gradebooks_results} 
			WHERE result_for = 'assignment' 
			AND user_id = {$submitted_info->user_id} 
			AND course_id = {$submitted_info->comment_parent} 
			AND assignment_id = {$submitted_info->comment_post_ID} ");

			if ($gradebook_result){
				$gradebook_result_id = (int)  $gradebook_result->gradebook_result_id;
				//Update Gradebook Result
				unset($gradebook_data['generate_date']);
				$wpdb->update($wpdb->tutor_gradebooks_results, $gradebook_data, array('gradebook_result_id' => $gradebook_result->gradebook_result_id ) );
			}else{
				$wpdb->insert($wpdb->tutor_gradebooks_results, $gradebook_data);
				$gradebook_result_id = (int) $wpdb->insert_id;
			}

			do_action('tutor_gradebook/assignment_generate/after', $gradebook_result_id);
			do_action('tutor_gradebook/generate/after', $gradebook_result_id);
		}
		
	}


}