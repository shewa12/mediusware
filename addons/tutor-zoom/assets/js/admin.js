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


        $(document).on('click', '.tutor-create-zoom-meeting-btn', function (e) {
            e.preventDefault();
    
            var $that = $(this);
            var topic_id = $(this).attr('data-topic-id');
            var course_id = $('#post_ID').val();
    
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: { topic_id, course_id, action: 'tutor_zoom_meeting_modal_content' },
                beforeSend: function () {
                    $that.addClass('tutor-updating-message');
                },
                success: function (data) {
                    $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                    $('.tutor-lesson-modal-wrap').attr('data-topic-id', topic_id).addClass('show');
    
                    $(document).trigger('assignment_modal_loaded', { topic_id: topic_id, course_id: course_id });
    
                    tinymce.init(tinyMCEPreInit.mceInit.course_description);
                    tinymce.execCommand('mceRemoveEditor', false, 'tutor_assignments_modal_editor');
                    tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
                },
                complete: function () {
                    quicktags({ id: "tutor_assignments_modal_editor" });
                    $that.removeClass('tutor-updating-message');
                }
            });
        });
    
        $(document).on('click', '.open-tutor-zoom-meeting-modal', function (e) {
            e.preventDefault();
    
            var $that = $(this);
            var assignment_id = $that.attr('data-assignment-id');
            var topic_id = $that.attr('data-topic-id');
            var course_id = $('#post_ID').val();
    
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: { assignment_id: assignment_id, topic_id: topic_id, course_id: course_id, action: 'tutor_load_assignments_builder_modal' },
                beforeSend: function () {
                    $that.addClass('tutor-updating-message');
                },
                success: function (data) {
                    $('.tutor-lesson-modal-wrap .modal-container').html(data.data.output);
                    $('.tutor-lesson-modal-wrap').attr({ 'data-assignment-id': assignment_id, 'data-topic-id': topic_id }).addClass('show');
    
                    $(document).trigger('assignment_modal_loaded', { assignment_id: assignment_id, topic_id: topic_id, course_id: course_id });
    
                    tinymce.init(tinyMCEPreInit.mceInit.course_description);
                    tinymce.execCommand('mceRemoveEditor', false, 'tutor_assignments_modal_editor');
                    tinyMCE.execCommand('mceAddEditor', false, "tutor_assignments_modal_editor");
                },
                complete: function () {
                    quicktags({ id: "tutor_assignments_modal_editor" });
                    $that.removeClass('tutor-updating-message');
                }
            });
        });
    });
})(jQuery);