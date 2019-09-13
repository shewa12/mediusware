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
		add_filter('tutor_assignment/single/results/after', array($this, 'filter_assignment_result'), 10, 3);
		add_filter('tutor_quiz/previous_attempts_html', array($this, 'previous_attempts_html'), 10, 3);
		add_action('tutor_course/single/actions_btn_group/after', array($this, 'course_single_actions_btn_group'), 10, 0);

		add_action('tutor_action_gradebook_generate_for_course', array($this, 'gradebook_generate_for_course'), 10, 0);
	}

	public function admin_scripts($page){
		if ($page === 'tutor-lms-pro_page_tutor_gradebook') {
			wp_enqueue_script( 'tutor-gradebook', TUTOR_GB()->url . 'assets/js/gradebook.js', array(), TUTOR_GB()->version, true );
		}
	}

	public function register_menu(){
		add_submenu_page('tutor-pro', __('Grade Book', 'tutor-pro'), __('Grade Book', 'tutor-pro'), 'manage_tutor', 'tutor_gradebook', array($this, 'tutor_gradebook') );
	}

	public function tutor_gradebook(){
		include TUTOR_GB()->path.'views/pages/grade_book.php';
	}

	public function add_new_gradebook(){
		global $wpdb;

		//Checking nonce
		tutor_utils()->checking_nonce();

		$required_fields = apply_filters('tutor_gradebook_required_fields', array(
			'grade_name'            => __('Grade name field is required', 'tutor-pro'),
			//'number_percent_from'   => __('Number percent from field is required', 'tutor-pro'),
			'number_percent_to'     => __('Number percent to field is required', 'tutor-pro'),
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



	public function get_gradebook_by_percent($percent = 0){
		global $wpdb;
		$gradebook = $wpdb->get_row("SELECT * FROM {$wpdb->tutor_gradebooks} 
		WHERE percent_from <= {$percent} 
		AND percent_to >= {$percent} ORDER BY gradebook_id ASC LIMIT 1  ");

		return $gradebook;
	}

	public function get_generated_gradebook($type = 'final', $ref_id = 0, $user_id = 0){
		global $wpdb;

		$user_id = tutils()->get_user_id($user_id);

		$res = false;
		if ($type === 'all'){
			$res = $wpdb->get_results("SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND course_id = {$ref_id} 
					AND result_for != 'final' ");

		}elseif ($type === 'quiz'){

			$res = $wpdb->get_row("SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND quiz_id = {$ref_id} 
					AND result_for = 'quiz' ");

		}elseif ($type === 'assignment'){
			$res = $wpdb->get_row("SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND assignment_id = {$ref_id} 
					AND result_for = 'assignment' ");
		}

		return $res;
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


	public function filter_assignment_result($content, $submit_id, $assignment_id){

		$max_mark = tutor_utils()->get_assignment_option($assignment_id, 'total_mark');
		$pass_mark = tutor_utils()->get_assignment_option($assignment_id, 'pass_mark');
		$given_mark = get_comment_meta($submit_id, 'assignment_mark', true);
		$grade = $this->get_generated_gradebook('assignment', $assignment_id);

		ob_start();
		?>

		<div class="assignment-result-wrap">
			<h4><?php echo sprintf(__('You received %s points out of %s', 'tutor-pro'), "<span class='received-marks'>{$given_mark}</span>", "<span class='out-of-marks'>{$max_mark}</span>") ?></h4>
			<h4 class="submitted-assignment-grade">
				<?php _e('Your grade is ', 'tutor-pro');

				echo $this->generate_grade_html($grade);
				echo $given_mark >= $pass_mark ? "<span class='submitted-assignment-grade-pass'> (".__('Passed', 'tutor-pro').")</span>" : "<span class='submitted-assignment-grade-failed'> (".__('Failed', 'tutor-pro').")</span>";
				?>
			</h4>
		</div>

		<?php
		return ob_get_clean();
	}

	public function previous_attempts_html($previous_attempts_html, $previous_attempts, $quiz_id){
		$passing_grade = tutor_utils()->get_quiz_option($quiz_id, 'passing_grade', 0);

		ob_start();
		?>

		<h4 class="tutor-quiz-attempt-history-title"><?php _e('Previous attempts', 'tutor-pro'); ?></h4>
		<div class="tutor-quiz-attempt-history single-quiz-page">
			<table>
				<tr>
					<th>#</th>
					<th><?php _e('Time', 'tutor-pro'); ?></th>
					<th><?php _e('Questions', 'tutor-pro'); ?></th>
					<th><?php _e('Total Marks', 'tutor-pro'); ?></th>
					<th><?php _e('Earned Marks', 'tutor-pro'); ?></th>
					<th><?php _e('Pass Mark', 'tutor-pro'); ?></th>
					<th><?php _e('Grade', 'tutor-pro'); ?></th>
					<th><?php _e('Result', 'tutor-pro'); ?></th>
				</tr>
				<?php
				foreach ( $previous_attempts as $attempt){
					?>
					<tr>
						<td><?php echo $attempt->attempt_id; ?></td>
						<td title="<?php _e('Time', 'tutor-pro'); ?>">
							<?php
							echo date_i18n(get_option('date_format'), strtotime($attempt->attempt_started_at)).' '.date_i18n(get_option('time_format'), strtotime($attempt->attempt_started_at));

							if ($attempt->is_manually_reviewed){
								?>
								<p class="attempt-reviewed-text">
									<?php
									echo __('Manually reviewed at', 'tutor-pro').date_i18n(get_option('date_format', strtotime($attempt->manually_reviewed_at))).' '.date_i18n(get_option('time_format', strtotime($attempt->manually_reviewed_at)));
									?>
								</p>
								<?php
							}
							?>
						</td>
						<td  title="<?php _e('Questions', 'tutor-pro'); ?>">
							<?php echo $attempt->total_questions; ?>
						</td>

						<td title="<?php _e('Total Marks', 'tutor-pro'); ?>">
							<?php echo $attempt->total_marks; ?>
						</td>

						<td title="<?php _e('Earned Marks', 'tutor-pro'); ?>">
							<?php
							$earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
							echo $attempt->earned_marks."({$earned_percentage}%)";
							?>
						</td>

						<td title="<?php _e('Pass Mark', 'tutor-pro'); ?>">
							<?php
							$pass_marks = ($attempt->total_marks * $passing_grade) / 100;
							if ($pass_marks > 0){
								echo number_format_i18n($pass_marks, 2);
							}
							echo "({$passing_grade}%)";
							?>
						</td>

						<td>
							<?php
							$grade = $this->get_gradebook_by_percent($earned_percentage);
							echo $this->generate_grade_html($grade);
							?>
						</td>


						<td title="<?php _e('Result', 'tutor-pro'); ?>">
							<?php
							if ($earned_percentage >= $passing_grade){
								echo '<span class="result-pass">'.__('Pass', 'tutor-pro').'</span>';
							}else{
								echo '<span class="result-fail">'.__('Fail', 'tutor-pro').'</span>';
							}
							?>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>

		<?php
		return ob_get_clean();
	}


	/**
	 * @param $grade
	 * @return mixed
	 *
	 * Generate Grade HTML
	 */

	public function generate_grade_html($grade){
		if ( ! is_object($grade)){
			global $wpdb;

			$grade = $wpdb->get_row("SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE gradebook_result_id = {$grade} ");
		}

		ob_start();

		if ($grade){
			$config = maybe_unserialize($grade->grade_config);
			$gradebook_enable_grade_point = get_tutor_option('gradebook_enable_grade_point');
			$gradebook_show_grade_scale = get_tutor_option('gradebook_show_grade_scale');
			$gradebook_scale_separator = get_tutor_option('gradebook_scale_separator');
			$gradebook_scale = get_tutor_option('gradebook_scale');
			?>
			<span class="gradename-bg" style="background-color: <?php echo tutils()->array_get('grade_color', $config); ?>;" >
				<?php echo $grade->grade_name; ?>
			</span>
			<?php
			if ($gradebook_enable_grade_point){
				echo "<span class='gradebook-earned-grade-point'>{$grade->grade_point}</span>";
			}
			if ($gradebook_show_grade_scale){
				echo "<span class='gradebook-scale-separator'>{$gradebook_scale_separator}</span><span class='gradebook_scale'>{$gradebook_scale}</span>";
			}
		}
		$output = apply_filters('tutor_gradebook_grade_output_html', ob_get_clean(), $grade);
		return $output;
	}

	public function course_single_actions_btn_group(){
		?>
		<form id="tutor-gradebook-generate-for-course" method="post">
			<?php tutor_nonce_field(); ?>
			<input type="hidden" name="tutor_action" value="gradebook_generate_for_course">
			<input type="hidden" name="course_ID" value="<?php echo get_the_ID(); ?>">

			<p>
				<button type="submit"> <i class="tutor-icon-spreadsheet"></i> <?php _e('Generate Gradebook', 'tutor-pro'); ?></button>
			</p>
		</form>
		<?php
	}

	public function gradebook_generate_for_course(){
		$user_id = get_current_user_id();
		$course_ID = (int) sanitize_text_field(tutils()->array_get('course_ID', $_POST));
		tutils()->checking_nonce();

		$course_contents = tutils()->get_course_contents_by_id($course_ID);
		$generated_grades_contents = $this->get_generated_gradebook('all', $course_ID, $user_id);

		if (tutils()->count($course_contents)){
			$require_gradding = array();
			foreach ($course_contents as $content){
				if ($content->post_type === 'tutor_quiz' || $content->post_type === 'tutor_assignments'){
					$require_gradding[] = $content;
				}
			}

			/**
			 * Getting assignments, quiz which not graded yet
			 */
			foreach ($require_gradding as $key => $content){
				foreach ($generated_grades_contents as $generated_grades_content){
					if ($content->ID == $generated_grades_content->quiz_id || $content->ID == $generated_grades_content->assignment_id){
						unset($require_gradding[$key]);
					}
				}
			}
			if (tutils()->count($require_gradding)){
				global $wpdb;

				$require_graddings = array_values($require_gradding);

				$earned_percentage = 0;
				$gradebook = $wpdb->get_row("SELECT * FROM {$wpdb->tutor_gradebooks} WHERE percent_from <= {$earned_percentage} AND percent_to >= {$earned_percentage} ORDER BY gradebook_id ASC LIMIT 1  ");
				if ( ! $gradebook){
					return;
				}

				foreach ($require_graddings as $course_item) {

					$gradebook_data = array(
						'user_id'            => $user_id,
						'course_id'          => $course_ID,
						'gradebook_id'       => $gradebook->gradebook_id,
						'grade_name'         => $gradebook->grade_name,
						'grade_point'        => $gradebook->grade_point,
						'earned_grade_point' => $gradebook->grade_point,
						'generate_date'      => date( "Y-m-d H:i:s" ),
						'update_date'        => date( "Y-m-d H:i:s" ),
					);

					$gradebook_result = false;

					if ($course_item->post_type === 'tutor_quiz'){
						$gradebook_data['quiz_id'] = $course_item->ID;
						$gradebook_data['result_for'] = 'quiz';

						$gradebook_result    = $wpdb->get_row( "SELECT * FROM {$wpdb->tutor_gradebooks_results} 
							WHERE result_for = 'quiz' 
							AND user_id = {$user_id} 
							AND course_id = {$course_ID} 
							AND quiz_id = {$course_item->ID} " );

					}elseif ($course_item->post_type === 'tutor_assignments'){
						$gradebook_data['assignment_id'] = $course_item->ID;
						$gradebook_data['result_for'] = 'assignment';

						$gradebook_result    = $wpdb->get_row( "SELECT * FROM {$wpdb->tutor_gradebooks_results} 
							WHERE result_for = 'assignment' 
							AND user_id = {$user_id} 
							AND course_id = {$course_ID} 
							AND assignment_id = {$course_item->ID} " );
					}

					if ( $gradebook_result ) {
						//Update Gradebook Result
						unset( $gradebook_data['generate_date'] );
						$wpdb->update( $wpdb->tutor_gradebooks_results, $gradebook_data, array( 'gradebook_result_id' => $gradebook_result->gradebook_result_id ) );
					} else {
						$wpdb->insert( $wpdb->tutor_gradebooks_results, $gradebook_data );
					}


				}

			}




		}



		echo '<pre>';
		//print_r($generated_grades_contents);
		die(print_r($require_gradding));
	}


	/**
	 * @param int $quiz_id
	 * @param int $user_id
	 *
	 * Get Grade percent from quiz base on settings...
	 */
	public function get_quiz_earned_number_percent($quiz_id = 0, $user_id = 0){
		$quiz_grade_method = get_tutor_option('quiz_grade_method');
		echo $quiz_grade_method;
	}

}