const express = require('express');
const { SocketAddress } = require('net');
const app = express();
const server = require('http').createServer(app);
const io = require('socket.io')(server, {
    cors: { origin: "*"}
});
const Redis = require('ioredis');
const redis = new Redis();
const users = [];

// app.get('/', (req, res) => {
//   res.send('<h1>Hello world</h1>');
// });

// server.listen(8001, () => {
//   console.log('listening on *:3000');
// });

server.listen(8001, () => {
    console.log('Node server is running...');
});

redis.subscribe('private-channel', function(){
    console.log('subcribe to private channel');
})

redis.on('message', function(channel, message) {
    message = JSON.parse(message);
    console.log(message);
    
    if(channel == "private-channel") {
        let data = message.data.data;
        let receiver_id = data.receiver_id;
        let event = message.event;

        io.to(`${users[receiver_id]}`).emit(channel + ':' + message.event, data);
    }
});

io.on('connection', (socket) => {
    
    // for connection creation. 
    socket.on('user_connected', (user_id) => {
        users[user_id] = socket.id;
        io.emit('updateUserStatus', users);
        console.log('A user having Chat id # ' + user_id + ' is connected.');
    });
    
    // for connection disconnect.
    socket.on('disconnect', function() {
        var i = users.indexOf(socket.id);
        users.splice(i, 1, 0);
        io.emit('updateUserStatus', users);
        console.log(users);
    });
});
