<?php

/**
 * @author: Martin Liprt
 * @email: tuxxx128@gmail.com
 */

namespace Tuxxx128\G2aPay;

interface IG2aPay
{
    const CHECKOUT_PRODUCTION_URL = 'https://checkout.pay.g2a.com',
          CHECKOUT_TEST_URL = 'https://checkout.test.pay.g2a.com';

   const REST_PRODUCTION_URL = 'https://pay.g2a.com/rest',
          REST_TEST_URL = 'https://www.test.pay.g2a.com/rest';
}