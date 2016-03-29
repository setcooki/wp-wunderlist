(function($, wunderlist)
{
    var nonce = null;
    var app =
    {
        /**
         * init app loading external scripts and options
         */
        init: function()
        {
            //wunderlist.debug('app.init');
            var cnt = 0;
            var loc = app.location();
            if(loc){
                if('nonce' in loc.params){
                    nonce = loc.params.nonce;
                    $.when($.getJSON(loc.url + '/../../../var/config.json')).done(function(c){
                        wunderlist.config = c || {};
                        if(wunderlist.config.deps){
                            $.each(wunderlist.config.deps, function(i, v){
                                $.when($.getScript(loc.url + v)).done(function(){
                                    if((cnt += 1) == wunderlist.config.deps.length){
                                        wunderlist._call('options', null, app.run);
                                    }
                                }).fail(function(e){
                                    wunderlist._error('failed to include: {0}', [v]);
                                });
                            });
                        }else{
                            wunderlist._call('options', null, app.run);
                        }
                    });
                }else{
                    //major error
                }
            }else{
                //major error
            }
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
            var l = parseInt(wunderlist._config('debug'));
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
        },

        /**
         *
         */
        location: function()
        {
            var location = null, match = null, params = {};
            var scripts = $('script');
            if(scripts && scripts.length > 0){
                $.each(scripts, function(){
                    if(this.src && (match = this.src.match(new RegExp('(.*)wp-wunderlist\\.(?:min\\.)?js\\?(.*)$'))) !== null){
                        if(match.length >= 3 && match[2] != ''){
                            $.each(match[2].split('&'), function(i,v){
                                v = v.split('=');
                                params[decodeURIComponent(v[0])] = decodeURIComponent(v[1]);
                            })
                        }
                        location = {
                            'url': match[1],
                            'params': params
                        };
                    }
                });
            }
            return location;
        }
    };

    wunderlist._call = function(call, data, callback, callbackParams, ajaxParams)
    {
        try
        {
            var d = {};
            d.call = call;
            d.data = data || null;
            d.action = 'wunderlist_ajax';
            d.nonce = nonce;
            callback = callback || null;
            callbackParams = callbackParams || null;
            ajaxParams = ajaxParams || {};
            $.ajax({
                type: ajaxParams.method || 'post',
                dataType: ajaxParams.dataType || 'json',
                url: ajaxParams.url || wunderlist._config('ajax.url'),
                data: d,
                success: function(response){
                    if(response && !response.error){
                        if(callback){
                            callback(response, callbackParams);
                        }
                    }else if(response && response.error){
                        wunderlist._error('ajax response failed with: {0}', [response.error]);
                    }else{
                        wunderlist._error('ajax response is empty');
                    }
                },
                error: function(o, s, t){
                    wunderlist._error('ajax call failed with: {0}', [t]);
                }
            });
        }catch(e){
            wunderlist._error('ajax call fails with exception: {0}', [e.message]);
        }
    };

    wunderlist._error = function(msg, vars)
    {
        app.log(msg, vars, 1);
    };

    wunderlist._debug = function(msg, vars)
    {
        app.log(msg, vars, 2);
    };

    wunderlist._config = function(key, value)
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

    if($){
        $(document).ready(function(){
            app.init();
        });
    }
})(jQuery || null, wunderlist = {});