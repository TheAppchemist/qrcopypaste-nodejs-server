var FUNCTION_AUTH = "auth";
var FUNCTION_ERROR = "error";
var FUNCTION_PING = "ping";
var FUNCTION_SEND = "paste";
var FUNCTION_COPY = "copy";
var FUNCTION_ERROR_CODE_CLIENT_DISCONNECTED = 1;

var clients = [];
var copies = [];

var WebSocketServer = require('ws').Server;
express = require('express'),
    exp = express();

var bodyParser = require('body-parser');
var uuid = require('uuid');
var httpServ = require('http');

exp.use(bodyParser.urlencoded({     // to support URL-encoded bodies
    extended: true
}));

var app = httpServ.createServer(exp).listen(1235, function() {

});

exp.post('/paste/:to', function (req, res) {
    res.header('Content-type', 'application/json');

    var to = req.params.to;

    if (clients[to] == null)
    {
        return res.end(JSON.stringify({success: false}));
    }

    var data = req.body.data;
    clients[to].send(JSON.stringify({
        data: data,
        method: FUNCTION_SEND
    }));

    return res.end(JSON.stringify({success: true}));
});

exp.get('/copy/:to', function (req, res) {
    res.header('Content-type', 'application/json');

    var to = req.params.to;

    if (clients[to] == null)
    {
        return res.end(JSON.stringify({success: false}));
    }

    return res.end(JSON.stringify({success: true, data: copies[to]}));
});

var wss = new WebSocketServer({server: app});

wss.on('connection', function(ws)
{
    ws.on('message', function(message)
    {
        console.log(message);
        var json = eval('(' + message + ')');
        var method = json.method;

        if (method == FUNCTION_COPY)
        {
            var data = json.data;
            if (copies[json.from] != null)
            {
                console.log("Saving " + data);
                copies[json.from] = data;
            }
        }

        /*var json = eval('(' + message + ')');

        if (clients[json.to] == null)
        {
            ws.send(JSON.stringify({
                method: FUNCTION_ERROR,
                message: "Client disconnected",
                code: FUNCTION_ERROR_CODE_CLIENT_DISCONNECTED
            }));
        }
        else {
            clients[json.to].send(message);
        }*/
    });

    var id = uuid.v1();
    var out = {
        method: FUNCTION_AUTH,
        auth: id
    };
    clients[id] = ws;
    copies[id] = "";
    ws.send(JSON.stringify(out));

    console.log('Connected: ' + id);

    ws.on('close', function close()
    {
        console.log('close');

        for(var key in clients)
        {
            if(clients[key] == ws)
            {
                delete clients[key];
                delete copies[key];
                console.log("Disconnected " + clients[key]);
            }
        }
    });
});

setInterval(function() {
    for (var key in clients)
    {
        var ping = {
            method: FUNCTION_PING
        };

        try {
            clients[key].send(JSON.stringify(ping));
        }
        catch(ex)
        {
            console.log(ex);
        }
    }
}, 50 * 1000);

console.log("Listening")