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
    public function addToCart($userID, $itemID, $itemTitle, $itemPrice, $itemQuantity, $cartStatus)
    {
        if (!$this->isCartItemExist($userID, $itemID, $cartStatus)) {
            $stmt = $this->con->prepare("INSERT INTO cart (userID, itemID, itemTitle, itemPrice, itemQuantity, cartStatus) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $userID, $itemID, $itemTitle, $itemPrice, $itemQuantity, $cartStatus);
            if ($stmt->execute()) {
                return ADDED_TO_CART;

            }
        }
        return ITEM_ALREADY_IN_CART;
    }


    //Place a user order into the 'orders' table
    public function placeOrder($userID, $orderTotal)
    {
        $stmt = $this->con->prepare("INSERT INTO orders SELECT * FROM cart WHERE userID = ? AND cartStatus = 'active'");
        $stmt->bind_param("s", $userID);

        $stmt2 = $this->con->prepare("INSERT INTO orderpayment (userID, orderTotal, orderStatus) VALUES (?, ?, 'active')");
        $stmt2->bind_param("ss", $userID, $orderTotal);

        $stmt3 = $this->con->prepare("UPDATE cart SET cartStatus = 'ordered' WHERE userID = ? AND cartStatus = 'active'");
        $stmt3->bind_param("s", $userID);

        if ($stmt->execute() && $stmt2->execute() && $stmt3->execute()) {
            return ORDER_PLACED;

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

            $stmt = $this->con->prepare("SELECT itemTitle AS name, itemPrice AS price, itemQuantity AS quantity FROM cart WHERE cartStatus = 'active' AND userID = ?");
            $stmt->bind_param("s", $userID);
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

    //Getting cart items for backend
    public function getItemsCart($orderID)
    {
            $stmt = $this->con->prepare("SELECT itemID, itemTitle, itemQuantity FROM cartitem WHERE cartID = ?");
            $stmt->bind_param("s", $orderID);
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
    private function isCartItemExist($userID, $itemID, $cartStatus)
    {
        $stmt = $this->con->prepare("SELECT cartID FROM cart WHERE userID = ? AND itemID = ? AND cartStatus = ?");
        $stmt->bind_param("sss", $userID, $itemID, $cartStatus);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }


    //Check that the user has an active cart
    private function isCartActive($userID)
    {
        $stmt = $this->con->prepare("SELECT * FROM cart WHERE userID = ? AND cartStatus = 'active'");
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

// Get Menu Items
    public function getOrdersDetails()
    {
        $results = $this->con->query("SELECT `orderID`, `status` FROM orders");

        return $results->fetch_all(MYSQLI_ASSOC);
    }

//returns staff from database using id
    public function getStaffByID($staffID)
    {
        $stmt = $this->con->prepare("SELECT id, firstName, lastName, staffLevel FROM staff WHERE id = ?");
        $stmt->bind_param("s", $staffID);
        $stmt->execute();
        $stmt->bind_result($staffID, $firstName, $lastName, $staffLevel);
        $stmt->fetch();

        $staff = array();
        $staff['id'] = $staffID;
        $staff['firstName'] = $firstName;
        $staff['lastName'] = $lastName;
        $staff['staffLevel'] = $staffLevel;
        return $staff;
    }

    //Login existing staff
    public function staffValidate($staffId)
    {
        if ($this->isStaffExist($staffId)) {
            return STAFF_AUTHENTICATED;
        } else
            return STAFF_NOT_FOUND;
    }

    //Check for staff record exists in database
    private function isStaffExist($email)
    {
        $stmt = $this->con->prepare("SELECT id FROM staff WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
}