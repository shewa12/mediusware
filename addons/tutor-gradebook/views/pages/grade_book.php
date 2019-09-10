<div class="wrap tutor-grade-book-wrap">
    <h2><?php _e('Grade Book', 'tutor'); ?></h2>

<div>

<?php
$courses = tutils()->get_courses_for_instructors( get_current_user_id() );
_e('Select A Course','tutor-pro');

echo '<pre>';
print_r($courses);
echo '</pre>';

$first_item = '';
echo '<select>';
    foreach ($courses as $key => $value) {
        if($key == 0){
            $first_item = $value->ID;
        }
        if(isset($_GET['courseid'])) {
            $first_item = $_GET['courseid'];
        }
        echo "<option ".($first_item==$value->ID ? 'selected' : '')." value={$value->ID}>{$value->post_title}</option>";
    }
echo '</select>';
echo '<button class="button button-primary tutor-gradebook-filter">Filter</button>';

function get_user_list($course_id){
	global $wpdb;
	$course_post_type = tutor()->course_post_type;
	$user_id = $wpdb->get_var("SELECT user_id from {$wpdb->usermeta} WHERE meta_key = '_tutor_instructor_course_id' AND meta_value = {$course_id}");
	return $user_id;
}



// $user = get_userdata( get_data($first_item) );

echo '<pre>';
print_r( $user );
echo '</pre>';
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
            <tr>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
            </tr>
            <tr>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
                <td>Demo data</td>
            </tr>
        </tbody>

    </table>

    <div class="tutor-pagination" >
        Pagination
		<?php
		// echo paginate_links( array(
		// 	'base' => str_replace( $current_page, '%#%', "admin.php?page=tutor_gradebook&paged=%#%" ),
		// 	'current' => max( 1, $current_page ),
		// 	'total' => ceil($total_items/$per_page)
		// ) );
		?>
    </div>

</div>