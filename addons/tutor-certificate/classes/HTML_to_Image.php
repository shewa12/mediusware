<?php

/* 
    All about HTML to Image conversion.
*/

namespace TUTOR_CERT;

class HTML_to_Image{
    public function register_script(){
        add_action('wp_enqueue_scripts', array($this, 'load_script'));
    }

    public function load_script(){
        if(is_single_course() || !empty($_GET['cert_hash'])){
            $base = tutor_pro()->url.'addons/tutor-certificate/assets/js/';
            
            wp_enqueue_script('html-to-image-converter', $base.'html2canvas.min.js');
            wp_enqueue_script('html-to-image-js-pdf', $base.'js-pdf.js');
            wp_enqueue_script('html-to-image', $base.'html-to-image.js');
        }
    }
}