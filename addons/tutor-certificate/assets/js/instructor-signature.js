jQuery(document).ready(function($)
{
    $('#tutor_pro_custom_signature_file_uploader').click(function(e){
        e.preventDefault();
        $(this).prev().trigger('click');
    }).prev().change(function(e){
        
        var files = e.target.files;

        if(!files || files.length==0){
            // Make sure file selected 
            return;
        }

        
        var img = $(this).parent().find('img');
        
        var reader = new FileReader();
        reader.onload = function(e) {
            img.attr('src', e.target.result);
        }
        reader.readAsDataURL(files[0]);
    });

    $('#tutor_pro_custom_signature_file_deleter').click(function(e){
        e.preventDefault();

        var parent = $(this).parent();

        parent.find('input').val('');
        parent.find('img').removeAttr('src');
    });
});