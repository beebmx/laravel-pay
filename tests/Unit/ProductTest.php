<?php

namespace Beebmx\LaravelPay\Tests\Unit;

use Beebmx\LaravelPay\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /** @test */
    public function a_quantity_of_product_is_optional()
    {
        $product = new Product('Product name', 500);

        $this->assertEquals(1, $product->quantity);
    }

    /** @test */
    public function a_quantity_of_product_can_be_set()
    {
        $product = new Product('Product name', 500, 5);

        $this->assertEquals(5, $product->quantity);
    }
}
