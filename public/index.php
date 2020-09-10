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

    $itemType = $_GET['itemType'];

    $db = new DbOperations;

    $items = $db->getItems($itemType);

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
    $deliveryStatus = $request_data['deliveryStatus'];
    $orderTotal = $request_data['orderTotal'];


    if (!haveEmptyParameters(array('userID', 'orderTotal'), $request, $response)) {

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
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});


//Add a clicked item to user cart
$app->post('/addtocart', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array(
        'userID', 'itemID', 'itemTitle', 'itemPrice', 'itemQuantity', 'itemSize', 'itemMilk',
        'itemSugar', 'itemDecaf', 'itemVanilla', 'itemCaramel', 'itemChocolate', 'itemWhippedCream', 'itemFrappe', 'itemHeated',
        'itemComment', 'itemType'
    ), $request, $response)) {

        $request_data = $request->getParsedBody();

        $userID = $request_data['userID'];
        $itemID = $request_data['itemID'];
        $itemTitle = $request_data['itemTitle'];
        $itemPrice = $request_data['itemPrice'];
        $itemQuantity = $request_data['itemQuantity'];
        $itemSize = $request_data['itemSize'];
        $itemMilk = $request_data['itemMilk'];
        $itemSugar = $request_data['itemSugar'];
        $itemDecaf = $request_data['itemDecaf'];
        $itemVanilla = $request_data['itemVanilla'];
        $itemCaramel = $request_data['itemCaramel'];
        $itemChocolate = $request_data['itemChocolate'];
        $itemWhippedCream = $request_data['itemWhippedCream'];
        $itemFrappe = $request_data['itemFrappe'];
        $itemHeated = $request_data['itemHeated'];
        $itemComment = $request_data['itemComment'];
        $itemType = $request_data['itemType'];

        $db = new DbOperations;

        $result = $db->addToCart(
            $userID,
            $itemID,
            $itemTitle,
            $itemPrice,
            $itemQuantity,
            $itemSize,
            $itemMilk,
            $itemSugar,
            $itemDecaf,
            $itemVanilla,
            $itemCaramel,
            $itemChocolate,
            $itemWhippedCream,
            $itemFrappe,
            $itemHeated,
            $itemComment,
            $itemType
        );

        if ($result == ADDED_TO_CART) {

            $message = array();
            $message['error'] = false;
            $message['message'] = 'Added to Cart';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == PROBLEM_ADDING_TO_CART) {

            $message = array();
            $message['error'] = false;
            $message['message'] = 'Problem adding item to cart';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(403);
        } else if ($result == NOT_ENOUGH_ITEM_STOCK) {

            $message = array();
            $message['error'] = false;
            $message['message'] = 'Not enough item in stock';

            $response->write(json_encode($message));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(402);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

//empty cart
$app->post('/emptycart', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('userID'), $request, $response)) {
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
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
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


//CAFE SIDE - get orders for listing orders view
$app->get('/getorderslist', function (Request $request, Response $response) {

    $db = new DbOperations;

    $items = $db->getOrdersDetails();

    return $response
        ->withJson($items)
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});


//CAFE SIDE - get deliveries list
$app->get('/getdeliverieslist', function (Request $request, Response $response) {

    $db = new DbOperations;

    $items = $db->getDeliveriesDetails();

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

    $db = new DbOperations;

    $result = $db->bookDelivery($userID, $streetNumber, $streetName);

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


//Marks order as delivered in the deliveries table
$app->post('/markdelivered', function (Request $request, Response $response) {

    $request_data = $request->getParsedBody();

    $userID = $request_data['userID'];
    $cartID = $request_data['cartID'];

    $db = new DbOperations;

    $result = $db->markOrderDelivered($userID, $cartID);

    if ($result == ORDER_DELIVERED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Order Delivered';

        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(201);
    } else if ($result == MARK_ORDER_DELIVERED_FAILED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Marking order delivered in database failed';

        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(422);
    }
});

//Delete menu item
$app->post('/deletemenuitem', function (Request $request, Response $response) {

    $request_data = $request->getParsedBody();

    $itemID = $request_data['itemID'];

    $db = new DbOperations;

    $result = $db->deleteMenuItem($itemID);

    if ($result == STAFF_DELETE_ITEM_PASSED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Item Deleted';
        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(201);
    } else if ($result == STAFF_DELETE_ITEM_FAILED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Item Failed To Delete';
        $response->write(json_encode($message));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(402);
    }
});

//Add to queue
$app->post('/addtoqueue', function (Request $request, Response $response) {

    $request_data = $request->getParsedBody();

    $staffID = $request_data['staffID'];
    $orderID = $request_data['orderID'];
    $cartID = $request_data['cartID'];

    $db = new DbOperations;

    $result = $db->addToQueue($staffID, $orderID, $cartID);

    if ($result == ORDER_ADDED_TO_QUEUE) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Order Added To Queue';
        $response->write(json_encode($message));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(201);
    } else if ($result == ORDER_ADDED_TO_QUEUE_FAILED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Order Failed To Add To Queue';
        $response->write(json_encode($message));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(402);
    } else if ($result == ORDER_ALREADY_EXISTS_IN_QUEUE) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Order Already In Queue';
        $response->write(json_encode($message));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(403);
    }
});

//Make queued order available
$app->post('/makeorderavailable', function (Request $request, Response $response) {

    $request_data = $request->getParsedBody();

    $staffID = $request_data['staffID'];
    $orderID = $request_data['orderID'];
    $cartID = $request_data['cartID'];

    $db = new DbOperations;

    $result = $db->makeOrderAvailable($staffID, $orderID, $cartID);

    if ($result == ORDER_AVAILABLE) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Staff member removed from order';
        $response->write(json_encode($message));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(201);
    } else if ($result == ORDER_AVAILABLE_FAILED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Staff member failed to be removed from order';
        $response->write(json_encode($message));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(402);
    }
});

//Make queued order available
$app->post('/assignstafftoorder', function (Request $request, Response $response) {

    $request_data = $request->getParsedBody();

    $staffID = $request_data['staffID'];
    $orderID = $request_data['orderID'];
    $cartID = $request_data['cartID'];

    $db = new DbOperations;

    $result = $db->assignStaffToOrder($staffID, $orderID, $cartID);

    if ($result == STAFF_ASSIGNED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Staff member assigned to order';
        $response->write(json_encode($message));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(201);
    } else if ($result == STAFF_ASSIGNED_FAILED) {
        $message = array();
        $message['error'] = false;
        $message['message'] = 'Staff member failed to be assigned to order';
        $response->write(json_encode($message));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(402);
    }
});

//Gets the values for a single menu item
$app->get('/getmenuitem', function (Request $request, Response $response) {

    $itemID = $_GET['itemID'];

    $db = new DbOperations;

    $item = $db->getMenuItem($itemID);

    return $response
        ->withJson($item)
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

//Delete cart item
$app->post('/deletecartitem', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array(
        'userID', 'itemID', 'itemTitle', 'itemPrice', 'itemQuantity', 'itemSize', 'itemMilk',
        'itemSugar', 'itemDecaf', 'itemVanilla', 'itemCaramel', 'itemChocolate', 'itemWhippedCream', 'itemFrappe', 'itemHeated',
        'itemComment'
    ), $request, $response)) {

        $request_data = $request->getParsedBody();

        $userID = $request_data['userID'];
        $itemID = $request_data['itemID'];
        $itemTitle = $request_data['itemTitle'];
        $itemPrice = $request_data['itemPrice'];
        $itemQuantity = $request_data['itemQuantity'];
        $itemSize = $request_data['itemSize'];
        $itemMilk = $request_data['itemMilk'];
        $itemSugar = $request_data['itemSugar'];
        $itemDecaf = $request_data['itemDecaf'];
        $itemVanilla = $request_data['itemVanilla'];
        $itemCaramel = $request_data['itemCaramel'];
        $itemChocolate = $request_data['itemChocolate'];
        $itemWhippedCream = $request_data['itemWhippedCream'];
        $itemFrappe = $request_data['itemFrappe'];
        $itemHeated = $request_data['itemHeated'];
        $itemComment = $request_data['itemComment'];
        $db = new DbOperations;

        $result = $db->deleteCartItem(
            $userID,
            $itemID,
            $itemTitle,
            $itemPrice,
            $itemQuantity,
            $itemSize,
            $itemMilk,
            $itemSugar,
            $itemDecaf,
            $itemVanilla,
            $itemCaramel,
            $itemChocolate,
            $itemWhippedCream,
            $itemFrappe,
            $itemHeated,
            $itemComment
        );

        if ($result == DELETE_CART_ITEM_PASSED) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Cart Item Deleted';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == DELETE_CART_ITEM_FAILED) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Cart Item Failed to Delete';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(402);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
});

//Get items for staff menu
$app->get('/getitemsforstaffmenu', function (Request $request, Response $response) {

    $db = new DbOperations;

    $items = $db->getItemsForStaffMenu();

    return $response
        ->withJson($items)
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

//Add menu item
$app->post('/addmenuitem', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array(
        'itemTitle', 'itemPriceDouble', 'milkOption', 'sugarOption',
        'decafOption', 'extrasOption', 'frappeOption', 'heatedOption', 'itemType', 'itemTimeInt'
    ), $request, $response)) {

        $request_data = $request->getParsedBody();

        $itemTitle = $request_data['itemTitle'];
        $itemShortDesc = $request_data['itemShortDesc'];
        $itemPriceDouble = $request_data['itemPriceDouble'];
        $milkOption = $request_data['milkOption'];
        $sugarOption = $request_data['sugarOption'];
        $decafOption = $request_data['decafOption'];
        $extrasOption = $request_data['extrasOption'];
        $frappeOption = $request_data['frappeOption'];
        $heatedOption = $request_data['heatedOption'];
        $itemType = $request_data['itemType'];
        $itemTimeInt = $request_data['itemTimeInt'];

        $db = new DbOperations;

        $result = $db->addMenuItem(
            $itemTitle,
            $itemShortDesc,
            $itemPriceDouble,
            $milkOption,
            $sugarOption,
            $decafOption,
            $extrasOption,
            $frappeOption,
            $heatedOption,
            $itemType,
            $itemTimeInt
        );

        if ($result == ITEM_ADDED) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Item added to list';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == ITEM_FAILED_TO_ADD) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Item failed to add to list';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(402);
        } else if ($result == ITEM_TITLE_EXISTS) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Item title already in list';
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


//modify menu item
$app->post('/modifymenuitem', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array(
        'itemID', 'itemTitle', 'itemPriceDouble', 'milkOption', 'sugarOption',
        'decafOption', 'extrasOption', 'frappeOption', 'heatedOption', 'itemType', 'itemTimeInt'
    ), $request, $response)) {

        $request_data = $request->getParsedBody();

        $itemID = $request_data['itemID'];
        $itemTitle = $request_data['itemTitle'];
        $itemShortDesc = $request_data['itemShortDesc'];
        $itemPriceDouble = $request_data['itemPriceDouble'];
        $milkOption = $request_data['milkOption'];
        $sugarOption = $request_data['sugarOption'];
        $decafOption = $request_data['decafOption'];
        $extrasOption = $request_data['extrasOption'];
        $frappeOption = $request_data['frappeOption'];
        $heatedOption = $request_data['heatedOption'];
        $itemType = $request_data['itemType'];
        $itemTimeInt = $request_data['itemTimeInt'];

        $db = new DbOperations;

        $result = $db->modifyMenuItem(
            $itemID,
            $itemTitle,
            $itemShortDesc,
            $itemPriceDouble,
            $milkOption,
            $sugarOption,
            $decafOption,
            $extrasOption,
            $frappeOption,
            $heatedOption,
            $itemType,
            $itemTimeInt
        );

        if ($result == ITEM_MODIFIED) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Item was modified';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == ITEM_MODIFIED_FAILED) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Item failed to modify';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(402);
        } else if ($result == ITEM_TITLE_EXISTS) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Item title already in list';
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

$app->run();
