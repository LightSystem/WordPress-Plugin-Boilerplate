jQuery(document).ready( function () {

    if(jQuery('#image_storage_options').value == "hotlinking"){
        jQuery('#thumb_options_set').hide();
    }

    jQuery('#image_storage_options').on('change', function(){

        if(this.value == 'local_storage'){
            jQuery('#thumb_options_set').show();
        } else {
            jQuery('#thumb_options_set').hide();
        }

    });

} );
