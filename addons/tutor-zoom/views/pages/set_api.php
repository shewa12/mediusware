<?php
if (!defined('ABSPATH'))
    exit;
?>
<div class="tutor-zoom-api-container">
    <form id="set-api" action="">
        <div class="tutor-zoom-form-container">
            <div class="input-area">
                <h3><?php _e('Setup your Zoom Integration'); ?></h3>
                <p><?php _e('Visit your Zoom account and fetch the API key to connect Zoom with your eLearning website. Go to Zoom Website.'); ?></p>
                <div class="tutor-form-group">
                    <label for="tutor_zoom_api_key"><?php _e('API Key'); ?></label>
                    <input type="text" id="tutor_zoom_api_key" name="tutor_zoom_api_key" placeholder="<?php _e('Enter Your Zoom Api Key'); ?>"/>
                </div>
                <div class="tutor-form-group">
                    <label for="tutor_zoom_secret_key"><?php _e('Secret Key'); ?></label>
                    <input type="text" id="tutor_zoom_secret_key" name="tutor_zoom_secret_key" placeholder="<?php _e('Enter Your Zoom Secret Key'); ?>"/>
                </div>
            </div>
            <div class="graphics-area">
                <img src="<?php echo TUTOR_ZOOM()->url.'assets/images/mask-group.png'; ?>" alt="" />
            </div>
        </div>
        <div>
            <button type="submit" class="button button-primary"><?php _e('Save Changes'); ?></button>
            <button type="button" class="button"><?php _e('Check API Connection'); ?></button>
        </div>
    </form>
</div>