<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../includes/DbOperations.php';

$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);


//Get Menu Items for Browse Menu
$app->get('/getitems', function(Request $request, Response $response) {

    $db = new DbOperations;

    $items = $db->getItems();

    return $response
        ->withJson($items)
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});


//Creates a new user record
$app->post('/createuser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('email', 'password', 'firstName', 'lastName', 'phone'), $request, $response)){

        $request_data = $request->getParsedBody(); 

        $email = $request_data['email'];
        $password = $request_data['password'];
        $firstName = $request_data['firstName'];
        $lastName = $request_data['lastName']; 
        $phone = $request_data['phone'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations; 

        $result = $db->createUser($email, $hash_password, $firstName, $lastName, $phone);
        
        if($result == USER_CREATED){

            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'User created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);

        }else if($result == USER_FAILURE){

            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'An error occurred';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    

        }else if($result == USER_EXISTS){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});


//Add a clicked item to user cart
$app->post('/addtocart', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('userID', 'itemID', 'itemTitle', 'itemPrice', 'itemQuantity', 'cartStatus'), $request, $response)){

        $request_data = $request->getParsedBody(); 

        $userID = $request_data['userID'];
        $itemID = $request_data['itemID'];
        $itemTitle = $request_data['itemTitle'];
        $itemPrice = $request_data['itemPrice']; 
        $itemQuantity = $request_data['itemQuantity'];
        $cartStatus = $request_data['cartStatus'];

        $db = new DbOperations; 

        $result = $db->addToCart($userID, $itemID, $itemTitle, $itemPrice, $itemQuantity, $cartStatus);
        
        if($result == ADDED_TO_CART){

            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'Added to Cart';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(303);

        } else if($result == ITEM_ALREADY_IN_CART) {
            
            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'Item already in Cart';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(304);
        }
    }
});   


//Get Cart Items for Cart Activity
$app->get('/getcartitems', function(Request $request, Response $response) {

    $userID = $_GET['userID'];

    $db = new DbOperations;

    $cart = $db->getCartItems($userID);

    if ($cart == CART_EMPTY) {
        
        $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'Your cart is empty';

            $response->write(json_encode($message));

            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(305);
    } else {

    return $response
        ->withJson($cart)
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    }
});


//Logs an existing user into their account
$app->post('/userlogin', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('email', 'password'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $email = $request_data['email'];
        $password = $request_data['password'];
        
        $db = new DbOperations; 

        $result = $db->userLogin($email, $password);

        if($result == USER_AUTHENTICATED){
            
            $user = $db->getUserByEmail($email);
            $response_data = array();

            $response_data['error']=false; 
            $response_data['message'] = 'Login Successful';
            $response_data['user']=$user; 

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    

        }else if($result == USER_NOT_FOUND){
            $response_data = array();

            $response_data['error']=true; 
            $response_data['message'] = 'User does not exist';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    

        }else if($result == USER_PASSWORD_DO_NOT_MATCH){
            $response_data = array();

            $response_data['error']=true; 
            $response_data['message'] = 'Invalid login credentials';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);  
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});


//Checks that parameter fields are not empty
function haveEmptyParameters($required_params, $request, $response){
    $error = false; 
    $error_params = '';
    $request_params = $request->getParsedBody(); 

    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true; 
            $error_params .= $param . ', ';
        }
    }

    if($error){
        $error_detail = array();
        $error_detail['error'] = true; 
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error; 
}

$app->run();