<!DOCTYPE html>
<html>
<title>QR Copy Paste</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="css/materialize.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">

<script src="js/jquery-2.1.4.min.js"></script>
<script type="application/javascript" src="js/qrcode.min.js"></script>
<script src="js/materialize.min.js"></script>
<script src="js/qrcode.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>

<style type="text/css">
    .center {
        margin: auto;
        width: 60%;
        padding: 10px;
    }

    .mainContainer {
        position: absolute;
        left: 20px;
        right: 20px;
        top: 20px;
        bottom: 20px;
        background-color: #1C202B;
        padding: 0px;
    }

    .sideContainer {
        height: 100%;
        padding: 0px;
    }

    .leftContainer {
    }

    .rightContainer {
        background-image: url('img/bg.jpg');
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
        background-position: center center;

        padding-top: 80px;
        position: relative;
    }

    #qrContainer {
        background-color: white;
        margin-top: 80px;
        display: inline-block;
        padding: 20px;
        border-radius: 10px;
    }

    #qrcode {
        width: 250px;
        height: 250px;
    }

    #progressDiv {
        position: absolute;
        bottom: 0;
        right: 0;
        padding-bottom: 0;
        margin-bottom: 0;
    }

    .gone {
        display: none;
    }

    #loaderHolder {
        width: 250px;
        height: 250px;
    }

    #loader {
        width: 250px;
        height: 250px;
        margin: auto auto;
        position: relative;
    }

    .center-vertical {
        position: relative;
        top: 50%;
        transform: translateY(-50%);
    }
</style>

<body>

<center><div class="w3-row"> <div class="w3-col l12">
<div class="w3-card" id="qrContainer">
    <div id="qrcode"></div>
    <div id="loader">
        <div class="center-vertical" style="width: 70px; height: 70px">
            <div class="preloader-wrapper big active" style="width: 70px; height: 70px">
                <div class="spinner-layer spinner-blue-only">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div><div class="gap-patch">
                        <div class="circle"></div>
                    </div><div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
        </div>
        <div class="w3-row">

        <center><div class="w3-col l6 center-align">
            <textarea id="txtData" style="width: 100%; height: 100px"></textarea><br><button onclick="save()">Save</button>
        </div></center></div>


</center>

    <div id="reconnect" style="width: 250px;
            height: 250px;
            margin: auto auto;
            position: relative;">
        <a onclick="connectToServer()" class="btn-floating btn-large waves-effect waves-light red center-vertical"><i class="material-icons">replay</i></a>

    </div>
</div>

<script type="application/javascript">
    var exampleSocket;
    var uuid;

    var qrcode = document.querySelector("#qrcode");
    qrcode.classList.add('gone');

    var reconnect = document.querySelector("#reconnect");
    reconnect.classList.add('gone');

    function connectToServer() {
        exampleSocket = new WebSocket("ws://localhost:1235");

        var reconnect = document.querySelector("#reconnect");
        reconnect.classList.add('gone');

        var loading = document.querySelector("#loader");
        loading.classList.remove('gone');

        exampleSocket.onmessage = function (event) {
            var json = eval('(' + event.data + ')');

            if (json.method == "auth") {
                uuid = json.auth;
                document.getElementById("qrcode").innerHTML = '';
                new QRCode(document.getElementById("qrcode"), {
                    text: uuid,
                    width: 250,
                    height: 250,
                    colorDark: "#1C202B",
                    colorLight: "#ffffff"
                });

                var qrcode = document.querySelector("#qrcode");
                qrcode.classList.remove('gone');

                var loading = document.querySelector("#loader");
                loading.classList.add("gone");
            }
            else if (json.method == "paste") {
                console.log("data: " + json.data);
            }
        };


        exampleSocket.onerror = function(error){
            console.log('Error detected: ' + error);
        };

        exampleSocket.onclose = function() {
            console.log("Disconnected");

            var qrcode = document.querySelector("#qrcode");
            qrcode.classList.add('gone');

            var reconnect = document.querySelector("#reconnect");
            reconnect.classList.remove('gone');
        };
    }

    function save()
    {
        exampleSocket.send(JSON.stringify({
            from: uuid,
            data: document.getElementById("txtData").value,
            method: "copy"
        }));
    }

    window.onload = connectToServer();

</script>

</body>
</html>