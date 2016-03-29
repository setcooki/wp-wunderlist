(function($, wunderlist)
{
    wunderlist.Action = function()
    {
        var self = this;
        self.getList = function(id)
        {
            return $(wunderlist._config('css.listRoot') + '[data-id="' + id + '"]');
        };
        self.getTask = function(list_id, task_id)
        {
            return $(self.getList(list_id)).find(wunderlist._config('css.taskRoot') + '[data-id="' + task_id + '"]');
        };
        self.getNote = function(list_id, note_id)
        {
            return $(self.getList(list_id)).find(wunderlist._config('css.noteRoot') + '[data-id="' + note_id + '"]');
        };
        self.setValue = function(e, value)
        {
            if($(e)){
                if($(e).is('input')){
                    $(e).val(value);
                }else{
                    $(e).html(value);
                }
            }
        };
        self.setData = function(e, data)
        {
            if($(e)){
                $(e).data(data);
            }
        };

        var action =
        {
            toggleNote: function(e){
                var data = $(e.currentTarget).data();
                if('toggle' in data){
                    var toggle = $(e.currentTarget).closest(wunderlist._config('css.taskRoot')).find(data.toggle);
                    $(toggle).toggle(100);
                }
            }
        };

        return {

            trigger: function(event)
            {
                if(typeof event == 'object' && 'target' in event){
                    var data = $(event.currentTarget).data();
                    if('action' in data && data.action in action){
                        action[data.action](event);
                    }
                }
            },

            call: function(action, data, params)
            {
                if(action && action in wunderlist.Action){
                    wunderlist.Api._call(action, data || null, wunderlist.Action[action], params || null);
                }
            }
        };
    }();
})(jQuery, wunderlist);