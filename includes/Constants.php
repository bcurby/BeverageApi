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
    define('PROBLEM_ADDING_TO_CART', 403);
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

    define("STAFF_DELETE_ITEM_PASSED", 201);
    define("STAFF_DELETE_ITEM_FAILED", 402);

    define("ORDER_ADDED_TO_QUEUE", 201);
    define("ORDER_ADDED_TO_QUEUE_FAILED", 402);
    define("ORDER_ALREADY_EXISTS_IN_QUEUE", 403);

    define("ORDER_AVAILABLE", 201);
    define("ORDER_AVAILABLE_FAILED", 402);

    define("STAFF_ASSIGNED", 201);
    define("STAFF_ASSIGNED_FAILED", 401);

	define("ORDER_RECORDED", 201);
	define("ORDER_RECORDED_FAILED", 402);

    define("ORDER_FOUND", 201);
    define("ORDER_NOT_FOUND", 402);

	define("ORDER_DELETED", 201);
	define("ORDER_DELETED_FAILED", 402);

	define("STAFF_QUEUE_DELETED", 201);
	define("STAFF_QUEUE_DELETED_FAILED", 402);

	define("UPDATED_ITEM_STATUS", 201);
	define("UPDATED_ITEM_STATUS_FAILED", 402);

	define("ITEM_STATUS_RECIEVED", 201);
	define("ITEM_STATUS_NOT_RECIEVED", 402);