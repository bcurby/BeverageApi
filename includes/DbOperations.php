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
    public function addToCart($userID, $itemID, $itemTitle, $itemPrice, $itemQuantity, $itemSize, $itemMilk, $itemSugar, 
    $itemDecaf, $itemVanilla, $itemCaramel, $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment, $itemType
    ) {

        $itemStock = $this->getItemStock($itemID);

        //Check that enough of the item is in stock
        if ($itemStock - $itemQuantity >= 5) {

            //does this user have an activer cart
            if ($this->isCartActive($userID)) {

                //if so then get the cartID
                $cartID = $this->getCartIDByUserID($userID);

                // check if the cartItem exists in the cart
                if (!$this->isCartItemExist($cartID, $itemID, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla, $itemCaramel, 
                $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment, $itemType)) {

                    $stmt = $this->con->prepare("INSERT INTO cartitem (cartID, itemID, itemTitle, itemPrice, itemQuantity, itemSize, 
                    itemMilk, itemSugar, itemDecaf, itemVanilla, itemCaramel, itemChocolate, itemWhippedCream, itemFrappe, itemHeated, itemComment, itemType) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param(
                        "sssssssssssssssss", $cartID, $itemID, $itemTitle, $itemPrice, $itemQuantity, $itemSize, $itemMilk, $itemSugar, $itemDecaf, 
                        $itemVanilla, $itemCaramel, $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment, $itemType);
                    
                        $newItemStock = $itemStock - $itemQuantity;
                        $stmt2 = $this->con->prepare("UPDATE items SET itemStock = ? WHERE id = ?");
                        $stmt2->bind_param(
                            "ss", $newItemStock, $itemID);

                    if ($stmt->execute() && $stmt2->execute()) {
                        return ADDED_TO_CART;
                    } else {
                        return PROBLEM_ADDING_TO_CART;
                    }
                }
                //When item exists already in the user cart return item quantity
                $cartItemQuantity = $this->getCartItemQuantity($cartID, $itemID, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla, $itemCaramel, 
                $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment, $itemType);

                $newQuantity = $itemQuantity + $cartItemQuantity;

                $stmt = $this->con->prepare("UPDATE cartitem SET itemQuantity = ? WHERE cartID = ? AND itemID = ? AND itemSize = ? AND itemMilk = ?
                AND itemSugar = ? AND itemDecaf = ? AND itemVanilla = ? AND itemCaramel = ? AND itemChocolate = ? 
                AND itemWhippedCream = ? AND itemFrappe = ? AND itemHeated = ? AND itemComment = ? AND itemType = ?");
                $stmt->bind_param(
                    "sssssssssssssss", $newQuantity, $cartID, $itemID, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla, $itemCaramel, 
                    $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment, $itemType);

                    $newItemStock = $itemStock - $itemQuantity;
                        $stmt2 = $this->con->prepare("UPDATE items SET itemStock = ? WHERE id = ?");
                        $stmt2->bind_param(
                            "ss", $newItemStock, $itemID);

                if ($stmt->execute() && $stmt2->execute()) {
                return ADDED_TO_CART;
                } else {
                    return PROBLEM_ADDING_TO_CART;
                }
            } else {
                $stmt = $this->con->prepare("INSERT INTO cart (userID, cartStatus) VALUES (?, 1)");
                $stmt->bind_param("s", $userID);
                $stmt->execute();
                $cartID = $this->getCartIDByUserID($userID);
                $stmt = $this->con->prepare("INSERT INTO cartitem (cartID, itemID, itemTitle, itemPrice, itemQuantity, itemSize, 
                itemMilk, itemSugar, itemDecaf, itemVanilla, itemCaramel, itemChocolate, itemWhippedCream, itemFrappe, itemHeated, itemComment, itemType) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "sssssssssssssssss", $cartID, $itemID, $itemTitle, $itemPrice, $itemQuantity, $itemSize, $itemMilk, $itemSugar,$itemDecaf, $itemVanilla, 
                    $itemCaramel, $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment, $itemType);

                    $newItemStock = $itemStock - $itemQuantity;
                        $stmt2 = $this->con->prepare("UPDATE items SET itemStock = ? WHERE id = ?");
                        $stmt2->bind_param(
                            "ss", $newItemStock, $itemID);

                if ($stmt->execute() && $stmt2->execute()) {
                    return ADDED_TO_CART;
                } else {
                    return PROBLEM_ADDING_TO_CART;
                }
            }
        } else {
        return NOT_ENOUGH_ITEM_STOCK;
        }   
    }


    

    //Empty user cart
    public function emptyCart($userID)
    {
        $cartID = $this->getCartIDByUserID($userID);
        $stmt = $this->con->prepare("UPDATE cart SET cartStatus = 0 WHERE cartID = $cartID");

        //Get item IDs and quantities for each cart item
        $cart = $this->getCartItemIDsAndQuantities($cartID);
    
        $arraylength = count ($cart);
    
        $i = 0;

        while ($i < $arraylength) {

        $itemID = $cart[$i]['itemID'];
        $itemQuantity = $cart[$i]['itemQuantity'];

        $i++;

        //get actual stock level for item
        $itemStock = $this->getItemStock($itemID);
        $newItemStock = $itemStock + $itemQuantity;


        $stmt2 = $this->con->prepare("UPDATE items SET itemStock = ? WHERE id = ?");
        $stmt2->bind_param("ss", $newItemStock, $itemID);
        $stmt2->execute();
        }
        if ($stmt->execute()) {
            return CART_EMPTY_PASS;
        } else {
            return CART_EMPTY_FAILED;
        }
        
    }    


    //get Cart Item ID & matching quantities array
    public function getCartItemIDsAndQuantities($cartID)
    {
        $stmt = $this->con->prepare("SELECT itemID, itemQuantity FROM cartitem WHERE cartID = ? AND itemType = 'food'");
        $stmt->bind_param("s", $cartID);
        $stmt->execute();
        $stmt->bind_result($itemID, $itemQuantity);

        $cart = array();

        while ($stmt->fetch()) {
             $temp = array();

             //$temp['cartItemID'] = $cartItemID;
             $temp['itemID'] = $itemID;
             $temp['itemQuantity'] = $itemQuantity;

            array_push($cart, $temp);
        }
        return $cart;
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

            $stmt = $this->con->prepare("INSERT INTO orders (cartID, userID, orderTotal, deliveryStatus, orderStatus, assignedStaff)
                VALUES (?, ?, ?, ?, 1, 0)");
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
    public function getItems($itemType)
    {
        //AS are present because the Android app expects those names as opposed to those used in the database
        $stmt = $this->con->prepare("SELECT `id`, `title` AS name, `shortdesc` AS description, `price`, milk, sugar, decaf, extras, frappe, heated, itemType, itemStock FROM items WHERE itemType = ?");
        $stmt->bind_param("s", $itemType);
        $stmt->execute();
        $stmt->bind_result($itemID, $itemTitle, $itemDescription, $itemPrice, $itemMilk, $itemSugar, $itemDecaf, $itemExtras, $itemFrappe, $itemHeated, $itemType, $itemStock);

        $menu = array();

        while ($stmt->fetch()) {
            $temp = array();

            $temp['id'] = $itemID;
            $temp['name'] = $itemTitle;
            $temp['description'] = $itemDescription;
            $temp['price'] = $itemPrice;
            $temp['milk'] = $itemMilk;
            $temp['sugar'] = $itemSugar;
            $temp['decaf'] = $itemDecaf;
            $temp['extras'] = $itemExtras;
            $temp['frappe'] = $itemFrappe;
            $temp['heated'] = $itemHeated;
            $temp['itemType'] = $itemType;
            $temp['itemStock'] = $itemStock;

            array_push($menu, $temp);
        }
        return $menu;
    }


    // Get Cart Items
    public function getCartItems($userID)
    {

        if ($this->isCartActive($userID)) {

            $cartID = $this->getCartIDByUserID($userID);

            $stmt = $this->con->prepare("SELECT itemID, itemTitle AS name, itemPrice AS price, itemQuantity AS quantity, itemSize, itemMilk, itemSugar, 
            itemDecaf, itemVanilla, itemCaramel, itemChocolate, itemWhippedCream, itemFrappe, itemHeated, itemComment, itemType FROM cartitem WHERE cartID = ?");
            $stmt->bind_param("s", $cartID);
            $stmt->execute();
            $stmt->bind_result($itemID, $itemTitle, $itemPrice, $itemQuantity, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla, $itemCaramel, 
            $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment, $itemType);

            $cart = array();

            while ($stmt->fetch()) {
                $temp = array();

                $temp['itemID'] = $itemID;
                $temp['name'] = $itemTitle;
                $temp['price'] = $itemPrice;
                $temp['quantity'] = $itemQuantity;
                $temp['itemSize'] = $itemSize;
                $temp['itemMilk'] = $itemMilk;
                $temp['itemSugar'] = $itemSugar;
                $temp['itemDecaf'] = $itemDecaf;
                $temp['itemVanilla'] = $itemVanilla;
                $temp['itemCaramel'] = $itemCaramel;
                $temp['itemChocolate'] = $itemChocolate;
                $temp['itemWhippedCream'] = $itemWhippedCream;
                $temp['itemFrappe'] = $itemFrappe;
                $temp['itemHeated'] = $itemHeated;
                $temp['itemComment'] = $itemComment;
                $temp['itemType'] = $itemType;

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
    private function isCartItemExist($cartID, $itemID, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla, 
    $itemCaramel, $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated) {
        $stmt = $this->con->prepare("SELECT * FROM cartitem WHERE cartID = ? AND itemID = ? AND itemSize = ? AND itemMilk = ?
         AND itemSugar = ? AND itemDecaf = ? AND itemVanilla = ? AND itemCaramel = ? AND itemChocolate = ? 
         AND itemWhippedCream = ? AND itemFrappe = ? AND itemHeated = ?");
        $stmt->bind_param(
            "ssssssssssss", $cartID, $itemID, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla, 
            $itemCaramel, $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated);
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
        $stmt = $this->con->prepare("SELECT itemID, itemTitle, itemQuantity, itemMilk, itemSugar, itemDecaf, 
        itemVanilla, itemCaramel, itemChocolate, itemWhippedCream, itemFrappe, itemHeated, itemComment FROM cartitem WHERE cartID = ?");
        $stmt->bind_param("s", $cartID);
        $stmt->execute();
        $stmt->bind_result($itemID, $itemTitle, $itemQuantity, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla, 
        $itemCaramel, $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment);

        $cart = array();

        while ($stmt->fetch()) {
            $temp = array();

            $temp['itemID'] = $itemID;
            $temp['itemTitle'] = $itemTitle;
            $temp['quantity'] = $itemQuantity;
            $temp['itemMilk'] = $itemMilk;
            $temp['itemSugar'] = $itemSugar;
            $temp['itemDecaf'] = $itemDecaf;
            $temp['itemVanilla'] = $itemVanilla;
            $temp['itemCaramel'] = $itemCaramel;
            $temp['itemChocolate'] = $itemChocolate;
            $temp['itemWhippedCream'] = $itemWhippedCream;
            $temp['itemFrappe'] = $itemFrappe;
            $temp['itemHeated'] = $itemHeated;
            $temp['itemComment'] = $itemComment;

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
        $results = $this->con->query("SELECT orderID, cartID, assignedStaff FROM orders WHERE orderStatus = 1");

        return $results->fetch_all(MYSQLI_ASSOC);
    }


    // CAFE SIDE - Get active order list
    public function getDeliveriesDetails()
    {
        $results = $this->con->query("SELECT userID, cartID, streetNumber, streetName FROM deliveries WHERE deliveryStatus = 1");

        return $results->fetch_all(MYSQLI_ASSOC);
    }

    // CAFE SIDE - Delete menu item
    public function deleteMenuItem($itemID)
    {
        $stmt = $this->con->prepare("DELETE FROM items WHERE id = $itemID");
        if ($stmt->execute()) {
            return STAFF_DELETE_ITEM_PASSED;
        } else {
            return STAFF_DELETE_ITEM_FAILED;
        }
    }

    // CAFE SIDE - add order to queue and bind staff member to that order
    public function addToQueue($staffID, $orderID, $cartID) {
        if (!$this->doesOrderExistInQueue($orderID)) {
            $stmt = $this->con->prepare("INSERT INTO staffqueue (staffID, orderID, cartID) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $staffID, $orderID, $cartID);
            if ($stmt->execute()) {
                $stmt = $this->con->prepare("UPDATE orders SET assignedStaff = $staffID WHERE orderID = $orderID");
                $stmt->execute();
                return ORDER_ADDED_TO_QUEUE;
            } else {
                return ORDER_ADDED_TO_QUEUE_FAILED;
            }
        } else {
            return ORDER_ALREADY_EXISTS_IN_QUEUE;
        }
    }

    //CAFE SIDE - Check for staff record exists in database
    private function doesOrderExistInQueue($orderID) {
        $stmt = $this->con->prepare("SELECT orderID from staffqueue WHERE orderID = $orderID");
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // CAFE SIDE - Remove staff member from order
    public function makeOrderAvailable($staffID, $orderID, $cartID) {
        $stmt1 = $this->con->prepare("UPDATE orders SET assignedStaff = 1 WHERE assignedStaff = $staffID AND orderID = $orderID AND cartID = $cartID");
        $stmt2 = $this->con->prepare("UPDATE staffqueue SET staffID = 1 WHERE staffID = $staffID AND orderID = $orderID AND cartID = $cartID");
        if ($stmt1->execute() && $stmt2->execute()) {
            return ORDER_AVAILABLE;
        } else {
            return ORDER_AVAILABLE_FAILED;
        }
    }

    // CAFE SIDE - Assigns staff member to order
    public function assignStaffToOrder($staffID, $orderID, $cartID) {
        $stmt1 = $this->con->prepare("UPDATE orders SET assignedStaff = $staffID WHERE assignedStaff = 1 AND orderID = $orderID AND cartID = $cartID");
        $stmt2 = $this->con->prepare("UPDATE staffqueue SET staffID = $staffID WHERE staffID = 1 AND orderID = $orderID AND cartID = $cartID");
        if ($stmt1->execute() && $stmt2->execute()) {
            return STAFF_ASSIGNED;
        } else {
            return STAFF_ASSIGNED_FAILED;
        }
    }

    //Get cartitem quantity
    public function getCartItemQuantity($cartID, $itemID, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla, $itemCaramel, 
    $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment, $itemType) {
        $stmt = $this->con->prepare("SELECT itemQuantity FROM cartitem WHERE cartID = ? AND itemID = ? AND itemSize = ? AND itemMilk = ?
         AND itemSugar = ? AND itemDecaf = ? AND itemVanilla = ? AND itemCaramel = ? AND itemChocolate = ? 
         AND itemWhippedCream = ? AND itemFrappe = ? AND itemHeated = ? AND itemComment = ? AND itemType = ?");
        $stmt->bind_param(
            "ssssssssssssss", $cartID, $itemID, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla, $itemCaramel, $itemChocolate, 
            $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment, $itemType);
        $stmt->execute();
        $stmt->bind_result($cartItemQuantity);
        $stmt->fetch();
        return $cartItemQuantity;
    }


    //get Item Stock
    public function getItemStock($itemID)
    {
        $stmt = $this->con->prepare("SELECT itemStock FROM items WHERE id = ?");
        $stmt->bind_param("s", $itemID);
        $stmt->execute();
        $stmt->bind_result($itemStock);
        $stmt->fetch();
        return $itemStock;
    }


    // Get single menu item
    public function getMenuItem($itemID)
    {
        //AS are present because the Android app expects those names as opposed to those used in the database
        $stmt = $this->con->prepare("SELECT `id`, `title` AS name, `shortdesc` AS description, `price`, milk, sugar, decaf, extras, frappe, heated, itemType, itemStock FROM items WHERE id = ?");
        $stmt->bind_param("s", $itemID);
        $stmt->execute();
        $stmt->bind_result($itemID, $itemTitle, $itemDescription, $itemPrice, $itemMilk, $itemSugar, $itemDecaf, $itemExtras, $itemFrappe, $itemHeated, $itemType, $itemStock);

        $item = array();

        while ($stmt->fetch()) {
            $temp = array();

            $temp['id'] = $itemID;
            $temp['name'] = $itemTitle;
            $temp['description'] = $itemDescription;
            $temp['price'] = $itemPrice;
            $temp['milk'] = $itemMilk;
            $temp['sugar'] = $itemSugar;
            $temp['decaf'] = $itemDecaf;
            $temp['extras'] = $itemExtras;
            $temp['frappe'] = $itemFrappe;
            $temp['heated'] = $itemHeated;
            $temp['itemType'] = $itemType;
            $temp['itemStock'] = $itemStock;

            array_push($item, $temp);
        }
        return $item;
    }

    
    // Delete cart item
    public function deleteCartItem($userID, $itemID, $itemTitle, $itemPrice, $itemQuantity, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla,
    $itemCaramel, $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment) {
        $cartID = $this->getCartIDByUserID($userID);
        
        $stmt = $this->con->prepare("DELETE FROM cartitem WHERE cartID = ? AND itemTitle = ? AND itemPrice = ? AND itemSize = ? AND itemMilk = ? AND itemSugar = ?
        AND itemDecaf = ? AND itemVanilla = ? AND itemCaramel = ? AND itemChocolate = ? AND itemWhippedCream = ? AND itemFrappe = ? AND itemHeated = ?
        AND itemComment = ?");
        $stmt->bind_param("ssssssssssssss", $cartID, $itemTitle, $itemPrice, $itemSize, $itemMilk, $itemSugar, $itemDecaf, $itemVanilla,
        $itemCaramel, $itemChocolate, $itemWhippedCream, $itemFrappe, $itemHeated, $itemComment);

        //get actual stock level for item
        $itemStock = $this->getItemStock($itemID);
        $newItemStock = $itemStock + $itemQuantity;


        $stmt2 = $this->con->prepare("UPDATE items SET itemStock = ? WHERE id = ?");
        $stmt2->bind_param("ss", $newItemStock, $itemID);
        $stmt2->execute();
        if ($stmt->execute()) {
            return DELETE_CART_ITEM_PASSED;
        } else {
            return DELETE_CART_ITEM_FAILED;
        }
    }

    
    // CAFE SIDE - Get the menu items for the staff menu and send all the attributes of each item
    public function getItemsForStaffMenu() {
        $results = $this->con->query("SELECT `id`, `title` AS name, `shortdesc` AS description, `price`, milk, sugar, decaf, extras, frappe, heated, itemType, itemStock, itemTime FROM items");

        return $results->fetch_all(MYSQLI_ASSOC);
    }

    // CAFE SIDE - Add Menu item
    public function addMenuItem($itemTitle, $itemShortDesc, $itemPriceDouble, $milkOption, $sugarOption, $decafOption, $extrasOption, $frappeOption, $heatedOption, $itemType, $itemTimeInt) {    
        if (!$this->doesItemExistInItems($itemTitle)) {
            $stmt = $this->con->prepare("INSERT INTO items(title, shortdesc, price, milk, sugar, decaf, extras, frappe, heated, itemType, itemStock, itemTime) 
            VALUES (?, ?, $itemPriceDouble, $milkOption, $sugarOption, $decafOption, $extrasOption, $frappeOption, $heatedOption, ?, 10, $itemTimeInt)");
            $stmt->bind_param("sss", $itemTitle, $itemShortDesc, $itemType);
            if ($stmt->execute()) {
                return ITEM_ADDED;
            } else {
                return ITEM_FAILED_TO_ADD;
            }
        } else {
             return ITEM_TITLE_EXISTS;
        }
    }

    //CAFE SIDE - Check for staff record exists in database
    private function doesItemExistInItems($itemTitle) {
        $stmt = $this->con->prepare("SELECT title from items WHERE title = ?");
        $stmt->bind_param("s", $itemTitle);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
}
