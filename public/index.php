<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../includes/DbOperations.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);


//Get Menu Items for Browse Menu
$app->get('/getitems', function (Request $request, Response $response) {

    $db = new DbOperations;

    $items = $db->getItems();

    return $response
        ->withJson($items)
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});


//Creates a new user record
$app->post('/createuser', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('email', 'password', 'firstName', 'lastName', 'phone'), $request, $response)) {

        $request_data = $request->getParsedBody();

        $email = $request_data['email'];
        $password = $request_data['password'];
        $firstName = $request_data['firstName'];
        $lastName = $request_data['lastName'];
        $phone = $request_data['phone'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations;

        $result = $db->createUser($email, $hash_password, $firstName, $lastName, $phone);

        if ($result == USER_CREATED) {

            $message = array();
            $message['error'] = false;
            $message['message'] = 'User created successfully';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == USER_FAILURE) {

            $message = array();
            $message['error'] = true;
            $message['message'] = 'An error occurred';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        } else if ($result == USER_EXISTS) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(403);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});


//Add an order to the 'orders' table
$app->post('/placeorder', function (Request $request, Response $response) {

    $request_data = $request->getParsedBody();

    $userID = $request_data['userID'];
    $creditCardNumber = $request_data['creditCardNumber'];
    $creditCardCVV = $request_data['creditCardCVV'];
    $expiryMonth = $request_data['expiryMonth'];
    $expiryYear = $request_data['expiryYear'];
    $deliveryStatus = $request_data['deliveryStatus'];
    $orderTotal = $request_data['orderTotal'];


    if (!haveEmptyParameters(array('userID', 'creditCardNumber', 'creditCardCVV', 'expiryMonth', 'expiryYear', 'orderTotal'), $request, $response)) {
        if (!invalidPayment($creditCardNumber, $creditCardCVV, $expiryMonth, $expiryYear, $orderTotal)) {

            $db = new DbOperations;

            $result = $db->placeOrder($userID, $orderTotal, $deliveryStatus);

            if ($result == ORDER_PLACED) {

                $message = array();
                $message['error'] = false;
                $message['message'] = 'Your order was successful';

                $response->write(json_encode($message));

                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(201);
            } else if ($result == ORDER_FAILED) {

                $message = array();
                $message['error'] = false;
                $message['message'] = 'There was a problem placing your order';

                $response->write(json_encode($message));

                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
            }
        }
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'There was a problem placing payment with your credit card';
        $response->write(json_encode($error_detail));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(422);
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});


//Add a clicked item to user cart
$app->post('/addtocart', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('userID', 'itemID', 'itemTitle', 'itemPrice', 'itemQuantity'), $request, $response)) {

        $request_data = $request->getParsedBody();

        $userID = $request_data['userID'];
        $itemID = $request_data['itemID'];
        $itemTitle = $request_data['itemTitle'];
        $itemPrice = $request_data['itemPrice'];
        $itemQuantity = $request_data['itemQuantity'];

        $db = new DbOperations;

        $result = $db->addToCart($userID, $itemID, $itemTitle, $itemPrice, $itemQuantity);

        if ($result == ADDED_TO_CART) {

            $message = array();
            $message['error'] = false;
            $message['message'] = 'Added to Cart';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
                
        } else if ($result == ITEM_ALREADY_IN_CART) {

            $message = array();
            $message['error'] = false;
            $message['message'] = 'Item already in Cart';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(403);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

//empty cart
$app->post('/emptycart', function (Request $request, Response $response) {
    
    $request_data = $request->getParsedBody();

    $userID = $request_data['userID'];

    $db = new DbOperations;

    $result = $db->emptyCart($userID);

    if ($result == CART_EMPTY_PASS) {

        $message = array();
        $message['error'] = false;
        $message['message'] = 'Cart Emptied';

        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(201);
            
    } else if ($result == CART_EMPTY_FAILED) {

        $message = array();
        $message['error'] = false;
        $message['message'] = 'Cart Failed to Empty';

        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(402);
    }    
 });


//Get Cart Items for Cart Activity
$app->get('/getcartitems', function (Request $request, Response $response) {

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
            ->withStatus(303);
    } else {

        return $response
            ->withJson($cart)
            ->withHeader('Content-type', 'application/json')
            ->withStatus(201);
    }
});


//Logs an existing user into their account
$app->post('/userlogin', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('email', 'password'), $request, $response)) {
        $request_data = $request->getParsedBody();

        $email = $request_data['email'];
        $password = $request_data['password'];

        $db = new DbOperations;

        $result = $db->userLogin($email, $password);

        if ($result == USER_AUTHENTICATED) {

            $user = $db->getUserByEmail($email);
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Login Successful';
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(202);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'User does not exist';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Invalid login credentials';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(401);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});


//Checks that parameter fields are not empty
function haveEmptyParameters($required_params, $request, $response)
{
    $error = false;
    $error_params = '';
    $request_params = $request->getParsedBody();

    foreach ($required_params as $param) {
        if (!isset($request_params[$param]) || strlen($request_params[$param]) <= 0) {
            $error = true;
            $error_params .= $param . ', ';
        }
    }

    if ($error) {
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error;
}


//Pay for order with users credit card details
function invalidPayment($creditCardNumber, $creditCardCVV, $expiryMonth, $expiryYear, $orderTotal)
{

    $credit_details = array();
    array_push($credit_details, $creditCardNumber);
    array_push($credit_details, $creditCardCVV);
    array_push($credit_details, $expiryMonth);
    array_push($credit_details, $expiryYear);
    array_push($credit_details, $orderTotal);


    if (in_array(0, $credit_details)) {
        //Default value of 0 for any of the credit card details means the payment would not proceed
        return true;
    } else {
        //Default value of 0 is not present for any of the credit card details
        //Payment proceeds and false is returned
        return false;
    }
}

//CAFE SIDE - get orders for listing orders view
$app->get('/getorderslist', function (Request $request, Response $response) {

    $db = new DbOperations;

    $items = $db->getOrdersDetails();

    return $response
        ->withJson($items)
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

//Logs an existing staff to viewing orders
$app->post('/staffValidate', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('staffID'), $request, $response)) {
        $request_data = $request->getParsedBody();

        $staffID = $request_data['staffID'];
        $db = new DbOperations;

        $result = $db->staffValidate($staffID);
        if ($result == STAFF_AUTHENTICATED) {

            $staff = $db->getStaffByID($staffID);
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Login Successful';
            $response_data['staff'] = $staff;

            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(202);
        } else if ($result == STAFF_NOT_FOUND) {
            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Staff ID is not found';

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

//CAFE SIDE - gets the order items for each order clicked
$app->get('/getorderitems', function (Request $request, Response $response) {

    $cartID = $_GET['cartID'];

    $db = new DbOperations;

    $cart = $db->getOrderItems($cartID);

    return $response
        ->withJson($cart)
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

//Creates a new delivery entry
$app->post('/bookdelivery', function (Request $request, Response $response) {
    
    $request_data = $request->getParsedBody();

    $userID = $request_data['userID'];
    $streetNumber = $request_data['streetNumber'];
    $streetName = $request_data['streetName'];
    $postCode = $request_data['postCode'];
    $cityTown = $request_data['cityTown'];

    $db = new DbOperations;

    $result = $db->bookDelivery($userID, $streetNumber, $streetName, $postCode, $cityTown);
    
    if ($result == DELIVERY_CREATED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Delivery Submitted';

        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(201);
            
    } else if ($result == DELIVERY_FAILED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Delivery Failed';

        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(422);
    }
});

$app->run();
