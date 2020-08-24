<?php
namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Course_Duplicator{
    private $necessary_post_columns=[
        'post_author',
        'post_content',
        'post_title',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_content_filtered',
        'menu_order',
        'post_type',
        'post_mime_type'
    ];

    private $necessary_child_types=[
        'topics',
        'lesson',
        'tutor_quiz'
    ];

    private $allowed_user_role=[
        'administrator',
        'editor',
        'tutor_instructor'
    ];

    // Store duplicated IDs here to avoid accidental inifinity recursion.
    private $duplicated_post_ids=[];

    function __construct(){
        add_action('wp_loaded', [$this, 'init_duplicator']);
        add_filter('post_row_actions', [$this, 'register_duplicate_button'], 10, 2);
    }

    public function register_duplicate_button($actions, $post){
        if($post->post_type==tutor()->course_post_type){
            $actions[]='<a href="?tutor_action=duplicate_course&course_id='.$post->ID.'" aria-label="Duplicate">
                Duplicate
            </a>';
        }
        
        return $actions;
    }

    public function init_duplicator(){
        $action = $_GET['tutor_action'] ?? '';
        $id = $_GET['course_id'] ?? '';

        if($action!=='duplicate_course' || !is_numeric($id) || $id==0){
            // This request is for something else or invalid
            return;
        }

        if($this->is_valid_user_role())
        {
            // Duplicate the post
            $new_post_id = $this->duplicate_post($id);
            
            if($new_post_id){
                $edit_link = get_edit_post_link($new_post_id, null);
                header('Location: '.$edit_link);
                exit;
            }
        }
        
        exit('You are not allowed for this action.');
    }

    private function is_valid_user_role() {
        $current_user = wp_get_current_user();

        if(is_object($current_user) && property_exists($current_user, 'roles')){
            $roles = (array)$current_user->roles;
            $different = array_diff($this->allowed_user_role, $roles);
            $exist_in_allowed = count($different)<count($this->allowed_user_role);

            return $exist_in_allowed;
        }
    }

    private function duplicate_post($post_id, $absolute_course_id=null, $new_parent_id=0){

        $post = get_post($post_id);
        $post = is_object($post) ? (array)$post : null;

        if(!$post){
            // Return right from here
            return false;
        }
        
        // Create new post using the old values
        $post = $this->strip_unnecessary_columns($post);
        $post['post_parent']=$new_parent_id;
        $new_id = wp_insert_post($post);

        // Duplicate post meta
        $this->duplicate_post_meta($post_id, $new_id, $absolute_course_id);

        // Assign taxonomy
        $this->assign_post_taxonomy($post_id, $new_id, 'course-category');
        $this->assign_post_taxonomy($post_id, $new_id, 'course-tag');

        // Set it as done
        $this->duplicated_post_ids[]=(int)$post_id;
        
        // Now duplicate childs like topic, lesson, etc.
        $childs = $this->get_child_post_ids($post_id);

        foreach($childs as $child_id){
            if(in_array((int)$child_id, $this->duplicated_post_ids)){
                // Avoid accidental infinity recursion
                continue;
            }
            
            $this->duplicate_post($child_id, ($absolute_course_id ?? $new_id), $new_id);
        }

        return $new_id;
    }

    private function duplicate_post_meta($old_id, $new_id, $absolute_course_id){

        // Get existing meta from old post
        $meta_array = get_post_meta($old_id);
        !is_array($meta_array) ? $meta_array=[] : 0;

        // Add these meta to newly created post
        foreach($meta_array as $name=>$value){

            // Convert to singular value from second level array
            $value = is_array($value) ? ($value[0] ?? '') : '';
            $value = is_serialized($value) ? unserialize($value) : $value;

            if($absolute_course_id){
                // Replace old course ID meta with new one
                $name=='_tutor_course_id_for_lesson' ? $value=$absolute_course_id : 0;
            }

            update_post_meta($new_id, $name, $value);
        }
    }

    private function assign_post_taxonomy($old_id, $new_id, $taxonomy){
        $old_terms = get_the_terms($old_id, $taxonomy);
        !is_array($old_terms) ? $old_terms=[] : 0;
        
        // Extract terms IDs
        $term_ids = [];
        foreach($old_terms as $term){
            $term_ids[]=$term->term_id;
        }

        // Assign the terms
        count($term_ids)>0 ? wp_set_post_terms($new_id, $term_ids, $taxonomy) : 0;
    }

    private function get_child_post_ids($parent_id){
        $children = get_children(['post_parent'=>$parent_id, 'post_type'=>$this->necessary_child_types]);
        !is_array($children) ? $children=[] : 0;
        
        $child_ids = [];
        foreach($children as $child_post){
            is_object($child_post) ? $child_ids[]=(int)$child_post->ID : 0;
        }

        return $child_ids;
    }

    private function strip_unnecessary_columns(array $post){
        $new_array = [];

        foreach($post as $column=>$value){
            if(in_array($column, $this->necessary_post_columns)){
                // Kepp only if it exist in ncessary column list
                $new_array[$column]=$value;
            } 
        }

        return $new_array;
    }
}