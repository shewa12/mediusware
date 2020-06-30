<?php
if ( ! defined( 'ABSPATH' ) )
exit;
?>

<div class="tutor-report-student-data">
	<?php
	$_search = isset($_GET['search']) ? $_GET['search'] : '';
	$_student = isset($_GET['student_id']) ? $_GET['student_id'] : '';
	if(!$_student){
		$sub_page = 'this_year';
		$course_id = false;
		if ( ! empty($_GET['time_period'])){
			$sub_page = sanitize_text_field($_GET['time_period']);
		}
		if ( ! empty($_GET['course_id'])){
			$course_id = (int) sanitize_text_field($_GET['course_id']);
		}
		if ( ! empty($_GET['date_range_from']) && ! empty($_GET['date_range_to'])){
			$sub_page = 'date_range';
		}
		include $view_page.$page."/graph/{$sub_page}.php";
		include $view_page.$page."/student-table.php";
	} else {
		include $view_page.$page."/student-profile.php";
	} ?>
</div>