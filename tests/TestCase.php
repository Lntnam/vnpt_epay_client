<?php

use Epay\EpayClient;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

abstract class TestCase extends FrameworkTestCase
{
    protected $password = 'bcblcn';
    protected $target = 'useraccount1';

    protected function createClient()
    {
        return new EpayClient('charging01', 'charging01', '00477', 'pajwtlzcb', [
            'EPAY_PUBLIC_KEY' => '/home/vagrant/Code/VNPT_EPAY_client/key/Epay_Public_key.pem',
            'PRIVATE_KEY'     => '/home/vagrant/Code/VNPT_EPAY_client/key/private_key.pem',
        ]);
    }
}
