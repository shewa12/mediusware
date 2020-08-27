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
        
        add_meta_box($this->meta_box_id, __('Enable/Disable Certificate', 'tutor-pro'), [$this, 'meta_box_content'], 'courses');
    }

    public function meta_box_content($post){
        
        ?>
            <label>
                <input 
                    type="checkbox" 
                    name="<?php echo $this->meta_box_id; ?>" 
                    <?php echo $this->is_enabled($post->ID)==1 ? ' checked="checked" ' : '';?>
                    value="1"/> <?php _e('Enable Certificate', 'tutor-pro'); ?>
            </label>
        <?php
    }

    public function save_meta($post_id){

        $is_on = ($_POST[$this->meta_box_id] ?? '')==1;
        
        update_post_meta($post_id, $this->meta_box_id, ($is_on ? 1 : 0));
    }

    public function is_enabled($post_id){
        $value = get_post_meta($post_id, $this->meta_box_id, true);

        // We should consider empty string and null as enabled for backward compatibility.
        // explicit 0 will mark it as disabled

        return ($value==1 || $value==='' || $value===null);
    }
}