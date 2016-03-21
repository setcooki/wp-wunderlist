(function($, wunderlist)
{
    var app =
    {
        /**
         * init app loading external scripts and options
         * @param c expects wunderlist config object
         */
        init: function(c)
        {
            wunderlist.config = c || {};
            wunderlist.debug('app.init');

            var cnt = 0;
            var inc = [
                '/static/js/lib/api.js',
                '/static/js/lib/action.js',
                '/static/js/lib/poll.js',
                '/static/js/lib/socket.js'
            ];
            $.each(inc, function(i, v){
                $.when($.getScript(wunderlist.conf('base') + v)).done(function(e){
                    if((cnt += 1) == inc.length){
                        wunderlist.call('options', null, app.run);
                    }
                }).fail(function(e){
                    wunderlist.error('failed to include: {0}', [v]);
                });
            });
        },

        /**
         * run app
         * @param options expects plugin options.json
         */
        run: function(options)
        {
            $(wunderlist.conf('css.taskRoot') + " [data-type='enter']").keyup(function(e){
                if(e.keyCode == 13){
                    wunderlist.Action.trigger(e);
                }
            });
            $(wunderlist.conf('css.taskRoot') + " [data-type='click']").click(function(e){
                wunderlist.Action.trigger(e);
            });

            if(options.live.mode && options.live.mode != 'none'){
                try {
                    if(options.live.mode == 'poll'){
                        wunderlist.Poll.run(options.live.poll.interval || 5000);
                    }else{
                        var socket = new WunderlistSocket({
                            host: options.live.push.host,
                            port: options.live.push.port
                        });
                        socket.run();
                    }
                }catch(e){}
            }
        },

        /**
         *
         * @param msg
         * @param vars
         * @param level
         */
        log: function(msg, vars, level)
        {
            var l = parseInt(wunderlist.conf('debug'));
            if(l > 0){
                if(level > l) return;
                vars = vars || [];
                if(vars.length > 0){
                    msg = msg.replace(/{(\d+)}/g, function(match, number) {
                        return (typeof vars[number] != 'undefined') ? vars[number] : match;
                    });
                }
                console.log(msg);
            }
        }
    };

    wunderlist.call = function(call, data, callback, callbackParams, ajaxParams)
    {
        try
        {
            var d = {};
            d.call = call;
            d.data = data || null;
            d.action = 'wunderlist_ajax';
            d.nonce = wunderlistAjax.nonce || null;
            callback = callback || null;
            callbackParams = callbackParams || null;
            ajaxParams = ajaxParams || {};
            $.ajax({
                type: ajaxParams.method || 'post',
                dataType: ajaxParams.dataType || 'json',
                url: ajaxParams.url || wunderlistAjax.url,
                data: d,
                success: function(response){
                    if(response && !response.error){
                        if(callback){
                            callback(response, callbackParams);
                        }
                    }else if(response && response.error){
                        wunderlist.error('ajax response failed with: {0}', [response.error]);
                    }else{
                        wunderlist.error('ajax response is empty');
                    }
                },
                error: function(o, s, t){
                    wunderlist.error('ajax call failed with: {0}', [t]);
                }
            });
        }catch(e){
            wunderlist.error('ajax call fails with exception: {0}', [e.message]);
        }
    };

    wunderlist.error = function(msg, vars)
    {
        app.log(msg, vars, 1);
    };

    wunderlist.debug = function(msg, vars)
    {
        app.log(msg, vars, 2);
    };

    wunderlist.conf = function(key, value)
    {
        if(key.indexOf('.') == -1){
            if(value != undefined){
                return wunderlist.config[key] = value;
            }
            return wunderlist.config[key];
        }else{
            return key.split('.').reduce(function(obj, i){ return obj[i] || '' ; }, wunderlist.config);
        }
    };

    if($ && wunderlistConf){
        $(document).ready(function(){
            app.init(wunderlistConf);
        });
    }
})(jQuery || null, wunderlist = {});