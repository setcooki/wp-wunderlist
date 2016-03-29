(function($, wunderlist)
{
    wunderlist.Api = function()
    {
        var options = {};

        return {

            options: function(o){
                options = o || {};
            },

            call: function(action, data, callback, callbackParams)
            {
                if(action){
                    data = data || {};
                    wunderlist._call('api', {
                        action: action,
                        data: data
                    }, callback || null, callbackParams || null);
                }
            }
        };
    }();
})(jQuery, wunderlist);