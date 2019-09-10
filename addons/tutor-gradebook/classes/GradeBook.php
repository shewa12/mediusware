<?php
/**
 * Tutor Multi Instructor
 */

namespace TUTOR_GB;

class GradeBook{

	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('tutor_admin_register', 	array($this, 'register_menu'));
	}

	public function admin_scripts($page){
		if ($page === 'tutor_gradebook') {
			wp_enqueue_script( 'tutor-gradebook', TUTOR_GB()->url . 'assets/js/gradebook.js', array(), TUTOR_GB()->version, true );
		}
	}
	
	public function register_menu(){
		add_submenu_page('tutor', __('Grade Book', 'tutor-pro'), __('Grade Book', 'tutor-pro'), 'manage_tutor', 'tutor_gradebook', array($this, 'tutor_gradebook') );
	}

	public function tutor_gradebook(){
		include TUTOR_GB()->path.'views/pages/grade_book.php';
	}

}