<?php


namespace Epay\Exceptions;


class EpayException extends \Exception
{
    public function __construct($result)
    {
        parent::__construct($result->message, $result->status);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}