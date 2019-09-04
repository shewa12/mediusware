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

	public function __construct() {
		add_filter('tutor_course_settings_tabs', array($this, 'settings_attr') );

		add_action('tutor_lesson_edit_modal_form_after', array($this, 'content_drip_lesson_metabox'), 10, 0);
	}

	public function settings_attr($args){
		$args['contentdrip'] = array(
			'label' => __('Content Drip', 'tutor'),
			'desc' => __('Tutor Content Drip allow you to schedule publish topics / lesson', 'tutor'),
			'icon_class' => 'dashicons dashicons-clock',
			'callback'  => '',
			'fields'    => array(
				'enable_content_drip' => array(
					'type'      => 'checkbox',
					'label'     => '',
					'label_title' => __('Enable', 'tutor'),
					'default' => '0',
					'desc'      => __('Enable / Disable content drip', 'tutor'),
				),
				'content_drip_type' => array(
					'type'      => 'radio',
					'label'     => 'Content Drip Type',
					'default' => 'unlock_by_date',
					'options'   => array(
						'unlock_by_date'                =>  __('Unlock course contents by given date', 'tutor'),
						'specific_days'                =>  __('Unlock course contents after give days', 'tutor'),
						'unlock_item_sequentially'      =>  __('Unlock item sequentially', 'tutor'),
						'after_finish_prerequisites'    =>  __('After finish prerequisites item', 'tutor'),
					),
					'desc'      => __('Based on content drip type, you can control unlocking course contents', 'tutor'),
				),
			),

		);
		return $args;
	}


	public function content_drip_lesson_metabox(){
		include  TUTOR_CONTENT_DRIP()->path.'views/content-drip-lesson.php';
	}

}