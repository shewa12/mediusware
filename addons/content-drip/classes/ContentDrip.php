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
	private $drip_type = null;

	public function __construct() {
		add_filter('tutor_course_settings_tabs', array($this, 'settings_attr') );

		add_action('tutor_lesson_edit_modal_form_after', array($this, 'content_drip_lesson_metabox'), 10, 0);
		add_action('tutor_quiz_edit_modal_settings_tab_after', array($this, 'content_drip_lesson_metabox'), 10, 0);
		add_action('tutor_assignment_edit_modal_form_after', array($this, 'content_drip_lesson_metabox'), 10, 0);

		add_action('tutor/lesson_update/after', array($this, 'lesson_updated'));
		add_action('tutor_quiz_settings_updated', array($this, 'lesson_updated'));
		add_action('tutor_assignment_updated', array($this, 'lesson_updated'));

		add_action('tutor/lesson_list/right_icon_area', array($this, 'show_content_drip_icon'));

		add_filter('tutor_lesson/single/content', array($this, 'drip_content_protection'));
		add_filter('tutor_assignment/single/content', array($this, 'drip_content_protection'));
		add_filter('tutor_single_quiz/body', array($this, 'drip_content_protection'));
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

		$course_id = tutils()->get_course_id_by_content($post);
		$enable = (bool) get_tutor_course_settings($course_id, 'enable_content_drip');
		if ( ! $enable){
			return false;
		}

		$drip_type = get_tutor_course_settings($course_id, 'content_drip_type');
		$this->drip_type = $drip_type;

		$courseObg = get_post_type_object( $post->post_type );
		$singular_post_type = '';
		if ( ! empty($courseObg->labels->singular_name)){
			$singular_post_type = $courseObg->labels->singular_name;
		}

		//if ($lesson_post_type === $post->post_type){
			if ($drip_type === 'unlock_by_date'){
				$unlock_timestamp = strtotime(get_item_content_drip_settings($lesson_id, 'unlock_date'));
				if ($unlock_timestamp){
					$unlock_date = date_i18n(get_option('date_format'), $unlock_timestamp);
					$this->unlock_message = sprintf(__("This %s will be available from %s", 'tutor-pro'), $singular_post_type, $unlock_date);

					return $unlock_timestamp > current_time('timestamp');
				}
			}elseif ($drip_type === 'specific_days'){
				$days = (int) get_item_content_drip_settings($lesson_id, 'after_xdays_of_enroll');

				if ($days > 0){
					$enroll = tutils()->is_course_enrolled_by_lesson($lesson_id);
					$enroll_date = tutils()->array_get('post_date', $enroll);
					$enroll_date = date('Y-m-d', strtotime($enroll_date));
					$days_in_time = 60*60*24*$days;

					$unlock_timestamp = strtotime($enroll_date) + $days_in_time;

					$unlock_date = date_i18n(get_option('date_format'), $unlock_timestamp);
					$this->unlock_message = sprintf(__("This lesson will be available for you from %s", 'tutor-pro'), $unlock_date);

					return $unlock_timestamp > current_time('timestamp');
				}
			}
		//}

		if ($drip_type === 'unlock_sequentially'){
			$previous_id = tutor_utils()->get_course_previous_content_id($post);

			if ($previous_id){
				$previous_content = get_post($previous_id);

				$obj = get_post_type_object( $previous_content->post_type );

				if ($previous_content->post_type === $lesson_post_type){
					$is_lesson_complete = tutils()->is_completed_lesson($previous_id);
					if ( ! $is_lesson_complete){
						$this->unlock_message = sprintf(__("Please complete previous %s first", 'tutor-pro'), $obj->labels->singular_name);
						return true;
					}
				}
				if ($previous_content->post_type === 'tutor_assignments') {
					$is_submitted = tutils()->is_assignment_submitted($previous_id);
					if ( ! $is_submitted){
						$this->unlock_message = sprintf(__("Please submit previous %s first", 'tutor-pro'), $obj->labels->singular_name);
						return true;
					}
				}
				if ($previous_content->post_type === 'tutor_quiz'){
					$attempts = tutils()->quiz_ended_attempts($previous_id);
					if ( ! $attempts){
						$this->unlock_message = sprintf(__("Please complete previous %s first", 'tutor-pro'), $obj->labels->singular_name);
						return true;
					}
				}

				/*
				if ($post->post_type === $lesson_post_type){
					$is_lesson_complete = tutils()->is_completed_lesson($previous_id);
					if ( ! $is_lesson_complete){
						$this->unlock_message = sprintf(__("Please complete previous %s first", 'tutor-pro'), $obj->labels->singular_name);
						return true;
					}
				}
				if ($post->post_type === 'tutor_quiz'){
					$attempts = tutils()->quiz_attempts($previous_id);
				}*/
			}

		}elseif ($drip_type === 'after_finishing_prerequisites'){
			$prerequisites = (array) get_item_content_drip_settings($lesson_id, 'prerequisites');
			$prerequisites = array_filter($prerequisites);
			
			if (tutils()->count($prerequisites)){
				$required_finish = array();
				
				foreach ($prerequisites as $id){
					$item = get_post($id);

					if ($item->post_type === $lesson_post_type){
						$is_lesson_complete = tutils()->is_completed_lesson($id);
						if ( ! $is_lesson_complete){
							$required_finish[] = "<a href='".get_permalink($item)."' target='_blank'>{$item->post_title}</a>";
						}
					}
					if ($item->post_type === 'tutor_assignments') {
						$is_submitted = tutils()->is_assignment_submitted($id);
						if ( ! $is_submitted){
							$required_finish[] = "<a href='".get_permalink($item)."' target='_blank'>{$item->post_title}</a>";
						}
					}
					if ($item->post_type === 'tutor_quiz'){
						$attempts = tutils()->quiz_ended_attempts($id);
						if ( ! $attempts){
							$required_finish[] = "<a href='".get_permalink($item)."' target='_blank'>{$item->post_title}</a>";
						}
					}
				}

				if (tutils()->count($required_finish)){
					$output = '<h4>' .sprintf(__("You can take this %s after finishing the following prerequisites:", 'tutor-pro'), $singular_post_type) . '</h4>';
					$output .= "<ul>";
					foreach ($required_finish as $required_finish_item){
						$output .= "<li>{$required_finish_item}</li>";
					}
					$output .= "</ul>";

					$this->unlock_message = $output;
					return true;
				}
			}
		}

		return false;
	}

	public function drip_content_protection($html){
		if ($this->is_lock_lesson(get_the_ID())){

			if ($this->drip_type === 'after_finishing_prerequisites'){
				$img_url = trailingslashit(TUTOR_CONTENT_DRIP()->url).'assets/images/traffic-light.svg';

				$output = "<div class='content-drip-message-wrap content-drip-wrap-flex'> <div class='content-drip-left'><img src='{$img_url}' alt='' /> </div> <div class='content-drip-right'>{$this->unlock_message}</div> </div>";

				$output = apply_filters('tutor/content_drip/unlock_message', $output);
				return "<div class='tutor-lesson-content-drip-wrap'> {$output} </div>";
			}else{
				$output = apply_filters('tutor/content_drip/unlock_message', "<div class='content-drip-message-wrap tutor-alert'> {$this->unlock_message}</div>");
				return "<div class='tutor-lesson-content-drip-wrap'> {$output} </div>";
			}

		}

		return $html;
	}

	public function is_valid_date($date_string = null){
		return (bool) strtotime($date_string);
	}

}