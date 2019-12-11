<?php
/**
 * Grade Book
 *
 * @since v.1.4.8
 * @author themeum
 * @url https://themeum.com
 *
 * @version v.1.4.8
 */


if ( ! defined( 'ABSPATH' ) )
	exit;

foreach ($recipients as $recipient){
	$user_id = $recipient->user_id;

	$instructor_type = false;
	$student_type = get_user_meta($user_id, '_is_tutor_student', true);
	if (user_can($user_id, 'tutor_instructor')){
		$instructor_type = __('Instructor', 'tutor-pro');
	}

	$enrolled_course_ids = array_unique(tutils()->get_enrolled_courses_ids_by_user($user_id));
	?>
	<div class="tutor-bp-message-thread-recipient-wrap">
		<?php
		echo '<p class="tutor-bp-thread-recipient-name" style="display:flex;"><strong>';
		_e('Participant : ', 'tutor-pro');
		echo '</strong>';

		echo bp_get_displayed_user_avatar(array('item_id' => $user_id, 'width' => 30, 'height' => 30)).' '. bp_core_get_user_displayname($user_id);
		if ($instructor_type || $student_type){
			echo ' (';
			echo $instructor_type ? $instructor_type : '';
			if ($instructor_type && $student_type){
				echo ' , ';
			}
			if ($student_type){
				_e('Student', 'tutor-pro');
			}
			echo ')';
		}
		echo '</p>';
		?>

		<?php
		if (tutils()->count($enrolled_course_ids)){
			?>
			<div class="tutor-bp-thread-participant-enrolled-wrap">
				<dl class="thread-participant-enrolled">
					<dt><?php _e('Enrolled courses', 'tutor-pro'); ?>:</dt>
					<dd>
						<ul class="tutor-bp-enrolled-course-list">
							<?php
							foreach ($enrolled_course_ids as $course_id){
								$course_title = get_the_title($course_id);
								if ($course_title) {
									?>
									<li>
										<a href="<?php echo get_the_permalink( $course_id ); ?>" class="bp-tooltip" data-bp-tooltip="<?php echo $course_title; ?>">
											<?php echo $course_title; ?>
										</a>
									</li>
									<?php
								}
							}
							?>
						</ul>
					</dd>
				</dl>
			</div>
			<?php
		}
		?>

	</div>
	<?php
}