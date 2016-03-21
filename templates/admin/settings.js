(function($, d, w, undefined){
    var auth = false;
    $(d).ready(function(){
        $('#wp_wunderlist_client_id, #wp_wunderlist_client_secret').on('keyup', function(e){
            auth = true;
        });
        $('#wp_wunderlist_auth_button').on('click', function(e){
            if(auth){
                var href = $(this).attr('href');
                $(e.currentTarget).closest('form').ajaxSubmit({
                    success: function(data){ location.href = href; },
                    error: function(error){}
                });
                e.preventDefault();
                return false;
            }
        });
        $('#wp_wunderlist_options_live_mode').change(function(){
            $('.live').hide();
            $('.live.' + $(this).val()).show();
        })
    });
})(jQuery, document, window);