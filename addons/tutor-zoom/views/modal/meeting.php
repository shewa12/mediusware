<?php
//$meeting_id = $post->ID;
?>

<form class="tutor-meeting-modal-form">
    <input type="hidden" name="action" value="tutor_zoom_save_meeting">
    <input type="hidden" name="meeting_id" value="<?php echo @$post->ID; ?>">
    <input type="hidden" name="current_topic_id" value="<?php echo @$topic_id; ?>">
    <input type="hidden" name="course_id" value="<?php echo @$post->ID; ?>">

    <div class="meeting-modal-form-wrap">
        <div class="tutor-quiz-builder-group">
            <h4><?php _e('Meeting Host', 'tutor-pro'); ?></h4>
            <div class="tutor-quiz-builder-row">
                <div class="tutor-quiz-builder-col">
                    <select name="" class="meeting-host">
                        <option value="">Host one</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="tutor-quiz-builder-group">
            <h4><?php _e('Meeting Title', 'tutor-pro'); ?></h4>
            <div class="tutor-quiz-builder-row">
                <div class="tutor-quiz-builder-col">
                    <input type="text" name="quiz_option[passing_grade]" value="">
                </div>
            </div>
        </div>

        <div class="tutor-quiz-builder-group">
            <h4><?php _e('Meeting Summery', 'tutor-pro'); ?></h4>
            <div class="tutor-quiz-builder-row">
                <div class="tutor-quiz-builder-col">
                    <textarea type="text" name="quiz_option[passing_grade]" rows="4"></textarea>
                </div>
            </div>
        </div>

        <div class="tutor-quiz-builder-group">
            <div class="tutor-quiz-builder-row">
                <div class="tutor-quiz-builder-col meeting-time">
                    <h4><?php _e('Meeting Time', 'tutor-pro'); ?></h4>
                    <div class="tutor-quiz-builder-row">
                        <div class="tutor-quiz-builder-col meeting-time-col">
                            <div class="date-range-input">
                                <input type="text" class="tutor_zoom_datepicker" value="" autocomplete="off" placeholder="<?php echo date('Y-m-d'); ?>">
                                <i class="tutor-icon-calendar"></i>
                            </div>
                        </div>
                        <div class="meeting-time-separator">-</div>
                        <div class="tutor-quiz-builder-col">
                            <div class="date-range-input">
                                <input type="text" class="tutor_zoom_timepicker" value="" autocomplete="off" placeholder="08:30 PM">
                                <i class="tutor-icon-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tutor-quiz-builder-col">
                    <h4><?php _e('Meeting Duration', 'tutor-pro'); ?></h4>
                    <div class="tutor-quiz-builder-row meeting-duration-row">
                        <div class="tutor-quiz-builder-col">
                            <input type="number" name="" value="" autocomplete="off" placeholder="30">
                        </div>
                        <div class="tutor-quiz-builder-col">
                            <select name="">
                                <option value="minutes" selected="selected"><?php _e('Minutes', 'tutor-pro'); ?></option>
                                <option value="hours"><?php _e('Minutes', 'Hours', 'tutor-pro'); ?></option>
                                <option value="days"><?php _e('Minutes', 'Days', 'tutor-pro'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tutor-quiz-builder-group">
            <div class="tutor-quiz-builder-row">
                <div class="tutor-quiz-builder-col meeting-time">
                    <h4><?php _e('Time Zone', 'tutor-pro'); ?></h4>
                    <div class="tutor-quiz-builder-row">
                        <div class="tutor-quiz-builder-col">
                            <select name="">
                                <option value="minutes" selected="selected"><?php _e('Minutes', 'tutor-pro'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="tutor-quiz-builder-col">
                    <h4><?php _e('Auto Recording', 'tutor-pro'); ?></h4>
                    <div class="tutor-quiz-builder-row">
                        <div class="tutor-quiz-builder-col">
                            <select name="">
                                <option value="no" selected="selected"><?php _e('No Recordings', 'tutor-pro'); ?></option>
                                <option value="local"><?php _e('Local', 'Hours', 'tutor-pro'); ?></option>
                                <option value="cloud"><?php _e('Cloud', 'Days', 'tutor-pro'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tutor-quiz-builder-group">
            <h4><?php _e('Password', 'tutor-pro'); ?></h4>
            <div class="tutor-quiz-builder-row">
                <div class="tutor-quiz-builder-col">
                    <div class="date-range-input">
                        <input type="text" class="" value="" autocomplete="off" placeholder="Create a Password">
                        <i class="tutor-icon-lock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="tutor-btn update_meeting_modal_btn"><?php _e('Save Meeting', 'tutor-pro'); ?></button>
    </div>
</form>