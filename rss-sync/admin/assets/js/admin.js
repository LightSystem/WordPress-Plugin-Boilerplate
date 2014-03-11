jQuery(document).ready( function () {

    jQuery('#image_storage_options').on('change', function(){

        if(this.value == 'local_storage'){
            jQuery('#thumb_options_set').show();
        } else {
            jQuery('#thumb_options_set').hide();
        }

    });

} );
