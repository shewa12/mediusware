<?php


namespace TUTOR_CERT;

class Disable_Certificate{

    private $meta_box_id = 'tutor_pro_enable_course_certificate';

    function __construct($register=true){
        if($register){
            add_action('add_meta_boxes', [$this, 'register_meta_box']);
            add_action('save_post', [$this, 'save_meta']);
        }
    }

    public function register_meta_box(){
        
        add_meta_box($this->meta_box_id, __('Certificate for this Course', 'tutor-pro'), [$this, 'meta_box_content'], 'courses');
    }

    public function meta_box_content($post){
        
        $is_enabled = $this->is_enabled($post->ID);

        ?>
            <label>
                <input 
                    type="radio" 
                    name="<?php echo $this->meta_box_id; ?>" 
                    <?php echo $is_enabled ? ' checked="checked" ' : '';?>
                    value="1"/> <?php _e('Enable', 'tutor-pro'); ?>
            </label>
            &nbsp;
            &nbsp;
            <label>
                <input 
                    type="radio" 
                    name="<?php echo $this->meta_box_id; ?>" 
                    <?php echo !$is_enabled ? ' checked="checked" ' : '';?>
                    value="0"/> <?php _e('Disable', 'tutor-pro'); ?>
            </label>
        <?php
    }

    public function save_meta($post_id){

        if(isset($_POST[$this->meta_box_id])){
            update_post_meta($post_id, $this->meta_box_id, $_POST[$this->meta_box_id]);
        }
    }

    public function is_enabled($post_id){
        $value = get_post_meta($post_id, $this->meta_box_id, true);

        // We should consider empty string and null as enabled for backward compatibility.
        // explicit 0 will mark it as disabled

        return ($value==1 || $value==='' || $value===null);
    }
}