<?php

namespace Beebmx\LaravelPay;

use Beebmx\LaravelPay\Concerns\ManageCustomer;
use Beebmx\LaravelPay\Concerns\ManagesAddresses;
use Beebmx\LaravelPay\Concerns\ManagesFactories;
use Beebmx\LaravelPay\Concerns\ManagesPaymentMethods;
use Beebmx\LaravelPay\Concerns\ManagesTransactions;
use Beebmx\LaravelPay\Concerns\PerformsPayments;

trait Payable
{
    use ManageCustomer;
    use ManagesAddresses;
    use ManagesFactories;
    use ManagesPaymentMethods;
    use ManagesTransactions;
    use PerformsPayments;
}
