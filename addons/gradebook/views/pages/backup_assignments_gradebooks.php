
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Grade Books', 'tutor-pro'); ?>  </h1>
    <a href="<?php echo admin_url('admin.php?page=tutor_gradebook&sub_page=add_new_gradebook'); ?>" class="page-title-action"><i class="tutor-icon-plus"></i>
		<?php _e('Add New Grade Book', 'tutor-pro'); ?>
    </a>
    <hr class="wp-header-end">

    <nav class="nav-tab-wrapper tutor-gradebook-nav-wrapper">
        <a href="<?php echo add_query_arg(array('sub_page' => 'student_gradebooks')); ?>"><?php _e('Students GradeBooks'); ?></a>
        <a href="<?php echo add_query_arg(array('sub_page' => 'final_gradebooks')); ?>"><?php _e('Final Gradebooks'); ?></a>
        <a href="<?php echo add_query_arg(array('sub_page' => 'quiz_gradebooks')); ?>"><?php _e('Quiz Gradebooks'); ?></a>
        <a href="<?php echo add_query_arg(array('sub_page' => 'assignments_gradebooks')); ?>"><?php _e('Assignments Gradebooks'); ?></a>
    </nav>

    <div class="tutor_admin_gradebook_list">

		<?php tutor_alert(null, 'success'); ?>

		<?php
		$gradebooks = tutils()->get_gradebooks('assignment');
		if (tutils()->count($gradebooks)){
			?>
            <table class="gradebook-list-table widefat striped">
                <tr>
                    <th><?php _e('Grade Name', 'tutor-pro'); ?></th>
                    <th><?php _e('Grade Point', 'tutor-pro'); ?></th>
                    <th><?php _e('Range %', 'tutor-pro'); ?></th>
                    <th>#</th>
                </tr>
				<?php foreach ($gradebooks as $gradebook){
				    $config = maybe_unserialize($gradebook->grade_config);
					?>
                    <tr>
                        <td>
                            <span class="gradename-bg" style="background-color: <?php echo tutils()->array_get('grade_color', $config); ?>;" >
                                <?php echo $gradebook->grade_name; ?>
                            </span>
                        </td>
                        <td><?php echo $gradebook->grade_point; ?></td>
                        <td><?php echo $gradebook->percent_from.'-'.$gradebook->percent_to; ?></td>
                        <td>Delete</td>
                    </tr>
					<?php
				} ?>
            </table>
			<?php
		}
		?>

    </div>

</div>