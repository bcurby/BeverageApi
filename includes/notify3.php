<?php


function sendingNotification3($to)
{
    // API access key from Google API's Console
    define('API_ACCESS_KEY','ya29.c.Kl7ZB95zdp5qyX19RHSIMWUTdLyCJy64l2O1T-p1gR9Hcv1HIu0NV5ZNRsE0ET5AA-lXUNxvu6w3hj5FZEYHOokuqVzeLyWAd2uPp_b4J2DMJd_2Gub9KJaa-DTOREZB');

    $url = 'https://fcm.googleapis.com/fcm/send';


// prepare the message
    $message = array(
        'title'     => 'This is a title.',
        'body'      => 'Here is a message.',
        'vibrate'   => 1,
        'sound'      => 1
    );

    $fields = array(
        'registration_ids' => $to,
        'data'             => $message
    );

    $headers = array(
        'Authorization: Bearer ya29.c.Kl7ZB95zdp5qyX19RHSIMWUTdLyCJy64l2O1T-p1gR9Hcv1HIu0NV5ZNRsE0ET5AA-lXUNxvu6w3hj5FZEYHOokuqVzeLyWAd2uPp_b4J2DMJd_2Gub9KJaa-DTOREZB',
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL,$url);
    curl_setopt( $ch,CURLOPT_POST,true);
    curl_setopt( $ch,CURLOPT_HTTPHEADER,$headers);
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt( $ch,CURLOPT_POSTFIELDS,json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}
