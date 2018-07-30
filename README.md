***No Maintenance Intended***

# ***G2A PAY - PHP API*** #
Complex PHP5 CURL library. See also official documentation: https://pay.g2a.com/documentation#introduction

## ***Great features of the library:*** ##
- ***Get payment detail***
- ***Set discount***
- ***Easily add item to order***
- ***Verify IPN hash***

## Install (Composer): ##

```
composer require tuxxx128/g2a-pay-php5-api
```
or just add:
```
{
    "require": {
        "tuxxx128/g2a-pay-php5-api": "dev-master"
    }
}
```

## Initialization: ##

```
<?php 

use Tuxxx128\G2aPay\G2aPayApi;
use Tuxxx128\G2aPay\G2aPayItem;

$g2aPayApi = new G2aPayApi('API HASH', 'SECRET KEY', true, 'EMAIL OF STORE');
```

Boolean type in construction determines mode of environment production (***true***) or development (***false***).

## Add new item to order list: ##
```
<?php 

$item = (new G2aPayItem)->itemTemplate();

$item->name = "My item";
$item->url = "http://...";
$item->price = 10; // default currency is 'EUR'

$g2aPayApi->addItem($item);
```

## Set discount (percents or fixed amount): ##
```
<?php 

$item = (new G2aPayItem)->itemTemplate();

$item->name = "Discount";
$item->url = "http://...";
```

Set over percent:

```
$g2aPayApi->addPercentDiscountItem($item, 25); // 25% from total amount
```

Or you can set directly fixed amount:

```
$g2aPayApi->addAmountDiscountItem($item, 5); // 5 EUR
```

## Change default currency: ##
The function is not calculating..

```
<?php

$g2aPayApi->setCurrency("USD"); // from default currency 'EUR' to 'USD'
```

## Create new payment: ##

```
<?php

$g2aPayApi->setUrlFail("http://..");
$g2aPayApi->setUrlSuccess("http://..");
$g2aPayApi->setOrderId(ORDER ID);
// $g2aPayApi->setEmail('user@server.tld');

header('Location: '.$g2aPayApi->getRedirectUrlOnGateway());
```

## Get complete transaction detail ##

```
<?php 

$transactionDetail = $g2aPayApi->getPaymentDetailById($transactionId);

var_dump($transactionDetail);
```

## Detect current environment ##
Maybe you need detect environment in your application.

```
<?php 

if($g2aPayApi->checkIsProductionEnvironment()) {
	// ...
}
else {
	// ...
}
```

## Verify IPN hash ##

```
<?php 

if($_POST['hash'] == $g2aPayApi->calculateIpnHash($transactionId, $orderId, $amount)) {
	// ...
}
else {
	// ...
}
```
## Next methods

```
<?php

echo $g2aPayApi->getTotalPrice();
echo ' ';
echo $g2aPayApi->getCurrency();
```

[![gitcheese.com](https://s3.amazonaws.com/gitcheese-ui-master/images/badge.svg)](https://www.gitcheese.com/donate/users/14346457/repos/81394009)
