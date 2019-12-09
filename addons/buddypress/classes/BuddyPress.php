<?php
/**
 * PaidMembershipsPro class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.3.5
 */

namespace TUTOR_BP;

if ( ! defined( 'ABSPATH' ) )
	exit;

class BuddyPress {

	public function __construct() {
		add_filter('tutor_course_settings_tabs', array($this, 'settings_attr') );
		add_filter('bp_get_activity_action', array($this, 'tutor_bp_group_activities'), 10, 3);
		add_action('tutor_course/settings_tab_content/after/tutor_bp', array($this, 'tutor_bp_settings'));

		add_action('tutor_save_course', array($this, 'save_course_meta'), 10, 2);


		/**
		 * Events Hook
		 */

		add_action('tutor_course_complete_after', array($this, 'tutor_course_complete_after'));
		add_action('tutor_after_enroll', array($this, 'tutor_after_enroll'));
		add_action('tutor/course/started', array($this, 'tutor_course_started'));
	}

	public function settings_attr($args){
		$args['tutor_bp'] = array(
			'label' => __('BuddyPress Groups', 'tutor-pro'),
			'desc' => __('Assign this course to a BuddyPress Group', 'tutor-pro'),
			'icon_class' => 'dashicons dashicons-buddicons-buddypress-logo',
			'callback'  => '',
			'fields'    => array(
				'enable_content_drip' => array(
					'type'      => 'checkbox',
					'label'     => '',
					'label_title' => __('Enable', 'tutor-pro'),
					'default' => '0',
					'desc'      => __('Enable / Disable BuddyPress group activity feeds', 'tutor-pro'),
				),

			),
		);
		return $args;
	}


	public function tutor_bp_group_activities($action, $activity, $r ){
		//var_dump($activity);
		//echo "<br /> <hr /> {$action} <hr /> <br />";
		//return '<a href="#">John Doe</a> updated a lesson';

		//return 'this is a posted header';
		$time = bp_insert_activity_meta();
		//return '<a href="">John Doe</a> HelloWorld ';

		return $action;
	}


	public function tutor_bp_settings(){
		include TUTOR_BP()->path.'views/bp-group-course.php';
	}

	/**
	 * @param $post_ID
	 * @param $post
	 *
	 * Save BuddyPress group as course meta
	 */
	public function save_course_meta($post_ID, $post){
		global $wpdb;

		$group_meta_table = $wpdb->prefix.'bp_groups_groupmeta';
		$group_ids = (array) tutils()->array_get('_tutor_bp_course_attached_groups', $_POST);
		$group_ids = array_filter($group_ids);

		$existing_group_ids = self::get_group_ids_by_course($post_ID);
		$delete_group_ids = array_diff($existing_group_ids, $group_ids);
		$new_group_ids = array_diff($group_ids, $existing_group_ids);

		if (tutils()->count($delete_group_ids)){
			foreach ($delete_group_ids as $delete_group_id){
				$wpdb->query("DELETE FROM {$group_meta_table} WHERE group_id = {$delete_group_id} AND meta_key = '_tutor_attached_course' AND meta_value = {$post_ID} ");
			}
		}

		// $wpdb->delete($group_meta_table, array('meta_key' => '_tutor_attached_course', 'meta_value' => $post_ID ));
		if (tutils()->count($new_group_ids)){
			foreach ($new_group_ids as $group_id){
				$wpdb->insert($group_meta_table, array('group_id' => $group_id, 'meta_key' => '_tutor_attached_course', 'meta_value' => $post_ID ));
			}
		}
	}

	/**
	 * @param int $course_id
	 *
	 * @return array
	 *
	 * Get BuddyPress Group ID by Tutor Course ID
	 */

	public static function get_group_ids_by_course($course_id = 0){
		global $wpdb;

		if ( ! $course_id){
			return array();
		}
		$group_meta_table = $wpdb->prefix.'bp_groups_groupmeta';
		$group_ids = $wpdb->get_col("SELECT group_id FROM {$group_meta_table} where meta_key = '_tutor_attached_course' AND meta_value = {$course_id} ;");

		return (array) $group_ids;
	}

	/**
	 * Hook Event Started
	 * @since v.1.4.8
	 */

	public function tutor_course_complete_after($course_id){
		$isEnable = (bool) tutils()->get_course_settings($course_id, 'enable_content_drip');
		if ( ! $isEnable){
			return;
		}

		$student_id = get_current_user_id();
		$group_ids = self::get_group_ids_by_course($course_id);

		if (tutils()->count($group_ids)){
			foreach ($group_ids as $group_id){
				if (groups_is_user_member($student_id, $group_id)) {

					do_action( 'tutor_bp_record_activity_before' );

					$course_url = "<a href='" . get_the_permalink( $course_id ) . "' target='_blank'>" . get_the_title( $course_id ) . "</a>";
					$activity_args = apply_filters( 'tutor_bp_course_completed_record_activity_args', array(
						'user_id'           => $student_id,
						'action'            => '_tutor_course_completed',
						'content'           => sprintf( __( 'I just completed learning %d. It was super insightful!', 'tutor-pro' ), $course_url ),
						'type'              => 'activity_update',
						'item_id'           => $group_id,
						'secondary_item_id' => $course_id,
					) );
					$activity_id = groups_record_activity( $activity_args );

					do_action( 'tutor_bp_record_activity_after', $activity_id );
				}
			}
		}
	}

	/**
	 * @param $course_id
	 *
	 * Course Enroll BuddyPress
	 */
	public function tutor_after_enroll($course_id){
		$isEnable = (bool) tutils()->get_course_settings($course_id, 'enable_content_drip');
		if ( ! $isEnable){
			return;
		}

		$student_id = get_current_user_id();
		$group_ids = self::get_group_ids_by_course($course_id);

		if (tutils()->count($group_ids)){
			foreach ($group_ids as $group_id){
				if (groups_is_user_member($student_id, $group_id)) {
					do_action( 'tutor_bp_record_activity_before' );

					$course_url = "<a href='" . get_the_permalink( $course_id ) . "' target='_blank'>". get_the_title( $course_id ) ."</a>";

					$activity_args = apply_filters( 'tutor_bp_course_enrolled_record_activity_args', array(
						'user_id'           => $student_id,
						'action'            => '_tutor_course_enrolled',
						'content'           => sprintf( __( 'Just got enrolled in %s, looks very promising! You should check it out as well. ', 'tutor-pro' ), $course_url ),
						'type'              => 'activity_update',
						'item_id'           => $group_id,
						'secondary_item_id' => $course_id,
					) );
					$activity_id = groups_record_activity( $activity_args );

					do_action( 'tutor_bp_record_activity_after', $activity_id );
				}
			}
		}

	}


	public function tutor_course_started($course_id){
		$isEnable = (bool) tutils()->get_course_settings($course_id, 'enable_content_drip');
		if ( ! $isEnable){
			return;
		}

		$student_id = get_current_user_id();
		$group_ids = self::get_group_ids_by_course($course_id);

		if (tutils()->count($group_ids)){
			$action_type = '_tutor_course_started';
			foreach ($group_ids as $group_id){
				if (groups_is_user_member($student_id, $group_id)) {
					do_action( 'tutor_bp_record_activity_before', $action_type );

					$course_url = "<a href='" . get_the_permalink( $course_id ) . "' target='_blank'>". get_the_title( $course_id ) ."</a>";

					$activity_args = apply_filters( 'tutor_bp_course_started_record_activity_args', array(
						'user_id'           => $student_id,
						'action'            => $action_type,
						'content'           => sprintf( __( 'Starting with %s from today. Wish me luck! ', 'tutor-pro' ), $course_url ),
						'type'              => 'activity_update',
						'item_id'           => $group_id,
						'secondary_item_id' => $course_id,
					) );
					$activity_id = groups_record_activity( $activity_args );

					do_action( 'tutor_bp_record_activity_after', $action_type, $activity_id );
				}
			}
		}

	}




}