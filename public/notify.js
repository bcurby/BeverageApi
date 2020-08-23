let {google} = require('googleapis');
let MESSAGING_SCOPE = "https://www.googleapis.com/auth/firebase.messaging";
let SCOPES = [MESSAGING_SCOPE];

let express = require('express');
let app = express();

let bodyParser = require('body-parser');
let router = express.Router();

let request = require('request');

app.use(bodyParser.urlencoded({extended:true}));
app.use(bodyParser.json);

router.post('/send', function(req,res){

    getAccessToken().then(function(access_token){

        let title = req.body.title;
        let body = req.body.body;
        let token = 'eC3SRQ3sTv-aa5BmBuB7rM:APA91bHZjgNUZOyWVbWDVafhlNvLqg92Ph0lfjmKdhfcoGvJ5L4qi7pZNwgHIEMs2_u-ka7enq1uRfaMGHoqXZo1OlO29wlIzjfSudYULXsoczilF_hSujXVj06rR3cCAK6PiLN-id_-';

        request.post({
           headers: {
               Authorization: 'Bearer'+ access_token,
           },
            url: "https://fcm.googleapis.com/v1/projects/beveragebookers/messages:send",
            body: JSON.stringify(
                {
                    "message": {
                        'token': token,
                        'notification': {
                            'body': body,
                            'title': title,
                        }
                    }
                }
            )

        }, function(error, response, body){
            res.end(body);
            console.log(body);
        });

    });

});

app.use('/api', router);

// app.listen(port, function(){
//
//     console.log("Server is listening to port " + port);

// });

let http = require('http')

function getAccessToken(){


return new Promise(function(resolve, reject){

    let key = require("./service-account.json")
    let jwtClient = new google.auth.JWT(
        key.client_email,
        null,
        key.private_key,
        SCOPES,
        null
    );
    jwtClient.authorize(function(err, tokens){
        if(err){
            reject(err);
            return;
        }
        resolve(tokens.access_token)

    });
});
}

getAccessToken().then(function(access_token){
    console.log(access_token);
});