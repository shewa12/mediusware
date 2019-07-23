<?php
namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) )
	exit;

class General{

	public function __construct() {
		add_action('tutor_action_tutor_add_course_builder', array($this, 'tutor_add_course_builder'));

		add_filter('frontend_course_create_url', array($this, 'frontend_course_create_url'));
		add_filter( 'template_include', array($this, 'fs_course_builder'), 99 );
	}

	/**
	 * Process course submission from frontend course builder
	 *
	 * @since v.1.3.4
	 */
	public function tutor_add_course_builder(){
		//Checking nonce
		tutor_utils()->checking_nonce();

		$course_post_type = tutor()->course_post_type;

		$course_ID = (int) sanitize_text_field(tutor_utils()->array_get('course_ID', $_POST));
		$post_ID = (int) sanitize_text_field(tutor_utils()->array_get('post_ID', $_POST));

		$post = get_post($post_ID);
		$update = true;

		/**
		 * Update the post
		 */

		$content = wp_kses_post(tutor_utils()->array_get('content', $_POST));
		$title = sanitize_text_field(tutor_utils()->array_get('title', $_POST));
		$tax_input = tutor_utils()->array_get('tax_input', $_POST);

		$postData = array(
			'ID'            => $post_ID,
			'post_title'    => $title,
			'post_name'     => sanitize_title($title),
			'post_content'  => $content,
		);

		//Publish or Pending...
		if (tutor_utils()->array_get('course_submit_btn', $_POST) === 'save_course_as_draft'){
			$postData['post_status'] = 'draft';
		}else{
			$can_publish_course = (bool) tutor_utils()->get_option('instructor_can_publish_course');
			if ($can_publish_course){
				$postData['post_status'] = 'publish';
			}else{
				$postData['post_status'] = 'pending';
			}
		}

		wp_update_post($postData);

		/**
		 * Setting Thumbnail
		 */
		$_thumbnail_id = (int) sanitize_text_field(tutor_utils()->array_get('tutor_course_thumbnail_id', $_POST));
		if ($_thumbnail_id){
			update_post_meta($post_ID, '_thumbnail_id', $_thumbnail_id);
		}else{
			delete_post_meta($post_ID, '_thumbnail_id');
		}

		/**
		 * Adding taxonomy
		 */
		if ( tutor_utils()->count($tax_input) ) {
			foreach ( $tax_input as $taxonomy => $tags ) {
				$taxonomy_obj = get_taxonomy($taxonomy);
				if ( ! $taxonomy_obj ) {
					/* translators: %s: taxonomy name */
					_doing_it_wrong( __FUNCTION__, sprintf( __( 'Invalid taxonomy: %s.' ), $taxonomy ), '4.4.0' );
					continue;
				}

				// array = hierarchical, string = non-hierarchical.
				if ( is_array( $tags ) ) {
					$tags = array_filter($tags);
				}
				wp_set_post_terms( $post_ID, $tags, $taxonomy );
			}
		}

		/**
		 * Adding support for do_action();
		 */
		do_action( "save_post_{$course_post_type}", $post_ID, $post, $update );
		do_action( 'save_post', $post_ID, $post, $update );
		do_action( 'save_tutor_course', $post_ID, $postData);

		if (wp_doing_ajax()){
			wp_send_json_success();
		}else{

			/**
			 * If update request not comes from edit page, redirect it to edit page
			 */
			$edit_mode = (int) sanitize_text_field(tutor_utils()->array_get('course_ID', $_GET));
			if ( ! $edit_mode){
				$edit_page_url = add_query_arg(array('course_ID' => $post_ID));
				wp_redirect($edit_page_url);
				die();
			}

			/**
			 * Finally redirect it to previous page to avoid multiple post request
			 */
			wp_redirect(tutor_utils()->referer());
			die();
		}
		die();
	}


	/**
	 * @return string
	 *
	 * Frontend Course builder url
	 */
	public function frontend_course_create_url(){
		return tutor_utils()->get_tutor_dashboard_page_permalink('create-course');
	}


	/**
	 * @param $template
	 *
	 * @return bool|string
	 *
	 * Include Dashboard
	 */
	public function fs_course_builder($template){
		global $wp_query;

		if ($wp_query->is_page) {
			$student_dashboard_page_id = (int) tutor_utils()->get_option( 'tutor_dashboard_page_id' );
			if ( $student_dashboard_page_id === get_the_ID() ) {

				if (tutor_utils()->array_get('tutor_dashboard_page', $wp_query->query_vars) === 'create-course') {
					$template = tutor_get_template('dashboard.create-course');
				}

			}
		}

		return $template;
	}


}