<?php

class DbOperations
{

    private $con;

    //Creates the connection from the DbConnect file
    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect;
        $this->con = $db->connect();
    }


    //Creates a new user
    public function createUser($email, $password, $firstName, $lastName, $phone)
    {
        if (!$this->isEmailExist($email)) {
            $stmt = $this->con->prepare("INSERT INTO users (email, password, firstName, lastName, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $email, $password, $firstName, $lastName, $phone);
            if ($stmt->execute()) {
                return USER_CREATED;
            } else {
                return USER_FAILURE;
            }
        }
        return USER_EXISTS;
    }

    
    //Add item to user cart
    public function addToCart($userID, $itemID, $itemTitle, $itemPrice, $itemQuantity)
    {

        //does this user have an activer cart
        if ($this->isCartActive($userID)) {

            //if so then get the cartID
            $cartID = $this->getCartIDByUserID($userID);

            // check if the cartItem exists in the cart
            if (!$this->isCartItemExist($cartID, $itemID)) {
                $stmt = $this->con->prepare("INSERT INTO cartItem (cartID, itemID, itemTitle, itemPrice, itemQuantity) 
                VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $cartID, $itemID, $itemTitle, $itemPrice, $itemQuantity);
                if ($stmt->execute()) {
                    return ADDED_TO_CART;
                }
            }
            return ITEM_ALREADY_IN_CART;
        } else {
            $stmt = $this->con->prepare("INSERT INTO cart (userID, cartStatus) VALUES (?, 1)");
            $stmt->bind_param("s", $userID);
            $stmt->execute();
            $cartID = $this->getCartIDByUserID($userID);
            $stmt = $this->con->prepare("INSERT INTO cartItem (cartID, itemID, itemTitle, itemPrice, itemQuantity) 
                    VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $cartID, $itemID, $itemTitle, $itemPrice, $itemQuantity);
            if ($stmt->execute()) {
                return ADDED_TO_CART;
            }
        }
        return ITEM_ALREADY_IN_CART;
    }


    //Empty user cart
    public function emptyCart($userID) {
        $cartID = $this->getCartIDByUserID($userID);
        $stmt = $this->con->prepare("DELETE FROM cart WHERE cartID = $cartID");
        $stmt = $this->con->prepare("DELETE FROM cartitem WHERE cartID = $cartID");
        if ($stmt->execute()) {
                return CART_EMPTY_PASS;
        } else {
                return CART_EMPTY_FAILED;
            }
    }


    //Creates a new delivery
    public function bookDelivery($userID, $streetNumber, $streetName)
    {
        $cartID = $this->getCartIDByUserID($userID);
        $stmt = $this->con->prepare("INSERT INTO deliveries (userID, cartID, streetNumber, streetName, deliveryStatus) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssss", $userID, $cartID, $streetNumber, $streetName);
        if ($stmt->execute()) {
            return DELIVERY_CREATED;
        } else {
            return DELIVERY_FAILED;
        }
    }


    //CAFE SIDE - Marks the order delivered in the deliveries table and orders table
    public function markOrderDelivered($userID, $cartID)
    {
        //mark order delivered in deliveries table
        $stmt1 = $this->con->prepare("UPDATE deliveries SET deliveryStatus = 0 WHERE userID = ? AND cartID = ?");
        $stmt1->bind_param("ss", $userID, $cartID);
        
        //mark order delivered in orders table
        $stmt2 = $this->con->prepare("UPDATE orders SET deliveryStatus = 0 WHERE userID = ? AND cartID = ?");
        $stmt2->bind_param("ss", $userID, $cartID);

        if ($stmt1->execute() && $stmt2->execute()) {
            return ORDER_DELIVERED;
        } else {
            return MARK_ORDER_DELIVERED_FAILED;
        }
    }


    //Place a user order into the 'orders' table
    public function placeOrder($userID, $orderTotal, $deliveryStatus)
    {

        if ($this->isCartActive($userID)) {

            $cartID = $this->getCartIDByUserID($userID);

            $stmt = $this->con->prepare("INSERT INTO orders (cartID, userID, orderTotal, deliveryStatus, orderStatus)
                VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $cartID, $userID, $orderTotal, $deliveryStatus);

            $stmt2 = $this->con->prepare("UPDATE cart SET cartStatus = 0 WHERE cartID = ?");
            $stmt2->bind_param("s", $cartID);

            if ($stmt->execute() && $stmt2->execute()) {
                return ORDER_PLACED;
            }
            return ORDER_FAILED;
        }
        return ORDER_FAILED;
    }


    //Login existing user
    public function userLogin($email, $password)
    {
        if ($this->isEmailExist($email)) {
            $hashed_password = $this->getUsersPasswordByEmail($email);
            if (password_verify($password, $hashed_password)) {
                return USER_AUTHENTICATED;
            } else {
                return USER_PASSWORD_DO_NOT_MATCH;
            }
        } else {
            return USER_NOT_FOUND;
        }
    }


    //Returns a user from the database using registered email address
    public function getUserByEmail($email)
    {
        $stmt = $this->con->prepare("SELECT id, email, firstName, LastName, phone FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $email, $firstName, $lastName, $phone);
        $stmt->fetch();
        $user = array();
        $user['id'] = $id;
        $user['email'] = $email;
        $user['firstName'] = $firstName;
        $user['lastName'] = $lastName;
        $user['phone'] = $phone;
        return $user;
    }


    // Get Menu Items
    public function getItems()
    {
        //AS are present because the Android app expects those names as opposed to those used in the database
        $results = $this->con->query("SELECT `id`, `title` AS name, `shortdesc` AS description, `price` FROM items");

        return $results->fetch_all(MYSQLI_ASSOC);
    }


    // Get Cart Items
    public function getCartItems($userID)
    {

        if ($this->isCartActive($userID)) {

            $cartID = $this->getCartIDByUserID($userID);

            $stmt = $this->con->prepare("SELECT itemTitle AS name, itemPrice AS price, itemQuantity AS quantity FROM cartitem WHERE cartID = ?");
            $stmt->bind_param("s", $cartID);
            $stmt->execute();
            $stmt->bind_result($itemTitle, $itemPrice, $itemQuantity);

            $cart = array();

            while ($stmt->fetch()) {
                $temp = array();

                $temp['name'] = $itemTitle;
                $temp['price'] = $itemPrice;
                $temp['quantity'] = $itemQuantity;

                array_push($cart, $temp);
            }
            return $cart;
        } else {
            return CART_EMPTY;
        }
    }

    //Returns a users cartID by their associated userID
    private function getCartIDByUserID($userID)
    {
        $stmt = $this->con->prepare("SELECT cartID FROM cart WHERE userID = ? AND cartStatus = 1");
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $stmt->bind_result($cartID);
        $stmt->fetch();
        return $cartID;
    }

    //Returns a users associated password for verification during Login
    private function getUsersPasswordByEmail($email)
    {
        $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($password);
        $stmt->fetch();
        return $password;
    }


    //Check email for user record exists in database
    private function isEmailExist($email)
    {
        $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }


    //Check that the item exists in the users cart
    private function isCartItemExist($cartID, $itemID)
    {
        $stmt = $this->con->prepare("SELECT * FROM cartItem WHERE cartID = ? AND itemID = ?");
        $stmt->bind_param("ss", $cartID, $itemID);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }


    //Check that the user has an active cart
    private function isCartActive($userID)
    {
        $stmt = $this->con->prepare("SELECT * FROM cart WHERE userID = ? AND cartStatus = 1");
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }


    //CAFE SIDE - Getting order items for each order as they are clicked
    public function getOrderItems($cartID)
    {
            $stmt = $this->con->prepare("SELECT itemID, itemTitle, itemQuantity FROM cartitem WHERE cartID = ?");
            $stmt->bind_param("s", $cartID);
            $stmt->execute();
            $stmt->bind_result($itemID,$itemTitle, $itemQuantity);

            $cart = array();

            while ($stmt->fetch()) {
                $temp = array();

                $temp['itemID'] = $itemID;
                $temp['itemTitle'] = $itemTitle;
                $temp['quantity'] = $itemQuantity;

                array_push($cart, $temp);
            }
            return $cart;
    }

    //returns staff from database using id
    public function getStaffByID($staffID)
    {
        $stmt = $this->con->prepare("SELECT staffID, firstName, lastName, staffLevel FROM staff WHERE staffID = ?");
        $stmt->bind_param("s", $staffID);
        $stmt->execute();
        $stmt->bind_result($staffID, $firstName, $lastName, $staffLevel);
        $stmt->fetch();

        $staff = array();
        $staff['staffID'] = $staffID;
        $staff['firstName'] = $firstName;
        $staff['lastName'] = $lastName;
        $staff['staffLevel'] = $staffLevel;
        return $staff;
    }

    //Login existing staff
    public function staffValidate($staffID)
    {
        if ($this->isStaffExist($staffID)) {
            return STAFF_AUTHENTICATED;
        } else
            return STAFF_NOT_FOUND;
    }

    //Check for staff record exists in database
    private function isStaffExist($staffID)
    {
        $stmt = $this->con->prepare("SELECT * FROM staff WHERE staffID = ?");
        $stmt->bind_param("s", $staffID);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // CAFE SIDE - Get active order list
    public function getOrdersDetails()
    {
        $results = $this->con->query("SELECT orderID, cartID FROM orders WHERE orderStatus = 1");

        return $results->fetch_all(MYSQLI_ASSOC);
    }


    // CAFE SIDE - Get active order list
    public function getDeliveriesDetails()
    {
        $results = $this->con->query("SELECT userID, cartID, streetNumber, streetName FROM deliveries WHERE deliveryStatus = 1");

        return $results->fetch_all(MYSQLI_ASSOC);
    }

    public function notificationToken($token, $email)
    {
        $stmt = $this->con->prepare("UPDATE users SET token = ? WHERE email = ? ");
        $stmt->bind_param("ss",$token ,  $email);

        if ($stmt->execute()){

            return TOKEN_RECEIVED;
        }
        return TOKEN_FAILED;
    }

    function sendPushNotification($to, $data)
    {

        $apiKey = "AAAAhwEX3OM:APA91bF-j4JnxA7LoviQ_3gtk2zyNNrD94i3XgIGDbpeJegme-UJf8qW2lr6-o8e3EapmXCPdgYK4u-feWK-DlvtQkjIh0tg5XXIr9fryj5hd0GbXtNPq0Ho_IFHu6oLlLrtCSIDuVuI";
        $fields = array('to' => $to, "notification" => $data);

        $headers = array('Authorization: key=' . $apiKey, 'Content-Type:application/json');

        $url = 'https://fcm.googleapis.com/fcm/send';

        $ch = curl_init();
        curl_setopt($ch, CURLINFO_REDIRECT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_PROXY_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json - encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decoded($result, true);

    }

    function getToken($userID)
    {
        $stmt = $this->con->prepare('SELECT token FROM users WHERE id = ?');
        $stmt->bind_param("s",$userID);
        $stmt->execute();
        $stmt->bind_result($token);
        $stmt->fetch();


       return $token;
    }
    function sendPushNotify($to = '', $data = array()){

        $api_key = 'ya29.c.Kl7ZB6DJ_FNvheIUhqz8EWkgboTOczm45SwNhFajaXV3BFlZgcDwbIR_6yLrrSS2aprSTK-owDhz6NEEtMx7M6Bi0qDUzCqbAvDhC7VCf-aFRykHLPCwb_1r7apZAoKL';
        $fields = array('to' => $to, 'notification' => $data);

        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$api_key
        );
        $url = 'https://fcm.googleapis.com/fcm/send';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
    function sendingNotification($to)
    {
        $title="Working";
        $message="It finally works";

        define( 'API_ACCESS_KEY', 'ya29.c.Kl7ZB5iPydfpWPPqAwrnnoAHuK1gkLv-OKiGi2qee3UroRCd_7Nu7p14nggkeLa7S_bIywGN2--TVZQZt8n6mdqg64N7IbfkUIjhPOfmjM5S5gomIovVq0JW5UcuISjw');
        $msg = array
        (
            'body'   =>$message,
            'title'     => $title,

        );

        $fields = array
        (
            'registration_ids'            =>  $to  ,                 // "/topics/alert",
            'priority' =>"high",
            'data' => array("title"=>$title,"body"=>$message)

        );

        $headers = array
        (
            'Authorization: Bearer' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );

//        // API access key from Google API's Console
//        define('API_ACCESS_KEY','ya29.c.Kl7ZB5iPydfpWPPqAwrnnoAHuK1gkLv-OKiGi2qee3UroRCd_7Nu7p14nggkeLa7S_bIywGN2--TVZQZt8n6mdqg64N7IbfkUIjhPOfmjM5S5gomIovVq0JW5UcuISjw');
//
//        $url = 'https://fcm.googleapis.com/fcm/send' ;
//
//
//// prepare the message
//        $msg = array
//        (
//            'body' 	=> 'Body  Of Notification',
//            'title'	=> 'Title Of Notification',
////            'icon'	=> 'myicon',/*Default Icon*/
////            'sound' => 'mySound'/*Default sound*/
//        );
//
//
//        $fields = array
//        (
//            'to'		=> $to,
//            'notification'	=> $msg
//        );
//
//
//        $headers = array
//        (
//            'Authorization: Bearer ' . API_ACCESS_KEY,
//            'Content-Type: application/json'
//        );
//
//        $ch = curl_init();
//        curl_setopt( $ch,CURLOPT_URL,$url);
//        curl_setopt( $ch,CURLOPT_POST,true);
//        curl_setopt( $ch,CURLOPT_HTTPHEADER,$headers);
//        curl_setopt( $ch,CURLOPT_RETURNTRANSFER,true);
//        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER,false);
//        curl_setopt( $ch,CURLOPT_POSTFIELDS,json_encode($fields));
//        $result = curl_exec($ch);
//        curl_close($ch);

        return $result;
    }

}
