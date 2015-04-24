(function($){
    var auth = false;
    $(document).ready(function(){
        $('#wunderlist_todo_client_id, #wunderlist_todo_client_secret').on('keyup', function(e){
            auth = true;
        });
        $('#wunderlist_todo_auth_button').on('click', function(e){
            if(auth){
                var href = $(this).attr('href');
                $(e.currentTarget).closest('form').ajaxSubmit({
                    success: function(d){ location.href = href; },
                    error: function(e){}
                });
                e.preventDefault();
                return false;
            }
        });
    });
})(jQuery);