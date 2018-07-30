<?php

use PHPUnit\Framework\TestCase;

class G2aPayTest extends TestCase
{

    private $a2aPayApi;

    public function setup()
    {
        $this->a2aPayApi = new \Tuxxx128\G2aPay\G2aPayApi('string', 'string', false);
    }

    public function testCheckModes()
    {
        $modeProduction = true;
        $modeTest = false;

        $a2aPayApiP = new \Tuxxx128\G2aPay\G2aPayApi('string', 'string', $modeProduction);
        $a2aPayApiT = new \Tuxxx128\G2aPay\G2aPayApi('string', 'string', $modeTest);

        $this->assertEquals(true, $a2aPayApiP->checkIsProductionEnvironment());
        $this->assertEquals(true, $a2aPayApiT->checkIsTestEnvironment());

        $this->assertEquals(\Tuxxx128\G2aPay\IG2aPay::CHECKOUT_PRODUCTION_URL, $a2aPayApiP->getApIendpointUrl($modeProduction));
        $this->assertEquals(\Tuxxx128\G2aPay\IG2aPay::CHECKOUT_TEST_URL, $a2aPayApiP->getApIendpointUrl($modeTest));
    }

    public function testCalculatingTotalPriceFromItems()
    {
        $mockyItemExpensive = \Mockery::mock(\Tuxxx128\G2aPay\G2aPayItem::class);
        
        $mockyItemExpensive->shouldReceive('getData')
            ->andReturn(\Tuxxx128\G2aPay\G2aPayItem::class);

        $mockyItemCheap = clone $mockyItemExpensive;

        $mockyItemExpensive->price = 103;
        $mockyItemExpensive->amount = 103;
        
        $this->a2aPayApi->addItem($mockyItemExpensive);

        $mockyItemCheap->price = 25;
        $mockyItemCheap->amount = 25;

        $this->a2aPayApi->addItem($mockyItemCheap);

        $this->assertEquals(128, $this->a2aPayApi->getTotalPrice());
    }

}