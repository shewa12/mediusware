<?php
namespace TUTOR_ZOOM;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Init {
	public $version = TUTOR_ZOOM_VERSION;
	public $path;
	public $url;
	public $basename;

	function __construct() {
		if ( ! function_exists('tutor')){
			return;
		}
		$addonConfig = tutor_utils()->get_addon_config(TUTOR_ZOOM()->basename);
		$isEnable = (bool) tutor_utils()->avalue_dot('is_enable', $addonConfig);
		if ( ! $isEnable){
			return;
		}

		$this->path = plugin_dir_path(TUTOR_ZOOM_FILE);
		$this->url = plugin_dir_url(TUTOR_ZOOM_FILE);
		$this->basename = plugin_basename(TUTOR_ZOOM_FILE);

		$this->load_tutor_zoom();
	}

	public function load_tutor_zoom(){
		/**
		 * Loading Autoloader
		 */

		spl_autoload_register(array($this, 'loader'));

		add_filter('tutor/options/extend/attr', array($this, 'add_options'));
	}

	/**
	 * @param $className
	 *
	 * Auto Load class and the files
	 */
	private function loader($className) {
		if ( ! class_exists($className)){
			$className = preg_replace(
				array('/([a-z])([A-Z])/', '/\\\/'),
				array('$1$2', DIRECTORY_SEPARATOR),
				$className
			);

			$className = str_replace('TUTOR_ZOOM'.DIRECTORY_SEPARATOR, 'classes'.DIRECTORY_SEPARATOR, $className);
			$file_name = $this->path.$className.'.php';

			if (file_exists($file_name) && is_readable( $file_name ) ) {
				require_once $file_name;
			}
		}
	}


	//Run the TUTOR right now
	public function run(){
		register_activation_hook( TUTOR_ZOOM_FILE, array( $this, 'tutor_activate' ) );
	}

	/**
	 * Do some task during plugin activation
	 */
	public function tutor_activate(){
		$version = get_option('tutor_zoom_version');
		//Save Option
		if ( ! $version){
			update_option('tutor_zoom_version', TUTOR_ZOOM_VERSION);
		}
	}

	public function add_options($attr){
		$attr['zoom_settings'] = array(
			'label'     => __('Zoom Settings', 'tutor-pro'),
			'sections'    => array(
				'general' => array(
					'label' => __('Enable/Disable', 'tutor-pro'),
					'desc' => __('Enable Disable Option to on/off notification on various event', 'tutor-pro'),
					'fields' => array(
						'email_to_students' => array(
							'type'      => 'checkbox',
							'label'     => __('E-Mail to Students', 'tutor-pro'),
							'options'   => array(
								'quiz_completed' 				=> __('Quiz Completed', 'tutor-pro'),
								'completed_course' 				=> __('Completed a Course', 'tutor-pro'),
								'remove_from_course' 			=> __('Remove from Course', 'tutor-pro'),
								'manual_enrollment' 			=> __('After Manual Enrollment', 'tutor-pro'),
								'assignment_graded' 			=> __('Assignment Graded', 'tutor-pro'),
								'new_announcement_posted' 		=> __('New Announcement Posted', 'tutor-pro'),
								'after_question_answered' 		=> __('Q&A Message Answered', 'tutor-pro'),
								'feedback_submitted_for_quiz' 	=> __('Feedback submitted for Quiz Attempt', 'tutor-pro'),
								'rate_course_and_instructor' 	=> __('Rate Course and Instructor After Course Completed', 'tutor-pro'),
							),
							'desc'      => __('Select when to send notification to the students',	'tutor-pro'),
						),
						'email_to_teachers' => array(
							'type'      => 'checkbox',
							'label'     => __('E-Mail to Teachers', 'tutor-pro'),
							'options'   => array(
								'a_student_enrolled_in_course' 	=> __('A Student Enrolled in Course', 'tutor-pro'),
								'a_student_completed_course'    => __('A Student Completed Course', 'tutor-pro'),
								'a_student_completed_lesson'    => __('A Student Completed Lesson', 'tutor-pro'),
								'a_student_placed_question'     => __('A Student asked a Question in Q&amp;A', 'tutor-pro'),
								'student_submitted_quiz'        => __('Student Submitted Quiz', 'tutor-pro'),
								'student_submitted_assignment'  => __('Student Submitted Assignment', 'tutor-pro'),
							),
							'desc'      => __('Select when to send notification to the teachers',	'tutor-pro'),
						),
						'email_to_admin' => array(
							'type'      => 'checkbox',
							'label'     => __('E-Mail to Admin', 'tutor-pro'),
							'options'   => array(
								'new_instructor_signup' 	=> __('New Instructor Signup', 'tutor-pro'),
								'new_student_signup' 		=> __('New Student Signup', 'tutor-pro'),
								'new_course_submitted' 		=> __('New Course Submitted for Review', 'tutor-pro'),
								'new_course_published' 		=> __('New Course Published', 'tutor-pro'),
								'course_updated' 			=> __('Course Edited/Updated', 'tutor-pro'),
							),
							'desc'      => __('Select when to send notification to the teachers',	'tutor-pro'),
						),
					),
				),
			),
		);

		return $attr;
	}

}