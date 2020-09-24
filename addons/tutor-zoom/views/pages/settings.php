<?php
if (!defined('ABSPATH'))
    exit;

    $zoom_settings_options = apply_filters('zoom_settings_options', array(
        'join_before_host' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Join Before Host', 'tutor-pro'),
            'desc'      	=> __('Join meeting before host start the meeting. Only for scheduled or recurring mettings', 'tutor-pro'),
        ),
        'host_video' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Host video', 'tutor-pro'),
            'desc'      	=> __('By enabling this option, the student will be able to verify and share their certificates URL which is publicly accessible', 'tutor-pro'),
        ),
        'participants_video' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Participants video', 'tutor-pro'),
            'desc'      	=> __('By enabling this option, the student will be able to verify and share their certificates URL which is publicly accessible', 'tutor-pro'),
        ),
        'mute_participants' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Mute Participants', 'tutor-pro'),
            'desc'      	=> __('By enabling this option, the student will be able to verify and share their certificates URL which is publicly accessible', 'tutor-pro'),
        ),
        'enforce_login' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Enforce Login', 'tutor-pro'),
            'desc'      	=> __('By enabling this option, the student will be able to verify and share their certificates URL which is publicly accessible', 'tutor-pro'),
        ),
        'recordings' => array(
            'type'      	=> 'select',
            'label'     	=> __('Recording Settings', 'tutor-pro'),
            'options'     	=> array(
                'no'    => __('No Recording', 'tutor-pro'),
                'local' => __('Local', 'tutor-pro'),
                'cloud' => __('Cloud', 'tutor-pro'),
            ),
            'desc'      	=> '',
        ),
    ));
?>
<div class="tutor-zoom-settings-container">
    <h3><?php _e('Settings'); ?></h3>
    <div class="tutor-zoom-settings-option-container">
        <?php 
        foreach($zoom_settings_options as $key => $option) { ?>
            <div class="settings-option-item">
                <?php if ($option['type'] == 'checkbox') { ?>
                    <label class="btn-switch">
                        <input type="checkbox" class="hello" value="1" name="<?php echo $key; ?>" checked="checked">
                        <div class="btn-slider btn-round"></div>
                    </label>
                    <div class="option-label"><?php echo $option['label']; ?></div>
                    <div class="option-desc"><?php echo $option['desc']; ?></div>
                <?php 
                } elseif ($option['type'] == 'select') { ?>
                    <label class="select-label"><?php echo $option['label']; ?></label>
                    <select class="select-control" name="<?php echo $key; ?>">
                        <?php foreach($option['options'] as $key => $option) {
                            echo "<option value='{$key}'>{$option}</option>";
                        } ?>
                    </select>

                <?php } ?>
            </div>
        <?php
        } ?>
    </div>
</div>