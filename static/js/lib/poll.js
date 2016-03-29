(function($, wunderlist)
{
    wunderlist.Poll = function()
    {
        var options = {};

        return {

            options: function(o){
                options = o || {};
            },

            run: function(interval){
                var params = null, list = null;
                setInterval(function(){
                    $(wunderlist._config('css.listRoot')).each(function(){
                        list = $(this);
                        params = {dataType: 'html'};
                        wunderlist._call('render', {type: 'list', id: $(this).data('id')}, function(html){
                            $(list).replaceWith(html);
                        }, null, params);
                    });
                }, interval);
            }
        };
    }();
})(jQuery, wunderlist);