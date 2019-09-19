
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Gradebooks', 'tutor-pro'); ?>  </h1>

    <hr class="wp-header-end">

    <nav class="nav-tab-wrapper tutor-gradebook-nav-wrapper">
        <a href="<?php echo remove_query_arg('sub_page'); ?>" class="nav-tab-item nav-tab-item-active"><?php _e('Overview'); ?></a>
        <a href="<?php echo add_query_arg(array('sub_page' => 'gradebooks')); ?>" class="nav-tab-item"><?php _e('Gradebooks'); ?></a>
    </nav>




    <div class="tutor-row">
        <div class="tutor-col-6">

            <form id="gradebook-search" method="get">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

                <input type="text" name="s" value="<?php echo tutils()->array_get('s', $_REQUEST) ?>">
            </form>

        </div>
    </div>


    <div class="tutor_admin_gradebook_list">



		<?php
		$per_page = 1;
		$current_page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
		$start =  max( 0,($current_page-1)*$per_page );

		$gradebooks = get_generated_gradebooks(array('start' => $start, 'limit' => $per_page));

		if (tutils()->count($gradebooks->res)){
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
				foreach ($gradebooks->res as $gradebook){
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
                            <p><?php echo $gradebook->course_title; ?></p>
                            <p>
								<?php
								echo tutils()->course_progress_status_context($gradebook->course_id, $gradebook->user_id);
								?>, <?php echo sprintf(__('%d quiz, %d assignment', 'tutor-pro'), $gradebook->quiz_count,
									$gradebook->assignment_count); ?>
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



        <div class="gradebook-overview-footer">

            <div class="tutor-flex-row">
                <div class="tutor-col gradebook-overview-items-col">
			        <?php echo $gradebooks->count.' '.__('Items', 'tutor'); ?>
                </div>

                <div class="tutor-col gradebook-overview-pagination-col">
                    <div class="tutor-pagination">
				        <?php
				        echo paginate_links( array(
					        'base' => str_replace( $current_page, '%#%', "admin.php?page=tutor_gradebook&paged=%#%" ),
					        'current' => max( 1, $current_page ),
					        'total' => ceil($gradebooks->count / $per_page)
				        ) );
				        ?>

                    </div>
                </div>
            </div>

        </div>



    </div>

</div>