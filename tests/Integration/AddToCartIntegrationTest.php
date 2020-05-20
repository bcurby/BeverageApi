<?php
declare(strict_types=1);

namespace Tests\Integration;

use \DbOperations;
use Tests\TestCase;

class AddToCartIntegrationTest extends TestCase
{
    
    protected $dbOperations;

    public function setUp()
    {
        $this->dbOperations = new DbOperations;
    }

    /**
     * @dataProvider cartItemsProvider
     */
    public function testAddToCart($userID, $itemID, $itemTitle, $itemPrice, $itemQuantity)
    {
        $addToCartResult = $this->dbOperations->addToCart($userID, $itemID, $itemTitle, $itemPrice, $itemQuantity, "active");

        $this->assertEquals(ADDED_TO_CART, $addToCartResult, "FAILURE: Item was not added to cart");

        $cartItems = $this->dbOperations->getCartItems($userID);

        $this->assertNotEquals(CART_EMPTY, $cartItems, "FAILURE: Cart still empty after adding item");
        $this->assertIsArray($cartItems, "FAILURE: Get cart items returned non-array value");

        $subjectItem = [
            'name' => $itemTitle,
            'price' => $itemPrice,
            'quantity' => $itemQuantity
        ];

        $matchingItems = array_filter($cartItems, function($cartItem) use ($subjectItem) {
            return $this->checkMatchingItems($cartItem, $subjectItem);
        });

        $this->assertNotEmpty($matchingItems, "FAILURE: Item was not found in getCartItems result");

    }

    public function cartItemsProvider()
    {
        return [
            [1111, 3, "coffee", 43.56, 1 ]
        ];
    }

    protected function checkMatchingItems($itemOne, $itemTwo)
    {
        return (
            $itemOne['name'] == $itemTwo['name']
            && $itemOne['price'] == $itemTwo['price']
            && $itemOne['quantity'] == $itemTwo['quantity']
        );
    }

}
