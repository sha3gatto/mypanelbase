// JavaScript Document
jQuery(document).ready( function(){

    var file_frame;

    jQuery('body').on('click','.hero4genesis_upload', function( event ){
        event.preventDefault();
        var widget_id = jQuery(this).closest('.widget').attr('id');

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
          title: jQuery( this ).data( 'uploader_title' ),
          button: {
            text: jQuery( this ).data( 'uploader_button_text' ),
          },
          multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
          // We set multiple to false so only get one image from the uploader
          attachment = file_frame.state().get('selection').first().toJSON();
          jQuery('#'+ widget_id +' .hero4genesis_imageurl').val(attachment.url);
          // jQuery('#wpautbox_user_image_url').html('<img src="'+ attachment.url +'" width="120"/><br />');
          // Do something with attachment.id and/or attachment.url here
        });

        // Finally, open the modal
        file_frame.open();
    });

    jQuery(document).on('click', '.hero4genesis_remove_image',function(){
        var widget_id = jQuery(this).closest('.widget').attr('id');
        jQuery('#'+ widget_id +' .hero4genesis_imageurl').val('');
    });
});

