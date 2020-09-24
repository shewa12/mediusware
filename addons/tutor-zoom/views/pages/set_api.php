<?php
if (!defined('ABSPATH'))
    exit;
?>
<div class="tutor-zoom-api-container">
    <form id="tutor-zoom-settings" action="">
        <?php wp_nonce_field('tutor_zoom_settings') ?>
        <input type="hidden" name="action" value="tutor_save_zoom_api">
        <div class="tutor-zoom-form-container">
            <div class="input-area">
                <h3><?php _e('Setup your Zoom Integration'); ?></h3>
                <p><?php _e('Visit your Zoom account and fetch the API key to connect Zoom with your eLearning website. Go to Zoom Website.'); ?></p>
                <div class="tutor-form-group">
                    <label for="tutor_zoom_api_key"><?php _e('API Key'); ?></label>
                    <input type="text" id="tutor_zoom_api_key" name="<?php echo $this->api_key; ?>[api_key]" value="<?php echo $this->get_api('api_key'); ?>" placeholder="<?php _e('Enter Your Zoom Api Key'); ?>"/>
                </div>
                <div class="tutor-form-group">
                    <label for="tutor_zoom_api_secret"><?php _e('Secret Key'); ?></label>
                    <input type="text" id="tutor_zoom_api_secret" name="<?php echo $this->api_key; ?>[api_secret]" value="<?php echo $this->get_api('api_secret'); ?>" placeholder="<?php _e('Enter Your Zoom Secret Key'); ?>"/>
                </div>
            </div>
            <div class="graphics-area">
                <img src="<?php echo TUTOR_ZOOM()->url.'assets/images/mask-group.png'; ?>" alt="" />
            </div>
        </div>
        <div class="tutor-zoom-button-container">
            <button type="submit" id="save-changes" class="button button-primary"><?php _e('Save Changes'); ?></button>
            <button type="button" id="check-zoom-api-connection" class="button"><?php _e('Check API Connection'); ?></button>
        </div>
    </form>
</div>