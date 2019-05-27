jQuery(document).ready(function($){
    'use strict';


    $(document).on('click', '.certificate-template', function(){
        $('.certificate-template').removeClass('selected-template');
        $(this).addClass('selected-template');
    });


    $(document).on('click', '.install-tutor-button', function(e){
        e.preventDefault();

        var $btn = $(this);

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {install_plugin: 'tutor', action: 'install_tutor_plugin'},
            beforeSend: function(){
                $btn.addClass('updating-message');
            },
            success: function (data) {
                $('.install-tutor-button').remove();
                $('#tutor_install_msg').html(data);
            },
            complete: function () {
                $btn.removeClass('updating-message');
            }
        });
    });




});
