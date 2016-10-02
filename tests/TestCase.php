<?php

use lntn\Epay\EpayClient;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

abstract class TestCase extends FrameworkTestCase
{
    protected $password = 'bcblcn';
    protected $target = 'useraccount1';

    protected function createClient()
    {
        return new EpayClient('charging01', 'charging01', '00477', 'pajwtlzcb', [
            'EPAY_PUBLIC_KEY' => __DIR__ . '/../key/Epay_Public_key.pem',
            'PRIVATE_KEY'     => __DIR__ . '/../key/private_key.pem',
        ]);
    }
}
