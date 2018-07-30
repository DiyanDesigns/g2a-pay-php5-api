<?php

/**
 * @author: Martin Liprt
 * @email: tuxxx128@gmail.com
 */

namespace Tuxxx128\G2aPay;

class G2aPayItem extends \stdClass
{
    /** @var array */
    private $data = [];

    public function __set($nm, $val)
    {
        $this->data[$nm] = $val;

        if ($nm == 'price' || $nm = 'qty') {
            $this->changeAmount();
        }
    }

    public function __get($nm)
    {
        if(isset($this->data[$nm])) {
            return $this->data[$nm];
        }

        return false;
    }

    private function changeAmount()
    {
        if ($this->price && $this->qty) {
            $this->data['amount'] = floatval($this->price * $this->qty);

            return true;
        }

        return false;
    }

    public function getData()
    {
        return $this->data;
    }

    public function itemTemplate()
    {
        $this->id     = 1;
        $this->sku    = 1;
        $this->name   = 'no name';
        $this->amount = 2;
        $this->price  = 1;
        $this->qty    = 1;
        $this->url    = '';
        $this->extra  = false;
        $this->type   = '';

        return $this;
    }
}