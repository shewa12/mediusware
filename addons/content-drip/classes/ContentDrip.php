<?php
/**
 * ContentDrip class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.4.1
 */

namespace TUTOR_CONTENT_DRIP;

if ( ! defined( 'ABSPATH' ) )
	exit;

class ContentDrip {

	private $unlock_timestamp = false;
	private $unlock_message = null;

	public function __construct() {
		add_filter('tutor_course_settings_tabs', array($this, 'settings_attr') );

		add_action('tutor_lesson_edit_modal_form_after', array($this, 'content_drip_lesson_metabox'), 10, 0);

		add_action('tutor/lesson_update/after', array($this, 'lesson_updated'));
		add_action('tutor/lesson_list/right_icon_area', array($this, 'show_content_drip_icon'));

		add_filter('tutor_lesson/single/content', array($this, 'drip_lesson_content'));
	}

	public function settings_attr($args){
		$args['contentdrip'] = array(
			'label' => __('Content Drip', 'tutor-pro'),
			'desc' => __('Tutor Content Drip allow you to schedule publish topics / lesson', 'tutor-pro'),
			'icon_class' => 'dashicons dashicons-clock',
			'callback'  => '',
			'fields'    => array(
				'enable_content_drip' => array(
					'type'      => 'checkbox',
					'label'     => '',
					'label_title' => __('Enable', 'tutor-pro'),
					'default' => '0',
					'desc'      => __('Enable / Disable content drip', 'tutor-pro'),
				),
				'content_drip_type' => array(
					'type'      => 'radio',
					'label'     => 'Content Drip Type',
					'default' => 'unlock_by_date',
					'options'   => array(
						'unlock_by_date'                =>  __('Schedule course contents by date', 'tutor-pro'),
						'specific_days'                 =>  __('Content available after X days from enrollment', 'tutor-pro'),
						'unlock_sequentially'           =>  __('Course content available sequentially', 'tutor-pro'),
						'after_finishing_prerequisites'    =>  __('Course content unlocked after finishing prerequisites', 'tutor-pro'),
					),
					'desc'      => __('You can schedule your course content using the above content drip options.', 'tutor-pro'),
				),
			),
		);
		return $args;
	}


	public function content_drip_lesson_metabox(){
		include  TUTOR_CONTENT_DRIP()->path.'views/content-drip-lesson.php';
	}

	public function lesson_updated($lesson_id){
		$content_drip_settings = tutils()->array_get('content_drip_settings', $_POST);
		if (tutils()->count($content_drip_settings)){
			update_post_meta($lesson_id, '_content_drip_settings', $content_drip_settings);
		}
	}

	/**
	 * @param $post
	 *
	 * Show lock icon based on condition
	 */
	public function show_content_drip_icon($post){
		$is_lock = $this->is_lock_lesson($post);

		if ($is_lock){
			echo '<i class="tutor-icon-lock"></i>';
		}
	}

	public function is_lock_lesson($post = null){
		$post = get_post($post);
		$lesson_id = $post->ID;

		$lesson_post_type = tutor()->lesson_post_type;
		if ($lesson_post_type === $post->post_type){
			$course_id = tutils()->get_course_id_by_lesson($lesson_id);
			$enable = (bool) get_tutor_course_settings($course_id, 'enable_content_drip');
			if ( ! $enable){
				return false;
			}

			$drip_type = get_tutor_course_settings($course_id, 'content_drip_type');
			if ($drip_type === 'unlock_by_date'){
				$unlock_timestamp = strtotime(get_lesson_content_drip_settings($lesson_id, 'unlock_date'));
				if ($unlock_timestamp){

					$unlock_date = date_i18n(get_option('date_format'), $unlock_timestamp);
					$this->unlock_message = sprintf(__("This lesson will be available from %s", 'tutor-pro'), $unlock_date);

					return $unlock_timestamp > current_time('timestamp');
				}
			}elseif ($drip_type === 'specific_days'){
				$days = (int) get_lesson_content_drip_settings($lesson_id, 'after_xdays_of_enroll');

				if ($days > 0){
					$enroll = tutils()->is_course_enrolled_by_lesson($lesson_id);
					$enroll_date = tutils()->array_get('post_date', $enroll);
					$enroll_date = date('Y-m-d', strtotime($enroll_date));
					$days_in_time = 60*60*24*$days;

					$unlock_timestamp = $enroll_date + $days_in_time;


					return $unlock_timestamp > current_time('timestamp');
				}

			}

		}

		return false;
	}

	public function drip_lesson_content($html){
		if ($this->is_lock_lesson(get_the_ID())){
			$output = apply_filters('tutor/content_drip/unlock_message', "<p class='tutor-error-msg'> {$this->unlock_message}</p>");
			return "<div class='tutor-lesson-content-drip-wrap'> {$output} </div>";
		}

		return $html;
	}

	public function is_valid_date($date_string = null){
		return (bool) strtotime($date_string);
	}

}