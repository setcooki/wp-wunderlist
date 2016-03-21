(function($, wunderlist)
{
    WunderlistSocket = function(options)
    {
        this.init(options);
    };

    $.extend(WunderlistSocket.prototype, {

        options: {},

        /**
         * init class instance with options
         * @param options expects options
         */
        init: function(options)
        {
            this.options = options || {}
        },

        /**
         * connect and run socket.io client
         */
        run: function()
        {
            wunderlist.debug('socket.run');

            var self = this;
            var host = this.options.host || window.location.protocol + '//' + window.location.host;
            var port = this.options.port || 7777;
            var socket = io.connect(host + ':' + port, {'connect timeout': 1000, 'force new connection': true});
            socket.on('connect', function(e){
                wunderlist.debug('socket.connected');
            });
            socket.on('webhook', function(data){
                self.call(data);
            });
            socket.on('error', function(e){
                wunderlist.error('socket error with: {0}', [e.message]);
            });
            socket.on('disconnect', function(e){
                wunderlist.debug('socket.disconnected');
            });
            socket.on('connect_error', function(e){
                //wunderlist.error('socket error with: {0}', [e.message]);
            });
        },

        /**
         * executes the webhook call and send data
         * @param data expects data from webhook post
         */
        call: function(data)
        {
            try{
                if(data){
                    data = JSON.parse(data);
                    if(data.action && wunderlist.Action[data.action]){
                         wunderlist.Action[data.action](data.data, data.params || {});
                    }
                }
            }
            catch(e){
                  wunderlist.error('socket response webhook error: {0}', [e.message]);
            }
        }
    });
})(jQuery, wunderlist);