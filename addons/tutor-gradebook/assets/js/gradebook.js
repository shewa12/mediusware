jQuery(document).ready(function($){
    'use strict';



    /*
    $(document).on('click', '.tutor-rating-delete-link', function (e) {
        e.preventDefault();

        var $that= $(this);
        $.ajax({
            url : ajaxurl,
            type : 'POST',
            data : {review_id : $that.attr('data-rating-id'), action : 'tutor_review_delete' },
            beforeSend: function () {
                $that.addClass('updating-message');
            },
            success: function (data) {
                if (data.success){
                    $that.closest('tr').remove();
                }
            },
            complete: function () {
                $that.removeClass('updating-message');
            }
        });
    });
    */



});