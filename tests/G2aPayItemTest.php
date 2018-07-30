<?php

use PHPUnit\Framework\TestCase;

class G2aPayItemTest extends TestCase
{

    public function testCheckSetGet()
    {
        $itemListObj = new \Tuxxx128\G2aPay\G2aPayItem;
        $itemListObj->test = 'testValue';
        $itemListObj->testotro = 'testOtroValue';

        $this->assertEquals('testValue', $itemListObj->test);
        $this->assertEquals('testOtroValue', $itemListObj->testotro);
    }

    public function testAddItemsToList()
    {
         $itemObj = new \Tuxxx128\G2aPay\G2aPayItem;
         $items = [];

         // item 1
         $newItemOne = $itemObj->itemTemplate();
         $this->assertEquals('no name', $newItemOne->name);
         $this->assertEquals(1, $newItemOne->price);

         // item 2
         $newItemTwo = $itemObj->itemTemplate();
         $this->assertEquals(1, $newItemTwo->price);
         $this->assertEquals(1, $newItemTwo->amount);

         $newItemTwo->price = 15;
         $this->assertEquals(15, $newItemTwo->price);
         $this->assertEquals(1, $newItemTwo->qty);
         $this->assertEquals(floatval($newItemTwo->price * $newItemTwo->qty), $newItemTwo->amount);

         $newItemTwo->qty = 3;
         $this->assertEquals(45, $newItemTwo->amount);

         $this->assertEquals('no name', $newItemTwo->name);

         $items[] = $newItemOne;
         $items[] = $newItemTwo;

         $this->assertEquals(2, count($items));
    }
}