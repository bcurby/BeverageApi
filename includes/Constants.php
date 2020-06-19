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

    define('ADDED_TO_CART', 201);
    define('ITEM_ALREADY_IN_CART', 403);
    define('CART_EMPTY', 303);

    define('ORDER_PLACED', 201);
    define('ORDER_FAILED', 422);


