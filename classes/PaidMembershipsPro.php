<?php
/**
 * PaidMembershipsPro class
 *
 * @author: themeum
 * @author_uri: https://themeum.com
 * @package Tutor
 * @since v.1.3.5
 */

namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) )
	exit;

class PaidMembershipsPro {

	public function __construct() {
		add_filter('tutor_monetization_options', array($this, 'tutor_monetization_options'));

		$monetize_by = tutils()->get_option('monetize_by');
		if ( $monetize_by !== 'pmpro'){
			return;
		}

		add_action('pmpro_membership_level_after_other_settings', array($this, 'display_courses_categories'));

		add_action('pre_get_posts', array($this, 'pre_get_posts'));
	}

	public function pre_get_posts($query){

		$membershipCats = $this->get_hidden_categories();

		if (tutils()->count($membershipCats)){
			//$query->set('category__not_in', $membershipCats);

			$taxquery = array(
				array(
					'taxonomy' => 'course-category',
					'field' => 'id',
					'terms' => $membershipCats,
					'operator'=> 'NOT IN'
				)
			);

			$query->set( 'tax_query', $taxquery );
		}

		return $query;
	}

	/**
	 * @param $arr
	 *
	 * @return mixed
	 *
	 * Returning monetization options
	 *
	 * @since v.1.3.5
	 */
	public function tutor_monetization_options($arr){
		$has_wc = tutils()->has_wc();
		if ($has_wc){
			$arr['pmpro'] = __('Subscriptions (Paid Memberships Pro)', 'tutor');
		}
		return $arr;
	}


	public function display_courses_categories(){
		global $wpdb;


		if(isset($_REQUEST['edit']))
			$edit = intval($_REQUEST['edit']);
		else
			$edit = false;

		// get the level...
		if(!empty($edit) && $edit > 0) {
			$level = $wpdb->get_row( $wpdb->prepare( "
					SELECT * FROM $wpdb->pmpro_membership_levels
					WHERE id = %d LIMIT 1",
				$edit
			),
				OBJECT
			);
			$temp_id = $level->id;
		} elseif(!empty($copy) && $copy > 0) {
			$level = $wpdb->get_row( $wpdb->prepare( "
					SELECT * FROM $wpdb->pmpro_membership_levels
					WHERE id = %d LIMIT 1",
				$copy
			),
				OBJECT
			);
			$temp_id = $level->id;
			$level->id = NULL;
		}
		else

			// didn't find a membership level, let's add a new one...
			if(empty($level)) {
				$level = new \stdClass();
				$level->id = NULL;
				$level->name = NULL;
				$level->description = NULL;
				$level->confirmation = NULL;
				$level->billing_amount = NULL;
				$level->trial_amount = NULL;
				$level->initial_payment = NULL;
				$level->billing_limit = NULL;
				$level->trial_limit = NULL;
				$level->expiration_number = NULL;
				$level->expiration_period = NULL;
				$edit = -1;
			}

		//defaults for new levels
		if(empty($copy) && $edit == -1) {
			$level->cycle_number = 1;
			$level->cycle_period = "Month";
		}

		// grab the categories for the given level...
		if(!empty($temp_id))
			$level->categories = $wpdb->get_col( $wpdb->prepare( "
					SELECT c.category_id
					FROM $wpdb->pmpro_memberships_categories c
					WHERE c.membership_id = %d",
				$temp_id
			) );
		if(empty($level->categories))
			$level->categories = array();

		$level_categories = $level->categories;

		//Echo output
		$this->pmpro_listCategories(0, $level_categories);
	}



	function pmpro_listCategories( $parent_id = 0, $level_categories = array() ) {
		$cats = tutils()->get_course_categories_term(0);

		if ( $cats ) {
			foreach ( $cats as $cat ) {
				$name = 'membershipcategory_' . $cat->term_id;
				if ( ! empty( $level_categories ) ) {
					$checked = checked( in_array( $cat->term_id, $level_categories ), true, false );
				} else {
					$checked = '';
				}
				echo "<ul><li class=membershipcategory><input type=checkbox name={$name} id={$name} value=yes {$checked}><label for={$name}>{$cat->name}</label>";
				$this->pmpro_listCategories( $cat->term_id, $level_categories );
				echo '</li></ul>';
			}
		}
	}




	public function get_hidden_categories(){
		global $current_user, $wpdb;



		//get page ids that are in my levels
		if(!empty($current_user->ID))
			$levels = pmpro_getMembershipLevelsForUser($current_user->ID);
		else
			$levels = false;

		//get categories that are filtered by level, but not my level
		global $pmpro_my_cats;
		$pmpro_my_cats = array();

		if($levels) {
			foreach($levels as $key => $level) {
				$member_cats = pmpro_getMembershipCategories($level->id);
				$pmpro_my_cats = array_unique(array_merge($pmpro_my_cats, $member_cats));
			}
		}

		//get hidden cats
		if(!empty($pmpro_my_cats))
			$sql = "SELECT category_id FROM $wpdb->pmpro_memberships_categories WHERE category_id NOT IN(" . implode(',', $pmpro_my_cats) . ")";
		else
			$sql = "SELECT category_id FROM $wpdb->pmpro_memberships_categories";

		$hidden_cat_ids = array_values(array_unique($wpdb->get_col($sql)));

		return $hidden_cat_ids;
	}





}