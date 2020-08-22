<?php

    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASSWORD', '');
    DEFINE('DB_NAME', 'beverage_db');

    define('USER_CREATED', 201);
    define('USER_EXISTS', 403);
    define('USER_FAILURE', 422); 

    define('USER_AUTHENTICATED', 202);
    define('USER_NOT_FOUND', 200); 
    define('USER_PASSWORD_DO_NOT_MATCH', 401);

    define('STAFF_AUTHENTICATED', 202);
    define('STAFF_NOT_FOUND', 200);

    define('ADDED_TO_CART', 201);
    define('ITEM_ALREADY_IN_CART', 403);
    define('CART_EMPTY', 303);

    define('ORDER_PLACED', 201);
    define('ORDER_FAILED', 422);

    define("CART_ITEMS_FOUND", 200);
    define("CART_ITEMS_FAILED", 402);

    define("CART_EMPTY_PASS", 201);
    define("CART_EMPTY_FAILED", 402);

    define("DELIVERY_CREATED", 201);
    define("DELIVERY_FAILED", 422);
    
    define("ORDER_DELIVERED", 201);
    define("MARK_ORDER_DELIVERED_FAILED", 422);

    define("TOKEN_RECEIVED", 501);
    define("TOKEN_FAILED", 502);

    define("MESSAGE_SENT", 601);
    define("MESSAGE_FAILED", 602);
