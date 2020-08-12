jQuery(document).ready(function($)
{
    const image=function(course_id, cert_hash, view_url)
    {                
        // Open the data url in new window
        this.view=url=>
        {
            window.location.assign(view_url);
            
            // var newTab = window.open();
            // newTab.document.body.innerHTML='<img src="'+url+'"/>';
        }

        // Convert data url to octet stream
        // and Show image download dialogue
        this.download=(url, width, height)=>
        {
            var doc = new window.jsPDF({unit:'px', orientation:(width>height ? 'l' : 'p')});
            doc.addImage(url, 'jpeg', 0, 0);
            doc.save('certificate.pdf');
        }

        // Set scale of the canvas according to water mark dimension
        this.re_scale_canvas=(canvas, width, height)=>
        {
            var new_canvas = document.createElement('canvas');
            new_canvas.width = width;
            new_canvas.height = height;

            var context = new_canvas.getContext('2d');
            context.drawImage(canvas, 0, 0, canvas.width, canvas.height, 0, 0, new_canvas.width, new_canvas.height);

            return new_canvas;
        }

        this.store_certificate=(data_url, callback)=>
        {
            $.get('?tutor_action=check_if_certificate_generated&cert_hash='+cert_hash, stored=>
            {
                if(stored=='yes')
                {
                    // No need to upload again if already stored
                    callback(true);
                    return;
                }

                var form_data = new FormData();
                form_data.append('tutor_action', 'store_certificate_image');
                form_data.append('cert_hash', cert_hash);
                form_data.append('certificate_image', data_url);

                $.ajax
                ({
                    url         : window.location.href,
                    type        : 'POST',
                    data        : form_data,
                    processData : false,
                    contentType : false,
                    success     : response=>callback(response=='ok'),
                    error       : ()=>callback(false)
                });
            });
        }

        // Call various method like image converter and after action
        this.dispatch_conversion_methods=(action, iframe_document, callback)=>
        {
            var body = iframe_document.getElementsByTagName('body')[0];
            var water_mark = iframe_document.getElementById('watermark');

            var width = water_mark.offsetWidth;
            var height = water_mark.offsetHeight;

            // Now set this dimension body
            body.style.display  = 'inline-block';
            body.style.overflow = 'hidden';
            body.style.width    = width+'px';
            body.style.height   = height+'px';

            // Now capture the iframe using library
            var container = iframe_document.getElementsByTagName('body')[0];
            html2canvas(container).then(canvas=> 
            {
                // var re_canvas = this.re_scale_canvas(canvas, 852, ((height/width)*852));
                var re_canvas = this.re_scale_canvas(canvas, width, height);
                var data_url = re_canvas.toDataURL('image/png');

                // Store the blob on server
                this.store_certificate(data_url, success=>
                {
                    // Dispatch proper action method
                    success ? this[action](data_url, width, height) : alert('Something Went Wrong.');
                    callback();
                });
            });
        }

        // Fetch certificate html from server
        // and initialize converters
        this.init_render_certificate=(action, callback)=>
        {
            var certificate_url = '?tutor_action=generate_course_certificate&course_id='+course_id;

            // Get the HTML from server
            $.get(certificate_url, html=>
            {
                // We need to put the html into iframe to make the certificate styles isolated from parent document
                // Otherwise style might be overridden/influenced
                var iframe = document.createElement('iframe');
                iframe.style.position='absolute';
                iframe.style.left='-999999px';
                document.getElementsByTagName('body')[0].appendChild(iframe);

                var iframe_document = iframe.contentWindow || iframe.contentDocument.document || iframe.contentDocument;
                iframe_document = iframe_document.document;

                // Render the html in iframe
                iframe_document.open();
                iframe_document.write(html);
                iframe_document.close();

                iframe.onload=()=>this.dispatch_conversion_methods(action, iframe_document, callback);
            });
        }
    }

    // Instantiate image processor for this scope
    var downloader_btn = $('#tutor-download-certificate-pdf');
    var downloader_btn_from_preview = $('#tutor-pro-certificate-download-pdf');
    var downloader = downloader_btn.length>0 ? downloader_btn : downloader_btn_from_preview;
    
    // Configure working state
    var loading_ = $('<div style="text-align:center;margin:15px 0;">Processing . . .</div>').hide();
    downloader.parent().append(loading_);

    var viewer_button = $('#tutor-view-certificate-image');

    var course_id = downloader.data('course_id');
    var cert_hash = downloader.data('cert_hash');
    var view_url = viewer_button.data('href');

    var image_processor = new image(course_id, cert_hash, view_url);

    // register event listener for course page
    downloader_btn.add(viewer_button).add(downloader_btn_from_preview).click(function(event)
    {
        // Prevent default action
        event.preventDefault();

        // Avoid repetitive click
        if(!loading_.is('visible'))
        {
            // Set state as work in progress
            loading_.show();

            // Invoke the render method according to action type 
            var action = $(this).attr('id')=='tutor-view-certificate-image' ? 'view' : 'download';
            
            image_processor.init_render_certificate(action, ()=>loading_.hide());
        }
    });
    
    // Register listeners for certificate preview page
    $('#tutor-pro-certificate-download-image').click(function()
    {
        var downloader = $('#tutor-pro-certificate-preview');
        var src = downloader.attr('src');

        var a = document.createElement('A');
        a.href = src.replace("image/png", "image/octet-stream");
        a.download = 'certificate.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });
});