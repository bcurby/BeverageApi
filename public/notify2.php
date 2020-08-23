
<?php

use GuzzleHttp;

$access_token = 'ya29.c.Kl7ZB95zdp5qyX19RHSIMWUTdLyCJy64l2O1T-p1gR9Hcv1HIu0NV5ZNRsE0ET5AA-lXUNxvu6w3hj5FZEYHOokuqVzeLyWAd2uPp_b4J2DMJd_2Gub9KJaa-DTOREZB';

$reg_id = 'eC3SRQ3sTv-aa5BmBuB7rM:APA91bHZjgNUZOyWVbWDVafhlNvLqg92Ph0lfjmKdhfcoGvJ5L4qi7pZNwgHIEMs2_u-ka7enq1uRfaMGHoqXZo1OlO29wlIzjfSudYULXsoczilF_hSujXVj06rR3cCAK6PiLN-id_-';

$message = [
    'notification' => [
        'title' => 'Test Message',
        'body' => "This is a test!"
    ],
    'to' => $reg_id
];

$client = new GuzzleHttp\Client([
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'key='.$access_token,
    ]
]);

$response = $client->post('https://fcm.googleapis.com/fcm/send',
    ['body' => json_encode($message)]
);

echo $response->getBody();
