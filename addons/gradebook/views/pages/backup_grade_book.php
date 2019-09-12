<div class="wrap tutor-grade-book-wrap">
    <h2><?php _e('Grade Book', 'tutor'); ?></h2>
<div>

<?php
$per_page = 20;
$current_page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
$start =  max( 0,($current_page-1)*$per_page );


$courses = tutils()->get_courses_for_instructors( get_current_user_id() );
_e('Select A Course','tutor-pro');

$selected_item = '';
echo '<select class="tutor-gradebook-filter-select">';
    foreach ($courses as $key => $value) {
        if($key == 0){
            $selected_item = $value->ID;
        }
        if(isset($_GET['courseid'])) {
            $selected_item = $_GET['courseid'];
        }
        echo "<option ".($selected_item == $value->ID ? 'selected' : '')." value={$value->ID}>{$value->post_title}</option>";
    }
echo '</select>';
echo '<button class="button button-primary tutor-gradebook-filter">Filter</button>';


function get_user_list_by_course($course_id, $start=0, $per_page=50){
	global $wpdb;
	$course_post_type = tutor()->course_post_type;
	$user_id = $wpdb->get_col("SELECT user_id from {$wpdb->usermeta} WHERE meta_key = '_tutor_instructor_course_id' AND meta_value = {$course_id} LIMIT {$start},{$per_page}");
	return $user_id;
}
$user_list = get_user_list_by_course($selected_item, $start, $per_page);


function get_quiz_count_by_course($course_id=0){
    global $wpdb;
    $quiz_count = $wpdb->get_var("SELECT count(ID) from {$wpdb->posts} 
                        WHERE post_type = 'tutor_quiz' 
                        AND post_parent IN (SELECT ID from {$wpdb->posts} WHERE post_parent = {$course_id} AND post_type = 'topics')");
    return $quiz_count;
}
function get_assignment_count_by_course($course_id){
    global $wpdb;
    $assignment_count = $wpdb->get_var("SELECT count(ID) from {$wpdb->posts} 
                        WHERE post_type = 'tutor_assignments' 
                        AND post_parent IN (SELECT ID from {$wpdb->posts} WHERE post_parent = {$course_id} AND post_type = 'topics')");
    return $assignment_count;
}


function get_student_enroll_date($course_id = 0, $user_id = 0){
    global $wpdb;
    $enroll_date = $wpdb->get_var("SELECT post_date from {$wpdb->posts} 
                        WHERE post_type = 'tutor_enrolled' AND post_parent = {$course_id} AND post_status = 'completed'
                        AND post_author = {$user_id}");
    return $enroll_date;
}

$lesson_count = tutils()->get_lesson_count_by_course($selected_item);
$quiz_count = get_quiz_count_by_course($selected_item);
$assignment_count = get_assignment_count_by_course($selected_item);
?>

</div>
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th><?php _e('Student', 'tutor-pro'); ?></th>
                <th><?php _e('Lesson', 'tutor-pro'); ?></th>
                <th><?php _e('Quiz', 'tutor-pro'); ?></th>
                <th><?php _e('Assignment', 'tutor-pro'); ?></th>
                <th><?php _e('Average', 'tutor-pro'); ?></th>
                <th><?php _e('Enrolled', 'tutor-pro'); ?></th>
                <th><?php _e('Current Status', 'tutor-pro'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php
                if(!empty($user_list)) {
                    foreach ($user_list as $user_id) {
                        echo '<tr>';
                            echo '<td>'.get_userdata($user_id)->display_name.'</td>';
                            echo '<td>'.$lesson_count.' ('.tutils()->get_completed_lesson_count_by_course($selected_item, $user_id).')</td>';
                            echo '<td>'.$quiz_count.'</td>';
                            echo '<td>'.$assignment_count.'</td>';
                            echo '<td>Demo data</td>';
                            echo '<td>'.date_i18n(get_option('date_format'), get_student_enroll_date($selected_item, $user_id)).'</td>';
                            echo '<td>'.(tutils()->is_completed_course($selected_item, $user_id) ? 'complete' : 'in-progress').'</td>';
                        echo '</tr>';
                    }
                }
            ?>
        </tbody>

    </table>

    <div class="tutor-pagination" >
        Pagination
		<?php
        $total_items = count($user_list);
		echo paginate_links( array(
			'base' => str_replace( $current_page, '%#%', "admin.php?page=tutor_gradebook&courseid=".$selected_item."&paged=%#%" ),
            'current' => max( 1, $current_page ),
			'total' => ceil($total_items/$per_page)
		) );
		?>
    </div>

</div>