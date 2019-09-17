
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Gradebooks', 'tutor-pro'); ?>  </h1>

    <hr class="wp-header-end">

    <nav class="nav-tab-wrapper tutor-gradebook-nav-wrapper">
        <a href="<?php echo remove_query_arg('sub_page'); ?>" class="nav-tab-item nav-tab-item-active"><?php _e('Overview'); ?></a>
        <a href="<?php echo add_query_arg(array('sub_page' => 'gradebooks')); ?>" class="nav-tab-item"><?php _e('Gradebooks'); ?></a>
    </nav>

    <div class="tutor_admin_gradebook_list">



		<?php

		global $wpdb;


		$gradebooks = $wpdb->get_results("SELECT gradebook_result.*, 

(SELECT COUNT(quizzes.quiz_id) FROM {$wpdb->tutor_gradebooks_results} quizzes WHERE quizzes.user_id = gradebook_result.user_id AND quizzes.course_id = gradebook_result.course_id AND quizzes.result_for = 'quiz') as quiz_count,

(SELECT COUNT(assignments.assignment_id) FROM {$wpdb->tutor_gradebooks_results} assignments WHERE assignments.user_id = gradebook_result.user_id AND assignments.course_id = gradebook_result.course_id AND assignments.result_for = 'assignment') as assignment_count,
grade_config,
student.display_name

FROM {$wpdb->tutor_gradebooks_results} gradebook_result
LEFT JOIN {$wpdb->tutor_gradebooks} gradebook ON gradebook_result.gradebook_id = gradebook.gradebook_id
LEFT  JOIN {$wpdb->users} student ON gradebook_result.user_id = student.ID


WHERE gradebook_result.result_for = 'final' ");


		echo '<pre>';
		print_r($gradebooks);
		echo '</pre>';



		if (tutils()->count($gradebooks)){

			?>
            <table class="gradebooks-lists">
                <tr>
                    <th><?php _e('Student', 'tutor-pro'); ?></th>
                    <th><?php _e('Course', 'tutor-pro'); ?></th>
                    <th><?php _e('Quiz', 'tutor-pro'); ?></th>
                    <th><?php _e('Assignments', 'tutor-pro'); ?></th>
                    <th><?php _e('Final Grade', 'tutor-pro'); ?></th>
                </tr>

				<?php
                foreach ($gradebooks as $gradebook){
	                $quiz_grade = get_quiz_gradebook_by_course($gradebook->course_id);
	                $assignment_grade = get_assignment_gradebook_by_course($gradebook->course_id);
					?>
                    <tr>
                        <td>
                            <div class="gradebooks-user-col">
                                <div class="tutor-flex-row">
                                    <div class="tutor-col-4">
			                            <?php
			                            echo tutils()->get_tutor_avatar($gradebook->user_id);
			                            ?>
                                    </div>
                                    <div class="tutor-col-8 user-info-col">
                                        <p class="user-display-name"><?php echo $gradebook->display_name; ?></p>
                                        <p class="gradebook-date"><?php echo date_i18n(get_option('date_format', strtotime($gradebook->update_date)
                                            )); ?></p>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <p><?php echo get_the_title($gradebook->course_id); ?></p>
                            <p>
                                <?php
                                echo tutils()->course_progress_status_context($gradebook->course_id, $gradebook->user_id);
                                ?>, 5 quiz, 0 assignment
                            </p>
                        </td>

                        <td><?php echo tutor_generate_grade_html($quiz_grade); ?></td>
                        <td><?php echo tutor_generate_grade_html($assignment_grade); ?></td>
                        <td><?php echo tutor_generate_grade_html($gradebook); ?></td>
                    </tr>

					<?php
				} ?>

            </table>

			<?php
		}
		?>



    </div>

</div>