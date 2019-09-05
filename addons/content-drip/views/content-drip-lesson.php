<?php
$post_id = get_the_ID();
$lesson_id = tutils()->array_get('lesson_id', $_POST);
if ( $lesson_id){
	$post_id = (int) sanitize_text_field($lesson_id);
}

$course_id = (int) sanitize_text_field(tutils()->array_get('course_id', $_POST));

$enable_content_drip = get_tutor_course_settings($course_id, 'enable_content_drip');
if ( ! $enable_content_drip){
	return;
}

$content_drip_type = get_tutor_course_settings($course_id, 'content_drip_type');
var_dump($content_drip_type);
?>

<div class="lesson-content-drip-wrap">

    <h3><?php _e('Content Drip Settings', 'tutor-pro'); ?></h3>

	<?php
	if ($content_drip_type === 'unlock_by_date'){
		$unlock_date = get_lesson_content_drip_settings($lesson_id, 'unlock_date');
		?>
        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for=""><?php _e('Lesson unlocking date:', 'tutor-pro'); ?></label>
            </div>
            <div class="tutor-option-field">
                <input type="text" value="<?php echo $unlock_date; ?>" name="content_drip_settings[unlock_date]" class="tutor_date_picker">
                <p class="desc"><?php _e('Date Format:', 'tutor-pro'); ?> <code>yyyy-mm-dd</code> </p>
            </div>
        </div>
		<?php
	}elseif ($content_drip_type === 'specific_days'){
		$days = get_lesson_content_drip_settings($lesson_id, 'after_xdays_of_enroll', 7);
		?>
        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for=""><?php _e('Days', 'tutor-pro'); ?></label>
            </div>
            <div class="tutor-option-field">
                <input type="number" value="<?php echo $days; ?>" name="content_drip_settings[after_xdays_of_enroll]">
                <p class="desc"><?php _e('This lesson will be available after the given number of days.', 'tutor-pro'); ?> </p>
            </div>
        </div>

		<?php
	}
	?>



</div>