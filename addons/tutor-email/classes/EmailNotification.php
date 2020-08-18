<?php

/**
 * Class Email Notification
 * @package TUTOR
 *
 * @since v.1.0.0
 */

namespace TUTOR_EMAIL;

if (!defined('ABSPATH'))
	exit;

class EmailNotification {

	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu'));

		add_action('tutor_quiz/attempt_ended', array($this, 'quiz_finished_send_email_to_student'), 10, 1);
		add_action('tutor_finish_quiz_attempt', array($this, 'quiz_finished_send_email_to_student'), 10, 1);

		add_action('tutor_quiz/attempt_ended', array($this, 'quiz_finished_send_email_to_instructor'), 10, 1);
		add_action('tutor_finish_quiz_attempt', array($this, 'quiz_finished_send_email_to_instructor'), 10, 1);

		add_action('tutor_course_complete_after', array($this, 'course_complete_email_to_student'), 10, 1);
		add_action('tutor_course_complete_after', array($this, 'course_complete_email_to_teacher'), 10, 1);
		add_action('tutor/course/enrol_status_change/after', array($this, 'course_enroll_email'), 10, 2);
		add_action('tutor_after_add_question', array($this, 'tutor_after_add_question'), 10, 2);
		add_action('tutor_lesson_completed_after', array($this, 'tutor_lesson_completed_after'), 10, 1);

		/**
		 * @since 1.6.9
		 */
		add_action('tutor_add_new_instructor_after', array($this, 'tutor_new_instructor_signup'), 10, 2);
		add_action('tutor_after_student_signup', array($this, 'tutor_new_student_signup'), 10, 2);
		add_action('pending_' . tutor()->course_post_type, array($this, 'tutor_course_pending'), 10, 3);
		add_action('publish_' . tutor()->course_post_type, array($this, 'tutor_course_published'), 10, 3);
		add_action('save_post_' . tutor()->course_post_type, array($this, 'tutor_course_updated'), 10, 3);
		add_action('tutor_assignment/after/submitted', array($this, 'tutor_assignment_after_submitted'), 10, 3);
		add_action('tutor_assignment/evaluate/after', array($this, 'tutor_after_assignment_evaluate'), 10, 3);
		add_action('tutor_enrollment/after/delete', array($this, 'tutor_student_remove_from_course'), 10, 3);
		add_action('tutor_enrollment/after/cancel', array($this, 'tutor_student_remove_from_course'), 10, 3);
		add_action('tutor_enrollment/after/complete', array($this, 'tutor_after_manual_enrollment'), 10, 3);
		add_action('tutor_announcements/after/save', array($this, 'tutor_announcements_after_save'), 10, 3);
		add_action('tutor_after_answer_to_question', array($this, 'tutor_after_answer_to_question'), 10, 3);
		add_action('tutor_quiz/attempt/submitted/feedback', array($this, 'feedback_submitted_for_quiz_attempt'), 10, 3);
		add_action('tutor_course_complete_after', array($this, 'tutor_course_complete_after'), 10, 3);
	}

	public function register_menu() {
		add_submenu_page('tutor', __('E-Mails', 'tutor-pro'), __('E-Mails', 'tutor-pro'), 'manage_tutor', 'tutor_emails', array($this, 'tutor_emails'));
	}

	public function tutor_emails() {
		include TUTOR_EMAIL()->path . 'views/pages/tutor_emails.php';
	}

	/**
	 * @param $to
	 * @param $subject
	 * @param $message
	 * @param $headers
	 * @param array $attachments
	 *
	 * @return bool
	 *
	 *
	 * Send E-Mail Notification for Tutor Event
	 */

	public function send($to, $subject, $message, $headers, $attachments = array()) {
		add_filter('wp_mail_from', array($this, 'get_from_address'));
		add_filter('wp_mail_from_name', array($this, 'get_from_name'));
		add_filter('wp_mail_content_type', array($this, 'get_content_type'));

		$message = apply_filters('tutor_mail_content', $message);
		$return  = wp_mail($to, $subject, $message, $headers, $attachments);

		remove_filter('wp_mail_from', array($this, 'get_from_address'));
		remove_filter('wp_mail_from_name', array($this, 'get_from_name'));
		remove_filter('wp_mail_content_type', array($this, 'get_content_type'));

		return $return;
	}

	/**
	 * Get the from name for outgoing emails from tutor
	 *
	 * @return string
	 */
	public function get_from_name() {
		$email_from_name = tutor_utils()->get_option('email_from_name');
		$from_name = apply_filters('tutor_email_from_name', $email_from_name);
		return wp_specialchars_decode(esc_html($from_name), ENT_QUOTES);
	}

	/**
	 * Get the from name for outgoing emails from tutor
	 *
	 * @return string
	 */
	public function get_from_address() {
		$email_from_address = tutor_utils()->get_option('email_from_address');
		$from_address = apply_filters('tutor_email_from_address', $email_from_address);
		return sanitize_email($from_address);
	}

	/**
	 * @return string
	 *
	 * Get content type
	 */
	public function get_content_type() {
		return apply_filters('tutor_email_content_type', 'text/html');
	}


	public function get_message($message = '', $search = array(), $replace = array()) {

		$email_footer_text = tutor_utils()->get_option('email_footer_text');

		$message = str_replace($search, $replace, $message);
		if ($email_footer_text) {
			$message .= $email_footer_text;
		}

		return $message;
	}


	/**
	 * @param $course_id
	 * 
	 * Send course completion E-Mail to Student
	 */
	public function course_complete_email_to_student($course_id) {
		$course_completed_to_student = tutor_utils()->get_option('email_to_students.completed_course');

		if (!$course_completed_to_student) {
			return;
		}

		$user_id = get_current_user_id();

		$course = get_post($course_id);
		$student = get_userdata($user_id);

		$completion_time = tutor_utils()->is_completed_course($course_id);
		$completion_time = $completion_time ? $completion_time : tutor_time();

		$completion_time_format = date_i18n(get_option('date_format'), $completion_time) . ' ' . date_i18n(get_option('time_format'), $completion_time);

		$file_tpl_variable = array(
			'{student_username}',
			'{course_name}',
			'{completion_time}',
			'{course_url}',
		);

		$replace_data = array(
			$student->display_name,
			$course->post_title,
			$completion_time_format,
			get_the_permalink($course_id),
		);

		$subject = __('You just completed ' . $course->post_title, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_student_course_completed');
		$email_tpl = apply_filters('tutor_email_tpl/course_completed', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_course_completed_email_header', $header, $course_id);

		$this->send($student->user_email, $subject, $message, $header);
	}


	public function course_complete_email_to_teacher($course_id) {
		$course_completed_to_teacher = tutor_utils()->get_option('email_to_teachers.a_student_completed_course');

		if (!$course_completed_to_teacher) {
			return;
		}

		$user_id = get_current_user_id();
		$student = get_userdata($user_id);

		$course = get_post($course_id);
		$teacher = get_userdata($course->post_author);

		$completion_time = tutor_utils()->is_completed_course($course_id);
		$completion_time = $completion_time ? $completion_time : tutor_time();

		$completion_time_format = date_i18n(get_option('date_format'), $completion_time) . ' ' . date_i18n(get_option('time_format'), $completion_time);


		$file_tpl_variable = array(
			'{instructor_username}',
			'{student_username}',
			'{course_name}',
			'{completion_time}',
			'{course_url}',
		);

		$replace_data = array(
			$teacher->display_name,
			$student->display_name,
			$course->post_title,
			$completion_time_format,
			get_the_permalink($course_id),
		);

		$subject = __($student->display_name . ' just completed ' . $course->post_title, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_instructor_course_completed');
		$email_tpl = apply_filters('tutor_email_tpl/course_completed', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_course_completed_email_header', $header, $course_id);

		$this->send($teacher->user_email, $subject, $message, $header);
	}


	/**
	 * Send the quiz to Student
	 *
	 * @param $attempt_id
	 */

	public function quiz_finished_send_email_to_student($attempt_id) {
		$quiz_completed = tutor_utils()->get_option('email_to_students.quiz_completed');
		if (!$quiz_completed) {
			return;
		}

		$attempt = tutor_utils()->get_attempt($attempt_id);
		$attempt_info = tutor_utils()->quiz_attempt_info($attempt_id);

		$submission_time = tutor_utils()->avalue_dot('submission_time', $attempt_info);
		$submission_time = $submission_time ? $submission_time : tutor_time();

		$quiz_id = tutor_utils()->avalue_dot('comment_post_ID', $attempt);
		$quiz_name = get_the_title($quiz_id);
		$course = tutor_utils()->get_course_by_quiz($quiz_id);
		$course_id = tutor_utils()->avalue_dot('ID', $course);
		$course_title = get_the_title($course_id);
		$submission_time_format = date_i18n(get_option('date_format'), $submission_time) . ' ' . date_i18n(get_option('time_format'), $submission_time);

		$quiz_url = get_the_permalink($quiz_id);
		$user = get_userdata(tutor_utils()->avalue_dot('user_id', $attempt));

		ob_start();
		tutor_load_template('email.to_student_quiz_completed');
		$email_tpl = apply_filters('tutor_email_tpl/quiz_completed', ob_get_clean());

		$file_tpl_variable = array(
			'{username}',
			'{quiz_name}',
			'{course_name}',
			'{submission_time}',
			'{quiz_url}',
		);

		$replace_data = array(
			$user->display_name,
			$quiz_name,
			$course_title,
			$submission_time_format,
			"<a href='{$quiz_url}'>{$quiz_url}</a>",
		);

		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$subject = apply_filters('student_quiz_completed_email_subject', sprintf(__("Thank you for %s  answers, we have received", "tutor"), $quiz_name));
		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_quiz_completed_email_header', $header, $attempt_id);

		$this->send($user->user_email, $subject, $message, $header);
	}

	public function quiz_finished_send_email_to_instructor($attempt_id) {
		$isEnable = tutor_utils()->get_option('email_to_teachers.student_submitted_quiz');
		if (!$isEnable) {
			return;
		}

		$attempt = tutor_utils()->get_attempt($attempt_id);
		$attempt_info = tutor_utils()->quiz_attempt_info($attempt_id);

		$submission_time = tutor_utils()->avalue_dot('submission_time', $attempt_info);
		$submission_time = $submission_time ? $submission_time : tutor_time();

		$quiz_id = tutor_utils()->avalue_dot('comment_post_ID', $attempt);
		$quiz_name = get_the_title($quiz_id);
		$course = tutor_utils()->get_course_by_quiz($quiz_id);
		$course_id = tutor_utils()->avalue_dot('ID', $course);
		$course_title = get_the_title($course_id);
		$submission_time_format = date_i18n(get_option('date_format'), $submission_time) . ' ' . date_i18n(get_option('time_format'), $submission_time);


		$attempt_url = tutor_utils()->get_tutor_dashboard_page_permalink('quiz-attempts/quiz-reviews/?attempt_id=' . $attempt_id);

		$user = get_userdata(tutor_utils()->avalue_dot('user_id', $attempt));

		$teacher = get_userdata($course->post_author);

		ob_start();
		tutor_load_template('email.to_instructor_quiz_completed');
		$email_tpl = apply_filters('tutor_email_tpl/quiz_completed/to_instructor', ob_get_clean());

		$file_tpl_variable = array(
			'{instructor_username}',
			'{username}',
			'{quiz_name}',
			'{course_name}',
			'{submission_time}',
			'{quiz_review_url}',
		);

		$replace_data = array(
			$teacher->display_name,
			$user->display_name,
			$quiz_name,
			$course_title,
			$submission_time_format,
			"<a href='{$attempt_url}'>{$attempt_url}</a>",
		);

		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$subject = apply_filters('student_quiz_completed_to_instructor_email_subject', sprintf(__("Submitted %s  answers, Review it", "tutor"), $quiz_name));
		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_quiz_completed_to_instructor_email_header', $header, $attempt_id);

		$this->send($user->user_email, $subject, $message, $header);
	}

	/**
	 * @param $enrol_id
	 * @param $status_to
	 *
	 * E-Mail to teacher when success enrol.
	 */
	public function course_enroll_email($enrol_id, $status_to) {
		$enroll_notification = tutor_utils()->get_option('email_to_teachers.a_student_enrolled_in_course');

		if (!$enroll_notification || $status_to !== 'completed') {
			return;
		}

		$user_id = get_current_user_id();
		$student = get_userdata($user_id);

		$course = tutils()->get_course_by_enrol_id($enrol_id);
		$teacher = get_userdata($course->post_author);

		$enroll_time = tutor_time();
		$enroll_time_format = date_i18n(get_option('date_format'), $enroll_time) . ' ' . date_i18n(get_option('time_format'), $enroll_time);

		$file_tpl_variable = array(
			'{instructor_username}',
			'{student_username}',
			'{course_name}',
			'{enroll_time}',
			'{course_url}',
		);

		$replace_data = array(
			$teacher->display_name,
			$student->display_name,
			$course->post_title,
			$enroll_time_format,
			get_the_permalink($course->ID),
		);

		$subject = __($student->display_name . ' enrolled ' . $course->post_title, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_instructor_course_enrolled');
		$email_tpl = apply_filters('tutor_email_tpl/to_teacher_course_enrolled', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_course_completed_email_header', $header, $course->ID);

		$this->send($teacher->user_email, $subject, $message, $header);
	}


	public function tutor_after_add_question($course_id, $comment_id) {
		$enroll_notification = tutor_utils()->get_option('email_to_teachers.a_student_placed_question');
		if (!$enroll_notification) {
			return;
		}

		$user_id = get_current_user_id();
		$student = get_userdata($user_id);

		$course = get_post($course_id);
		$teacher = get_userdata($course->post_author);

		$get_comment = tutor_utils()->get_qa_question($comment_id);
		$question = $get_comment->comment_content;
		$question_title = $get_comment->question_title;

		$enroll_time = tutor_time();
		$enroll_time_format = date_i18n(get_option('date_format'), $enroll_time) . ' ' . date_i18n(get_option('time_format'), $enroll_time);

		$file_tpl_variable = array(
			'{instructor_username}',
			'{student_username}',
			'{course_name}',
			'{course_url}',
			'{enroll_time}',
			'{question_title}',
			'{question}',
		);

		$replace_data = array(
			$teacher->display_name,
			$student->display_name,
			$course->post_title,
			get_the_permalink($course_id),
			$enroll_time_format,
			$question_title,
			wpautop(stripslashes($question)),
		);

		$subject = __(sprintf('%s asked a question on %s', $student->display_name, $course->post_title), 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_instructor_asked_question_by_student');
		$email_tpl = apply_filters('tutor_email_tpl/to_teacher_asked_question_by_student', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('to_teacher_asked_question_by_student_email_header', $header, $course_id);

		$this->send($teacher->user_email, $subject, $message, $header);
	}


	public function tutor_lesson_completed_after($lesson_id) {
		$course_completed_to_teacher = tutor_utils()->get_option('email_to_teachers.a_student_completed_lesson');

		if (!$course_completed_to_teacher) {
			return;
		}

		$user_id = get_current_user_id();
		$student = get_userdata($user_id);

		$course_id = tutor_utils()->get_course_id_by_lesson($lesson_id);

		$lesson = get_post($lesson_id);
		$course = get_post($course_id);
		$teacher = get_userdata($course->post_author);

		$completion_time =  tutor_time();
		$completion_time_format = date_i18n(get_option('date_format'), $completion_time) . ' ' . date_i18n(get_option('time_format'), $completion_time);

		$file_tpl_variable = array(
			'{instructor_username}',
			'{student_username}',
			'{course_name}',
			'{lesson_name}',
			'{completion_time}',
			'{lesson_url}',
		);

		$replace_data = array(
			$teacher->display_name,
			$student->display_name,
			$course->post_title,
			$lesson->post_title,
			$completion_time_format,
			get_the_permalink($lesson_id),
		);

		$subject = __($student->display_name . ' just completed lesson ' . $course->post_title, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_instructor_lesson_completed');
		$email_tpl = apply_filters('tutor_email_tpl/lesson_completed', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_lesson_completed_email_header', $header, $lesson_id);

		$this->send($teacher->user_email, $subject, $message, $header);
	}

	/**
	 * After instructor successfully signup
	 *
	 * @since 1.6.9
	 */
	public function tutor_new_instructor_signup($user_id) {
		$new_instructor_signup = tutor_utils()->get_option('email_to_admin.new_instructor_signup');

		if (!$new_instructor_signup) {
			return;
		}

		$instructor_id = tutils()->get_user_id($user_id);
		$instructor = get_userdata($instructor_id);
		
		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$signup_time =  tutor_time();
		$signup_time_format = date_i18n(get_option('date_format'), $signup_time) . ' ' . date_i18n(get_option('time_format'), $signup_time);

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{instructor_name}',
			'{instructor_email}',
			'{signup_time}'
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$instructor->display_name,
			$instructor->user_email,
			$signup_time_format,
		);

		$admin_email = get_option('admin_email');
		$subject = __('New instructor signed up - '.$site_name, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_admin_new_instructor_signup');
		$email_tpl = apply_filters('tutor_email_tpl/new_instructor_signup', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('instructor_signup_email_header', $header, $instructor_id);

		$this->send($admin_email, $subject, $message, $header);
	}

	/**
	 * After student successfully signup
	 *
	 * @since 1.6.9
	 */
	public function tutor_new_student_signup($user_id) {
		$new_student_signup = tutor_utils()->get_option('email_to_admin.new_student_signup');

		if (!$new_student_signup) {
			return;
		}

		$student_id = tutils()->get_user_id($user_id);
		$student = get_userdata($student_id);

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$signup_time =  tutor_time();
		$signup_time_format = date_i18n(get_option('date_format'), $signup_time) . ' ' . date_i18n(get_option('time_format'), $signup_time);

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{student_name}',
			'{student_email}',
			'{signup_time}'
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$student->display_name,
			$student->user_email,
			$signup_time_format,
		);

		$admin_email = get_option('admin_email');
		$subject = __('New student signed up - '.$site_name, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_admin_new_student_signup');
		$email_tpl = apply_filters('tutor_email_tpl/new_student_signup', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_signup_email_header', $header, $student_id);

		$this->send($admin_email, $subject, $message, $header);
	}

	/**
	 * After new course submit for review
	 *
	 * @since 1.6.9
	 */
	public function tutor_course_pending($course_id, $course) {
		$new_course_submitted = tutor_utils()->get_option('email_to_admin.new_course_submitted');

		if (!$new_course_submitted) {
			return;
		}

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$submitted_time =  tutor_time();
		$submitted_time_format = date_i18n(get_option('date_format'), $submitted_time) . ' ' . date_i18n(get_option('time_format'), $submitted_time);
		$instructor_name = get_the_author_meta('display_name', $course->post_author);

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{course_name}',
			'{course_url}',
			'{instructor_name}',
			'{submitted_time}'
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$course->post_title,
			get_the_permalink($course_id),
			$instructor_name,
			$submitted_time_format,
		);

		$admin_email = get_option('admin_email');
		$subject = __('New Course Submitted for Review - '.$course->post_title, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_admin_new_course_submitted_for_review');
		$email_tpl = apply_filters('tutor_email_tpl/new_course_submitted', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('new_course_submitted_email_header', $header, $course_id);

		$this->send($admin_email, $subject, $message, $header);
	}

	/**
	 * After new course published
	 *
	 * @since 1.6.9
	 */
	public function tutor_course_published($course_id, $course) {
		$new_course_published = tutor_utils()->get_option('email_to_admin.new_course_published');

		if (!$new_course_published) {
			return;
		}

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$published_time =  tutor_time();
		$published_time_format = date_i18n(get_option('date_format'), $published_time) . ' ' . date_i18n(get_option('time_format'), $published_time);
		$instructor_name = get_the_author_meta('display_name', $course->post_author);

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{course_name}',
			'{course_url}',
			'{instructor_name}',
			'{published_time}'
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$course->post_title,
			get_the_permalink($course_id),
			$instructor_name,
			$published_time_format,
		);

		$admin_email = get_option('admin_email');
		$subject = __('New Course Published - '.$course->post_title, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_admin_new_course_published');
		$email_tpl = apply_filters('tutor_email_tpl/new_course_published', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('new_course_published_email_header', $header, $course_id);

		$this->send($admin_email, $subject, $message, $header);
	}

	/**
	 * After course updated/edited
	 *
	 * @since 1.6.9
	 */
	public function tutor_course_updated($course_id, $course, $update=false) {
		$course_updated = tutor_utils()->get_option('email_to_admin.course_updated');

		if (!$course_updated || !$update) {
			return;
		}

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$updated_time =  tutor_time();
		$updated_time_format = date_i18n(get_option('date_format'), $updated_time) . ' ' . date_i18n(get_option('time_format'), $updated_time);
		$instructor_name = get_the_author_meta('display_name', $course->post_author);

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{course_name}',
			'{course_url}',
			'{instructor_name}',
			'{updated_time}'
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$course->post_title,
			get_the_permalink($course_id),
			$instructor_name,
			$updated_time_format,
		);

		$admin_email = get_option('admin_email');
		$subject = __('A Course has been edited on '.$course->post_title, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_admin_course_updated');
		$email_tpl = apply_filters('tutor_email_tpl/course_updated', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('course_updated_email_header', $header, $course_id);

		$this->send($admin_email, $subject, $message, $header);
	}

	/**
	 * After assignment submitted
	 *
	 * @since 1.6.9
	 */
	public function tutor_assignment_after_submitted($assignment_submit_id) {
		$student_submitted_assignment = tutor_utils()->get_option('email_to_teachers.student_submitted_assignment');

		if (!$student_submitted_assignment) {
			return;
		}

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$submitted_assignment = tutils()->get_assignment_submit_info($assignment_submit_id);
		$student_name = get_the_author_meta('display_name', $submitted_assignment->user_id);
		$course_name = get_the_title($submitted_assignment->comment_parent);
		$course_url = get_the_permalink($submitted_assignment->comment_parent);
		$assignment_name = get_the_title($submitted_assignment->comment_post_ID);
		$submitted_url = tutils()->get_tutor_dashboard_page_permalink('assignments/submitted');
		$review_link = esc_url($submitted_url.'?assignment='.$submitted_assignment->comment_post_ID);

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{student_name}',
			'{course_name}',
			'{course_url}',
			'{assignment_name}',
			'{review_link}'
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$student_name,
			$course_name,
			$course_url,
			$assignment_name,
			$review_link,
		);

		$admin_email = get_option('admin_email');
		$subject = __('New Assignment Submission on course - '.$course_name.' at '.$site_name, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_instructor_student_submitted_assignment');
		$email_tpl = apply_filters('tutor_email_tpl/student_submitted_assignment', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('student_submitted_assignment_email_header', $header, $assignment_submit_id);

		$this->send($admin_email, $subject, $message, $header);
	}

	/**
	 * After assignment evaluate
	 *
	 * @since 1.6.9
	 */
	public function tutor_after_assignment_evaluate($assignment_submit_id) {
		$assignment_graded = tutor_utils()->get_option('email_to_students.assignment_graded');

		if (!$assignment_graded) {
			return;
		}

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$submitted_assignment = tutils()->get_assignment_submit_info($assignment_submit_id);
		$student_email = get_the_author_meta('user_email', $submitted_assignment->user_id);
		$course_name = get_the_title($submitted_assignment->comment_parent);
		$course_url = get_the_permalink($submitted_assignment->comment_parent);
		$assignment_name = get_the_title($submitted_assignment->comment_post_ID);
		$assignemnt_score = get_comment_meta( $assignment_submit_id, 'assignment_mark', true );
		$assignment_comment = get_comment_meta( $assignment_submit_id, 'instructor_note', true );

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{course_name}',
			'{course_url}',
			'{assignment_name}',
			'{assignemnt_score}',
			'{assignment_comment}'
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$course_name,
			$course_url,
			$assignment_name,
			$assignemnt_score,
			$assignment_comment
		);

		$subject = __('Grade submitted for Assignment - '.$assignment_name.' - '.$course_name, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_student_assignment_evaluate');
		$email_tpl = apply_filters('tutor_email_tpl/assignment_evaluate', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('assignment_evaluate_email_header', $header, $assignment_submit_id);

		$this->send($student_email, $subject, $message, $header);
	}

	/**
	 * After remove student from course
	 *
	 * @since 1.6.9
	 */
	public function tutor_student_remove_from_course($enrol_id) {
		$remove_from_course = tutor_utils()->get_option('email_to_students.remove_from_course');

		if (!$remove_from_course) {
			return;
		}

		$enrolment = tutils()->get_enrolment_by_enrol_id($enrol_id);
		if (!$enrolment) {
			return;
		}

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$course_name = $enrolment->course_title;
		$course_url = get_the_permalink($enrolment->course_id);
		$student_email = $enrolment->user_email;;

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{course_name}',
			'{course_url}'
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$course_name,
			$course_url
		);

		$subject = __('You have been removed from the course - '.$course_name, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_student_remove_from_course');
		$email_tpl = apply_filters('tutor_email_tpl/remove_from_course', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('remove_from_course_email_header', $header, $enrol_id);

		$this->send($student_email, $subject, $message, $header);
	}


	/**
	 * After manual enrollment
	 *
	 * @since 1.6.9
	 */
	public function tutor_after_manual_enrollment($enrol_id) {
		$manual_enrollment = tutor_utils()->get_option('email_to_students.manual_enrollment');

		if (!$manual_enrollment) {
			return;
		}

		$enrolment = tutils()->get_enrolment_by_enrol_id($enrol_id);
		if (!$enrolment) {
			return;

		}
		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$course_name = $enrolment->course_title;
		$course_url = get_the_permalink($enrolment->course_id);
		$course_start_url = tutils()->get_course_first_lesson($enrolment->course_id);
		$student_email = $enrolment->user_email;

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{course_name}',
			'{course_url}',
			'{course_start_url}'
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$course_name,
			$course_url,
			$course_start_url
		);

		$subject = __('You are enrolled in '.$course_name.' at '.$site_url, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_student_manual_enrollment');
		$email_tpl = apply_filters('tutor_email_tpl/manual_enrollment', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('manual_enrollment_email_header', $header, $enrol_id);

		$this->send($student_email, $subject, $message, $header);
	}

	/**
	 * After save new announcement
	 *
	 * @since 1.6.9
	 */
	public function tutor_announcements_after_save($announcement_id, $announcement) {
		$new_announcement_posted = tutor_utils()->get_option('email_to_students.new_announcement_posted');

		if (!$new_announcement_posted) {
			return;
		}

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$course_name = get_the_title($announcement->post_parent);
		$course_url = get_the_permalink($announcement->post_parent);
		$announcement_content = $announcement->post_content;
		$student_emails = tutils()->get_student_emails_by_course_id($announcement->post_parent);

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{course_name}',
			'{course_url}',
			'{announcement}',
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$course_name,
			$course_url,
			$announcement_content,
		);

		$subject = __('New Announcement on course - '.$course_name, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_student_new_announcement_posted');
		$email_tpl = apply_filters('tutor_email_tpl/new_announcement_posted', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('new_announcement_posted_email_header', $header, $announcement_id);

		$this->send($student_emails, $subject, $message, $header);
	}

	/**
	 * After question has been answered
	 *
	 * @since 1.6.9
	 */
	public function tutor_after_answer_to_question($answer_id) {
		$after_question_answered = tutor_utils()->get_option('email_to_students.after_question_answered');

		if (!$after_question_answered) {
			return;
		}

		$answer = tutils()->get_qa_answer_by_answer_id($answer_id);

		$course_name = get_the_title($answer->comment_post_ID);
		$course_url = get_the_permalink($answer->comment_post_ID);
		$question_by = get_the_author_meta('user_email', $answer->question_by);

		$file_tpl_variable = array(
			'{answer}',
			'{answer_by}',
			'{question}',
			'{question_title}',
			'{course_name}',
			'{course_url}',
		);

		$replace_data = array(
			$answer->comment_content,
			$answer->display_name,
			$answer->question,
			$answer->question_title,
			$course_name,
			$course_url,
		);

		$subject = __('Instructor has answered to your question on - '.$course_name, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_student_question_answered');
		$email_tpl = apply_filters('tutor_email_tpl/question_answered', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('question_answered_email_header', $header, $answer_id);

		$this->send($question_by, $subject, $message, $header);
	}

	/**
	 * After quiz attempts feedback
	 *
	 * @since 1.6.9
	 */
	public function feedback_submitted_for_quiz_attempt($attempt_id) {
		$feedback_submitted_for_quiz = tutor_utils()->get_option('email_to_students.feedback_submitted_for_quiz');

		if (!$feedback_submitted_for_quiz) {
			return;
		}

		$attempt = tutor_utils()->get_attempt($attempt_id);
		$quiz = tutor_utils()->get_attempt($attempt->quiz_id);
		$course = get_post($attempt->course_id);
		$instructor_name = get_the_author_meta('display_name', $course->post_author);
		$instructor_feedback = get_post_meta($attempt_id, 'instructor_feedback', true);

		$user_email = get_the_author_meta('user_email', $attempt->user_id);

		$file_tpl_variable = array(
			'{quiz_name}',
			'{total_marks}',
			'{earned_marks}',
			'{course_name}',
			'{instructor_name}',
			'{instructor_feedback}'
		);

		$replace_data = array(
			$quiz->post_title,
			$attempt->total_marks,
			$attempt->earned_marks,
			$course->post_title,
			$instructor_name,
			$instructor_feedback
		);

		$subject = __('Feedback submitted for '.$quiz->post_title, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_student_feedback_submitted_for_quiz');
		$email_tpl = apply_filters('tutor_email_tpl/feedback_submitted_for_quiz', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('feedback_submitted_for_quiz_email_header', $header, $attempt_id);

		$this->send($user_email, $subject, $message, $header);
	}

	/**
	 * After course completed
	 *
	 * @since 1.6.9
	 */
	public function tutor_course_complete_after($course_id) {
		$rate_course_and_instructor = tutor_utils()->get_option('email_to_students.rate_course_and_instructor');

		if (!$rate_course_and_instructor) {
			return;
		}

		$site_url = get_bloginfo( 'url' );
		$site_name = get_bloginfo( 'name' );
		$course = get_post($course_id);
		$course_url = get_the_permalink($course_id);
		$instructor_url = tutils()->profile_url($course->post_author);

		$user_id = get_current_user_id();
		$user_email = get_the_author_meta('user_email', $user_id);

		$file_tpl_variable = array(
			'{site_url}',
			'{site_name}',
			'{course_name}',
			'{course_url}',
			'{instructor_url}',
		);

		$replace_data = array(
			$site_url,
			$site_name,
			$course->post_title,
			$course_url,
			$instructor_url,
		);

		$subject = __('Congratulations on Finishing the Course '.$course->post_title, 'tutor-pro');

		ob_start();
		tutor_load_template('email.to_student_rate_course_and_instructor');
		$email_tpl = apply_filters('tutor_email_tpl/rate_course_and_instructor', ob_get_clean());
		$message = $this->get_message($email_tpl, $file_tpl_variable, $replace_data);

		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";
		$header = apply_filters('rate_course_and_instructor_email_header', $header, $course_id);

		$this->send($user_email, $subject, $message, $header);
	}
}
