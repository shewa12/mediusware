<?php

global $wpdb;

$course_type = tutor()->course_post_type;
$all_data = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type ='{$course_type}' AND post_status = 'publish' ");
$current_id = isset($_GET['course_id']) ? $_GET['course_id'] : (isset($all_data[0]) ? $all_data[0]->ID : '');

$totalCount = (int) $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} WHERE post_parent = {$current_id} AND post_type = 'tutor_enrolled';");

$per_page = 50;
$total_items = $totalCount;
$current_page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
$start =  max( 0,($current_page-1)*$per_page );


$lesson_type = tutor()->lesson_post_type;
$quiz_number = '';

if($current_id){
	$quiz_number = $wpdb->get_var(
		"SELECT COUNT(ID) FROM {$wpdb->posts}
		WHERE post_parent IN (SELECT ID FROM {$wpdb->posts} WHERE post_type ='topics' AND post_parent = {$current_id} AND post_status = 'publish')
	 	AND post_type ='tutor_quiz' 
		AND post_status = 'publish'");
}

$course_completed = $wpdb->get_results(
        "SELECT ID, post_author, meta.meta_value as order_id from {$wpdb->posts} 
        JOIN {$wpdb->postmeta} meta 
        ON ID = meta.post_id
        WHERE post_type = 'tutor_enrolled' 
        AND meta.meta_key = '_tutor_enrolled_by_order_id'
        AND post_parent = {$current_id} 
        AND post_status = 'completed' 
        ORDER BY ID DESC LIMIT {$start},{$per_page};");

// echo '<pre>';
// print_r( $start );
// echo '</pre>';

$complete_data = 0;
$course_single = array();
if(is_array($course_completed) && !empty($course_completed)){
    $complete = 0;
    foreach ($course_completed as $data) {
        $var = array();
        $var['order_id'] = $data->order_id;
        $var['post_id'] = $current_id;
        $var['complete'] = tutor_utils()->get_course_completed_percent($current_id, $data->post_author);
        $var['user_id'] = $data->post_author;
        $course_single[] = $var;
        if($var['complete'] == 100){ $complete_data++; }
    }
} else {
    $complete_data = 0;
}
?>

<div class="report-stats">
    <select class="single-course-report">
        <?php
            if (is_array($all_data)) {
                foreach ($all_data as $val) {
                    echo '<option '.($current_id == $val->ID ? "selected" : "").' value="'.$val->ID.'">'.$val->post_title.'</option>';
                }
            }
        ?>
    </select>
</div>
</br></br>

<div class="report-stats">
    <div class="report-stat-box">
        <div class="report-stat-box-body">
            <div class="box-icon">
                <i class="tutor-icon-mortarboard"></i>
            </div>
            <div class="box-stats-text">
                <h3><?php echo tutor_utils()->get_lesson_count_by_course($current_id); ?></h3>
                <p><?php _e('Lesson Number', 'tutor-pro'); ?></p>
            </div>
        </div>
    </div>
    <div class="report-stat-box">
        <div class="report-stat-box-body">
            <div class="box-icon">
                <i class="tutor-icon-graduate"></i>
            </div>
            <div class="box-stats-text">
                <h3><?php echo $quiz_number; ?></h3>
                <p><?php _e('Quiz Number', 'tutor-pro'); ?></p>
            </div>
        </div>
    </div>
    <div class="report-stat-box">
        <div class="report-stat-box-body">
            <div class="box-icon">
                <i class="tutor-icon-open-book-1"></i>
            </div>
            <div class="box-stats-text">
                <h3><?php echo tutor_utils()->get_assignments_by_course($current_id)->count; ?></h3>
                <p><?php _e('Assignments Number', 'tutor-pro'); ?></p>
            </div>
        </div>
    </div>
    <div class="report-stat-box">
        <div class="report-stat-box-body">
            <div class="box-icon">
                <i class="tutor-icon-clipboard"></i>
            </div>
            <div class="box-stats-text">
                <?php $total_student = tutor_utils()->count_enrolled_users_by_course($current_id); ?>
                <h3><?php echo $total_student; ?></h3>
                <p><?php _e('Course Enrolled', 'tutor-pro'); ?></p>
            </div>
        </div>
    </div>
    <div class="report-stat-box">
        <div class="report-stat-box-body">
            <div class="box-icon">
                <i class="tutor-icon-conversation-1"></i>
            </div>
            <div class="box-stats-text">
                <h3><?php echo $complete_data; ?></h3>
                <p><?php _e('Course Completed', 'tutor-pro'); ?></p>
            </div>
        </div>
    </div>
    <div class="report-stat-box">
        <div class="report-stat-box-body">
            <div class="box-icon">
                <i class="tutor-icon-student"></i>
            </div>
            <div class="box-stats-text">
                <h3><?php echo $total_student - $complete_data; ?></h3>
                <p><?php _e('Course Continue', 'tutor-pro'); ?></p>
            </div>
        </div>
    </div>
    <div class="report-stat-box">
        <div class="report-stat-box-body">
            <div class="box-icon">
                <i class="tutor-icon-professor"></i>
            </div>
            <div class="box-stats-text">
                <?php
                    $instructor = tutor_utils()->get_instructors_by_course($current_id);
                    $instructor = is_array($instructor) ? count($instructor) : 0;
                ?>
                <h3><?php echo $instructor; ?></h3>
                <p><?php _e('Instructors', 'tutor-pro'); ?></p>
            </div>
        </div>
    </div>
    <div class="report-stat-box">
        <div class="report-stat-box-body">
            <div class="box-icon">
                <i class="tutor-icon-review"></i>
            </div>
            <div class="box-stats-text">
                <h3><?php echo count(tutor_utils()->get_course_reviews($current_id)); ?></h3>
                <p><?php _e('Total Reviews', 'tutor-pro'); ?></p>
            </div>
        </div>
    </div>
</div>


<div class="tutor-bg-white box-padding">
    <h3><?php echo get_the_title($current_id); ?></h3>
    <p><?php echo sprintf(__('Total Data  %d', 'tutor-pro'), $totalCount) ?></p>
    <table class="widefat tutor-report-table">
        <tr>
            <th><?php _e('Order', 'tutor-pro'); ?> </th>
            <th><?php _e('Course', 'tutor-pro'); ?> </th>
            <th><?php _e('Student', 'tutor-pro'); ?> </th>
            <th><?php _e('Complete', 'tutor-pro'); ?> </th>
        </tr>
		<?php
		if (is_array($course_single) && count($course_single)){
			foreach ($course_single as $course){
				$order = wc_get_order( $course['order_id'] );
                $order_items = $order->get_items();
				?>
                <tr>
                    <?php edit_post_link( '#'.$course['order_id'] , '<td>', '</td>', $course['order_id'], null ); ?>
                    <td>
						<a target="_blank" href="<?php echo get_permalink($course['post_id']); ?>"><?php echo get_the_title($course['post_id']); ?></a>
					</td>
                    <td>
						<?php 
							$user = get_userdata($course['user_id']);
							echo $user->display_name; 
						?>
					</td>
                    <td>
						<?php echo $course['complete']; ?>
					</td>
                </tr>
				<?php
			}
		}
		?>
    </table>

    <div class="tutor-pagination" >
		<?php
		echo paginate_links( array(
			'base' => str_replace( $current_page, '%#%', "admin.php?page=tutor_report&sub_page=sales&paged=%#%" ),
			'current' => max( 1, $current_page ),
			'total' => ceil($total_items/$per_page)
		) );
		?>
    </div>
</div>