jQuery(document).ready(function($)
{
    const image=function()
    {                
        // Open the data url in new window
        this.view=url=>
        {
            var newTab = window.open();
            newTab.document.body.innerHTML='<img src="'+url+'"/>';
        }

        // Convert data url to octet stream
        // and Show image download dialogue
        this.download=(url, width, height)=>
        {
            var doc = new window.jsPDF({unit:'px', orientation:(width>height ? 'l' : 'p')});
            doc.addImage(url, 'png', 0, 0);
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

        // Call various method like image converter and after action
        this.dispatch_methods=(action, iframe, iframe_document, callback)=>
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

                // Dispatch proper action method
                this[action](re_canvas.toDataURL('image/png'), width, height);

                callback();
            });
        }

        // Fetch certificate html from server
        // and initialize converters
        this.init_render_certificate=(course_id, action, callback)=>
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

                iframe.onload=()=>this.dispatch_methods(action, iframe, iframe_document, callback);
            });
        }
    }

    // Configure working state
    var loading_ = $('<div style="text-align:center;margin:15px 0;">Processing . . .</div>').hide();
    $('#tutor-download-certificate-pdf').parent().append(loading_);

    // Instantiate image processor for this scope
    var image_processor = new image();

    // register event listener
    $('#tutor-download-certificate-pdf, #tutor-view-certificate-image').click(function(event)
    {
        // Prevent default action
        event.preventDefault();

        // Avoid repetitive click
        if(!loading_.is('visible'))
        {
            // Set state as work in progress
            loading_.show();

            // Invoke the render method according to action type 
            var action = $(this).attr('id')=='tutor-download-certificate-pdf' ? 'download' : 'view';
            var course_id = $(this).data('course_id');
            
            image_processor.init_render_certificate(course_id, action, function()
            {
                // Set state as work not in progress/complete
                loading_.hide();
            });
        }
    });
});