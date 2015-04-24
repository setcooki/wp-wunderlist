var fs      = require('fs');
var app     = require('express')();
var http    = require('http').Server(app);
var io      = require('socket.io')(http);
var options = require('../var/options.json');
var clients = [];

io.on('connection', function(socket){
    console.log("connected is: " + socket.conn.id);
    //console.log('a user connected');
    socket.on('disconnect', function(){
        //console.log('user disconnected');
    });
    socket.on('trigger', function(data){
        io.sockets.emit('webhook', data);
    });
});

if(options.live.port){
    http.listen(options.live.port, function(){
      console.log('listening on *:7777');
    });
}