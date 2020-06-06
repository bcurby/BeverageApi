<?php
declare(strict_types=1);

namespace Tests\Integration;

use \DbOperations;
use Tests\TestCase;

class PlaceOrderIntegrationTest extends TestCase
{
    
    protected $dbOperations;

    public function setUp()
    {
        $this->dbOperations = new DbOperations;
    }

    /**
     * @dataProvider orderProvider
     */
    public function testPlaceOrder($userID, $orderTotal)
    {
        $placeOrderResults = $this->dbOperations->placeOrder($userID, $orderTotal);

        $this->assertEquals(ORDER_PLACED, $placeOrderResults, "FAILURE: Order did not occur");
    }

    public function orderProvider()
    {
        return [
            [ 1111, 43.56 ] // just like the test for AddToCart we can use the same userID to allow it to work with this test
        ];
    }
}
