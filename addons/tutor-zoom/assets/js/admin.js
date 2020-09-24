(function ($) {
    'use strict';
    $(document).ready(function () {

        $('#tutor-zoom-settings').on('change', '.btn-switch, .select-control', function (e) {
            $(this).closest('form').submit();
        });

        $('#tutor-zoom-settings').submit(function (e) {
            e.preventDefault();
            var $form = $(this);
            var data = $form.serialize();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $form.find('#save-changes').addClass('tutor-updating-message');
                    $form.parent().append('<span id="saving-msg">Saving...</span>');
                },
                success: function (data) {
                    if (data.success) {
                    }
                },
                complete: function () {
                    $form.find('#save-changes').removeClass('tutor-updating-message');
                    $form.parent().find('#saving-msg').remove();
                }
            });
        });

        $('#check-zoom-api-connection').click(function (e) {
            e.preventDefault();
            var $that = $(this);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {action: 'tutor_check_api_connection'},
                beforeSend: function () {
                    $that.addClass('tutor-updating-message');
                },
                success: function (result) {
                    alert(result);
                },
                complete: function () {
                    $that.removeClass('tutor-updating-message');
                }
            });
        });
    });
})(jQuery);