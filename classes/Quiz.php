<?php


namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) )
	exit;


class Quiz {

	public function __construct() {
		add_action('tutor_quiz/previous_attempts/table/thead/col', array($this, 'add_attempts_thead_col'));
		add_action('tutor_quiz/previous_attempts/table/tbody/col', array($this, 'add_attempts_tbody_col'));
		add_filter('tutor_single_quiz/top', array($this, 'view_quiz_attempt'));
		add_filter('tutor_single_quiz/body', array($this, 'remove_quiz_body_if_attempt_view'));
	}

	public function add_attempts_thead_col(){
		echo '<th>#</th>';
	}
	public function add_attempts_tbody_col($attempt){
		echo '<td>';
		echo '<a href="'.add_query_arg(array('view_quiz_attempt_id' => $attempt->attempt_id)).'">'.__('View Attempt', 'tutor-pro').'</a>';
		echo '</td>';
	}

	public function view_quiz_attempt($html){
		$attempt_id = (int) sanitize_text_field(tutils()->array_get('view_quiz_attempt_id', $_GET));
		if ($attempt_id){
			$user_id = get_current_user_id();
			$attempt = tutils()->get_attempt($attempt_id);

			if (tutils()->array_get('user_id', $attempt) != $user_id){
				return $html;
			}
			return tutor_get_template_html('single.quiz.view_attempt', compact('attempt_id'), true);
		}

		return $html;
	}

	public function remove_quiz_body_if_attempt_view($html){
		$attempt_id = (int) sanitize_text_field(tutils()->array_get('view_quiz_attempt_id', $_GET));

		if ($attempt_id){
			$user_id = get_current_user_id();
			$attempt = tutils()->get_attempt($attempt_id);

			if (tutils()->array_get('user_id', $attempt) == $user_id){
				return '';
			}

		}

		return $html;
	}


}