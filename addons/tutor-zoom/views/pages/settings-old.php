<?php
if (!defined('ABSPATH'))
    exit;

    $zoom_settings_options = apply_filters('zoom_settings_options', array(
        'join_before_host' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Join Before Host', 'tutor-pro'),
            'desc'      	=> __('Join meeting before host start the meeting. Only for scheduled or recurring meetings.', 'tutor-pro'),
        ),
        'host_video' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Host video', 'tutor-pro'),
            'desc'      	=> __('Start video when host join meeting', 'tutor-pro'),
        ),
        'participants_video' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Participants video', 'tutor-pro'),
            'desc'      	=> __('Start video when participants join meeting.', 'tutor-pro'),
        ),
        'mute_participants' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Mute Participants', 'tutor-pro'),
            'desc'      	=> __('Mutes Participants when entering the meeting.', 'tutor-pro'),
        ),
        'enforce_login' => array(
            'type'      	=> 'checkbox',
            'label'     	=> __('Enforce Login', 'tutor-pro'),
            'desc'      	=> __('Only logged in users in Zoom App can join this Meeting', 'tutor-pro'),
        ),
        'auto_recording' => array(
            'type'      	=> 'select',
            'label'     	=> __('Auto Recording', 'tutor-pro'),
            'options'     	=> array(
                'no'    => __('No Recordings', 'tutor-pro'),
                'local' => __('Local', 'tutor-pro'),
                'cloud' => __('Cloud', 'tutor-pro'),
            ),
            'desc'      	=> __('Set what type of auto recording feature you want to add. Default is none.', 'tutor-pro'),
        ),
    ));
?>
<div class="tutor-zoom-settings-container">
    <h3><?php _e('Settings'); ?></h3>
    <div class="tutor-zoom-settings-option-container">
        <form id="tutor-zoom-settings" action="">
            <?php wp_nonce_field('tutor_zoom_settings') ?>
            <input type="hidden" name="action" value="tutor_save_zoom_settings">
            <?php 
            foreach($zoom_settings_options as $key => $option) { ?>
                <div class="settings-option-item">
                    <?php if ($option['type'] == 'checkbox') { ?>
                        <label class="btn-switch">
                            <input type="checkbox" class="hello" value="1" name="<?php echo $this->settings_key.'['.$key.']'; ?>" <?php checked($this->get_settings($key), '1'); ?>>
                            <div class="btn-slider btn-round"></div>
                        </label>
                        <div class="option-label"><?php echo $option['label']; ?></div>
                        <div class="option-desc"><?php echo $option['desc']; ?></div>
                    <?php 
                    } elseif ($option['type'] == 'select') { ?>
                        <div class="option-label label-select">
                            <label class="select-label"><?php echo $option['label']; ?></label>
                            <select class="select-control" name="<?php echo $this->settings_key.'['.$key.']'; ?>">
                                <?php foreach($option['options'] as $optKey => $opt) {
                                    $checked = selected($this->get_settings($key),  $optKey);
                                    echo "<option value='{$optKey}' {$checked}>{$opt}</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="option-desc"><?php echo $option['desc']; ?></div>
                    <?php } ?>
                </div>
            <?php
            } ?>
        </form>
    </div>
</div>