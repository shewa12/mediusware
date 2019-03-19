jQuery(document).ready(function($){
    'use strict';


    $(document).on('click', '.certificate-template', function(){

        $('.certificate-template').removeClass('selected-template');
        $(this).addClass('selected-template');
    });


});
