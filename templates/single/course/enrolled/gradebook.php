<?php

/**
 * Grade Book
 *
 * @since v.1.4.2
 * @author themeum
 * @url https://themeum.com
 */

$course_id = get_the_ID();

$grades = get_generated_gradebook('all', $course_id);

$final_grade = get_generated_gradebook('final', $course_id);
$assignment_grade = get_assignment_gradebook_by_course($course_id);
$quiz_grade = get_quiz_gradebook_by_course($course_id);

if ($assignment_grade){
    ?>
    <table>
        <tr>
            <th><?php _e('Quiz', 'tutor-pro'); ?></th>
            <th><?php _e('Assignments', 'tutor-pro'); ?></th>
            <th><?php _e('Final Grade', 'tutor-pro'); ?></th>
        </tr>
        <tr>
            <td><?php echo tutor_generate_grade_html($quiz_grade); ?></td>
            <td><?php echo tutor_generate_grade_html($assignment_grade); ?></td>
            <td><?php echo tutor_generate_grade_html($final_grade); ?></td>
        </tr>
    </table>
    <?php
}



if (tutils()->count($grades)){

	?>

	<table class="course-single-gradebooks">
		<tr>
			<th><?php _e('Title', 'tutor-pro'); ?></th>
			<th><?php _e('Grade', 'tutor-pro'); ?></th>
		</tr>
		<?php

		foreach ($grades as $grade){
			?>
			<tr>
				<td>
					<p class="course-item-title">
						<?php
						if ($grade->result_for === 'quiz'){
							echo "<a href='".get_permalink($grade->quiz_id)."' target='_blank'>[{$grade->result_for}] ".get_the_title($grade->quiz_id)."</a>";
						}elseif($grade->result_for === 'assignment'){
							echo "<a href='".get_permalink($grade->assignment_id)."' target='_blank'>[{$grade->result_for}] ".get_the_title($grade->assignment_id)."</a>";
						}
						?>
					</p>
					<p class="datetime">
						<?php _e('Generated at', 'tutor-pro'); ?>: <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'),
							strtotime($grade->generate_date)); ?>
					</p>
					<p class="datetime">
						<?php _e('Updated at', 'tutor-pro'); ?>: <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'),
							strtotime($grade->update_date)); ?>
					</p>
				</td>
				<td><?php echo tutor_generate_grade_html($grade); ?></td>
			</tr>
			<?php
		}

		?>

	</table>

	<?php


}