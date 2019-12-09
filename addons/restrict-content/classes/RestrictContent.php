<?php
/**
 * Tutor Course attachments Main Class
 */

namespace TUTOR_RC;

use TUTOR\Tutor_Base;

class RestrictContent extends Tutor_Base {

	public function __construct() {
		parent::__construct();

		add_filter('tutor_course/single/add-to-cart', array($this, 'tutor_course_add_to_cart'));
		add_filter('tutor_course_price', array($this, 'tutor_course_price'));
	}

	public function tutor_course_add_to_cart($html) {
		global $current_user, $wpdb, $post;

		$monetize_by = get_tutor_option('monetize_by');


		if ($monetize_by !== 'restrict-content'){
			return $html;
		}

		if (function_exists('rcp_user_can_access')) {
			$has_membership_access = false;

			if (rcp_user_can_access(get_current_user_id(), $post->ID)) {
				$has_membership_access = true;
			}

			if (is_user_logged_in()) {
				if ($has_membership_access) {
					return $html;
				} else {
					$msg = rcp_get_restricted_content_message();
					return apply_filters('tutor_enrol_no_membership_msg', $msg);
				}
			}
		}
		
		return $html;
	}

	public function tutor_course_price($html){
		$monetize_by = get_tutor_option('monetize_by');

		if ($monetize_by === 'restrict-content'){
			return '';
		}

		return $html;
	}

}