<?php
namespace Epay\Responses;

class EpayResponse
{
    protected $status;
    protected $message;

    /**
     * EpayResponse constructor.
     * @param $status
     * @param $message
     */
    public function __construct($status, $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    function __toString()
    {
        return sprintf('EPAY Response [%s]: %s', $this->status, $this->message);
    }
}
