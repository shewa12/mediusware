<?php

/* 
    All about HTML to Image conversion.
*/

namespace TUTOR_CERT;

class HTML_to_Image
{
    private function need_script()
    {
        $q = get_queried_object();

        return (is_object($q) && $q->post_type==tutor()->course_post_type);
    }

    public function register_script()
    {
        add_action('wp_enqueue_scripts', [$this, 'load_script']);
    }

    public function load_script()
    {
        if($this->need_script())
        {
            $base = tutor_pro()->url.'addons/tutor-certificate/assets/js/';
            
            wp_enqueue_script('html-to-image-converter', $base.'html2canvas.min.js');
            wp_enqueue_script('html-to-image', $base.'html-to-image.js');
            wp_enqueue_script('html-to-image-js-pdf', $base.'js-pdf.js');
        }
    }
}