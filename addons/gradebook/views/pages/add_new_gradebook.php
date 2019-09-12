
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Grade Books', 'tutor-pro'); ?>  </h1>
	<?php
	if (!$sub_page){ ?>
        <a href="<?php echo admin_url('admin.php?page=tutor_gradebook&sub_page=add_new_gradebook'); ?>" class="page-title-action"><i class="tutor-icon-plus"></i>
			<?php _e('Add New Grade Book', 'tutor-pro'); ?>
        </a>
		<?php
	}

	tutor_alert(null, 'success');
	?>

    <hr class="wp-header-end">

    <form action="" id="add-gradebook-form" method="post">
        <input type="hidden" name="tutor_action" value="add_new_gradebook">
		<?php
		tutor_nonce_field();

		$errors = apply_filters('tutor_gradebook_validation_error', array());

		if (is_array($errors) && count($errors)){
			echo '<div class="tutor-alert-warning"><ul class="tutor-required-fields">';
			foreach ($errors as $error_key => $error_value){
				echo "<li>{$error_value}</li>";
			}
			echo '</ul></div>';
		}
		?>

		<?php do_action('tutor_add_new_instructor_form_fields_before'); ?>

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
					<?php _e('Grade Name', 'tutor-pro'); ?>
                    <span class="tutor-required-fields">*</span>
                </label>
            </div>
            <div class="tutor-option-field">
                <input type="text" name="grade_name" value="<?php echo tutor_utils()->input_old('grade_name'); ?>" placeholder="<?php _e('Grade Name',
                    'tutor-pro'); ?>">
            </div>
        </div>

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
				    <?php _e('Grade Point', 'tutor-pro'); ?>
                    <span class="tutor-required-fields">*</span>
                </label>
            </div>
            <div class="tutor-option-field">
                <input type="text" name="grade_point" value="<?php echo tutor_utils()->input_old('grade_point'); ?>" placeholder="<?php _e('Grade Point',
				    'tutor-pro'); ?>">
            </div>
        </div>

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
				    <?php _e('Number Percent From', 'tutor-pro'); ?>
                    <span class="tutor-required-fields">*</span>
                </label>
            </div>
            <div class="tutor-option-field">
                <input type="text" name="number_percent_from" value="<?php echo tutor_utils()->input_old('number_percent_from'); ?>" placeholder="<?php _e('Number Percent From', 'tutor-pro'); ?>">
            </div>
        </div>

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
				    <?php _e('Number Percent To', 'tutor-pro'); ?>
                    <span class="tutor-required-fields">*</span>
                </label>
            </div>
            <div class="tutor-option-field">
                <input type="text" name="number_percent_to" value="<?php echo tutor_utils()->input_old('number_percent_to'); ?>" placeholder="<?php _e('Number Percent To', 'tutor-pro'); ?>">
            </div>
        </div>

        <!--
        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
				    <?php /*_e('Grade For', 'tutor-pro'); */?>
                    <span class="tutor-required-fields">*</span>
                </label>
            </div>
            <div class="tutor-option-field">
                <select name="grade_for">
                    <option value="quiz"><?php /*_e('Quiz', 'tutor'); */?></option>
                    <option value="assignment"><?php /*_e('Assignment', 'tutor'); */?></option>
                    <option value="final"><?php /*_e('Final', 'tutor'); */?></option>
                </select>
            </div>
        </div>
        -->

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="">
				    <?php _e('Grade Color', 'tutor-pro'); ?>
                    <span class="tutor-required-fields">*</span>
                </label>
            </div>
            <div class="tutor-option-field">
                <input type="text" class="tutor_colorpicker" name="grade_config[grade_color]" value="<?php echo tutils()->input_old('grade_config.grade_color'); ?>" >
            </div>
        </div>

        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label"></div>

            <div class="tutor-option-field">
                <div class="tutor-form-group tutor-reg-form-btn-wrap">
                    <button type="submit" name="tutor_add_gradebook_btn" value="register" class="tutor-button tutor-button-primary">
                        <i class="tutor-icon-plus-square-button"></i>
						<?php _e('Add new Grade', 'tutor-pro'); ?></button>
                </div>
            </div>
        </div>

    </form>


</div>
