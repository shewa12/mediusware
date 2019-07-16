<?php
$assignment_id = $post->ID;
?>

<form class="tutor_assignment_modal_form">
	<input type="hidden" name="action" value="tutor_modal_create_or_update_assignment">
	<input type="hidden" name="assignment_id" value="<?php echo $post->ID; ?>">
	<input type="hidden" name="current_topic_id" value="<?php echo $topic_id; ?>">

    <div class="assignment-modal-form-wrap">
<!--	<div class="tutor-option-field-row">-->

		<?php do_action('tutor_assignment_edit_modal_form_before', $post); ?>

		<div class="tutor-option-field-row">
			<div class="tutor-option-field tutor-assignment-modal-title-wrap">
				<input type="text" name="assignment_title" value="<?php echo $post->post_title; ?>" placeholder="<?php _e('Assignment title', 'tutor'); ?>">
			</div>
		</div>

		<div class="assignment-modal-field-row">
			<div class="assignment-modal-field">
				<?php
				wp_editor($post->post_content, 'tutor_assignments_modal_editor', array( 'editor_height' => 150));
				?>
			</div>
		</div>

		<?php do_action('tutor_assignment_edit_modal_form_after', $post); ?>
<!--	</div>-->


    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Attachments', 'tutor-pro'); ?></label>
        </div>
        <div class="tutor-option-field">
            <?php $assignment_attachments = maybe_unserialize(get_post_meta($post->ID,'_tutor_assignment_attachments', true));  ?>

            <div id="assignment-attached-file">

                <?php
                if (tutor_utils()->count($assignment_attachments)){
                    foreach ($assignment_attachments as $assignment_attachment){
                        if ($assignment_attachment) {

                            $attachment_name =  get_post_meta( $assignment_attachment, '_wp_attached_file', true );
	                        $attachment_name = substr($attachment_name, strrpos($attachment_name, '/')+1);
	                        ?>
                            <div class="tutor-individual-attachment-file">
                                <p class="attachment-file-name"><?php echo $attachment_name; ?></p>
                                <input type="hidden" name="tutor_assignment_attachments[]" value="<?php echo $assignment_attachment; ?>">
                                <a href="javascript:;" class="remove-assignment-attachment-a text-muted"> &times; <?php _e('Remove', 'tutor-pro'); ?></a>
                            </div>
	                        <?php
                        }
                    }
                }

                ?>


            </div>

            <p><a href="javascript:;" class="add-assignment-attachments"><?php _e('Add attachments', 'tutor'); ?></a></p>

        </div>
    </div>


    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for="">Time Duration</label>
        </div>

        <div class="tutor-option-field">
            <div class="tutor-option-gorup-fields-wrap">
                <div class="tutor-option-group-field">
                    <input type="number" name="assignment_option[time_duration][value]" value="<?php echo tutor_utils()->get_assignment_option($assignment_id, 'time_duration.value', 0); ?>">
                </div>
                <div class="tutor-option-group-field">
                    <select name="assignment_option[time_duration][time]">
                        <option value="weeks" <?php selected('weeks', tutor_utils()->get_assignment_option($assignment_id, 'time_duration.time')); ?>>Weeks</option>
                        <option value="days"  <?php selected('days', tutor_utils()->get_assignment_option($assignment_id, 'time_duration.time')); ?>>Days</option>
                        <option value="hours"  <?php selected('hours', tutor_utils()->get_assignment_option($assignment_id, 'time_duration.time')); ?>>Hours</option>
                    </select>
                </div>
            </div>

            <p class="desc">Assignment time duration, set 0 for no limit.</p>
        </div>
    </div>

    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Mark', 'tutor-pro'); ?></label>
        </div>
        <div class="tutor-option-field">
            <input type="number" name="assignment_option[total_mark]" value="<?php echo tutor_utils()->get_assignment_option($assignment_id, 'total_mark', 10) ?>">
            <p class="desc"><?php _e('Maximum mark for the student will have', 'tutor-pro'); ?></p>
        </div>
    </div>

    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Passing Mark', 'tutor-pro'); ?></label>
        </div>
        <div class="tutor-option-field">
            <input type="number" name="assignment_option[pass_mark]" value="<?php echo tutor_utils()->get_assignment_option($assignment_id, 'pass_mark', 5) ?>">
            <p class="desc"><?php _e('Min mark required for the student to pass this assignment.', 'tutor-pro'); ?></p>
        </div>
    </div>

    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Allow to upload files', 'tutor-pro'); ?></label>
        </div>
        <div class="tutor-option-field">
            <input type="number" name="assignment_option[upload_files_limit]" value="<?php echo tutor_utils()->get_assignment_option
            ($assignment_id, 'upload_files_limit', 1) ?>">
            <p class="desc"><?php _e('How many files students can upload in this assignment. set 0 to disable upload file', 'tutor-pro'); ?></p>
        </div>
    </div>

    <div class="tutor-option-field-row">
        <div class="tutor-option-field-label">
            <label for=""><?php _e('Upload File size limit', 'tutor-pro'); ?></label>
        </div>
        <div class="tutor-option-field">
            <input type="number" name="assignment_option[upload_file_size_limit]" value="<?php echo tutor_utils()->get_assignment_option
            ($assignment_id, 'upload_file_size_limit', 2) ?>">
            <p class="desc"><?php echo sprintf(__('Set Maximum Attachment size in %s for upload.', 'tutor-pro'), '<code>MB</code>') ?></p>
        </div>
    </div>

    </div>
	<div class="modal-footer">
		<button type="button" class="tutor-btn update_assignment_modal_btn"><?php _e('Update Assignment', 'tutor'); ?></button>
	</div>
</form>