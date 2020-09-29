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

<style type="text/css">
.tutor-zoom-card-title >h3 {
    font-weight: bold;
} 
.tutor-zoom-card-content {
    display: flex;
}    
.tutor-zoom-card {
    max-width: 620px;
    margin:auto;
}  
.tutor-zoom-card-body {
    background-color:  #fff;
    margin-bottom: 10px;
}  
.tutor-zoom-card-content {
    padding: 10px;
}
.card-icon {
    margin-right: 50px;
}
.card-content li {
    list-style: none;
    font-size: 16px;
    font-weight: bold;
}
.card-content p {
    font-size: 16px;
}
.card-radio {
    display: flex;

}
.card-radio > .single-radio{
    margin-right: 20px;
}
.card-radio > .single-radio:last-child{
    margin-right: 0px;
}
</style>
    <div class="tutor-zoom-card">
        <div class="tutor-zoom-card-title">
            <h3><?php _e('Settings')?></h3>
        </div>
    <?php  foreach($zoom_settings_options as $key => $option):?>
        <div class="tutor-zoom-card-body">
            <div class="tutor-zoom-card-content">
            <?php if ($option['type'] == 'checkbox'):?>    
                <div class="card-icon">
                    <label class="btn-switch">
                        <input type="checkbox" class="hello" value="1" name="<?php echo $this->settings_key.'['.$key.']'; ?>" <?php checked($this->get_settings($key), '1'); ?>>
                        <div class="btn-slider btn-round"></div>
                    </label>
                </div>
                <div class="card-content">
                    <li>
                        <?php echo $option['label']; ?>
                    </li>
                    <p>
                        <?php echo $option['desc']; ?>
                    </p>
                </div>
            <?php elseif($option['type'] == 'select'):?> 

                <div class="card-content">
                    <li><?php echo $option['label']; ?></li>
                    <p>
                        <?php echo $option['desc']; ?>
                    </p>
                    <div class="card-radio">
                        <?php 
                       
                        $name= $this->settings_key.'['.$key.']'; 
                        ?>
                            <?php 
                                foreach($option['options'] as $optKey => $opt) {
                            // $checked = selected(
                            //     $this->get_settings($key),  $optKey
                            // );
                                    $checked ="checked";
                                    echo 
                                    "<div class='single-radio'>
                                    <input type='radio' name='{$name}' value='{$optKey}'
                                     {$checked}>{$opt}
                                    </div>";
                                } 
                            ?>                        
                    </div>  
                </div>                
            <?php endif;?>  
            </div>
        </div>        
    <?php endforeach;?>

    </div>