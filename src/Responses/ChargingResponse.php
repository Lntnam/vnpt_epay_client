<?php
namespace lntn\Epay\Responses;


class ChargingResponse extends EpayResponse
{
    protected $trans_id;
    protected $amount;
    protected $response_amount;

    public function __construct($status, $message, $trans_id, $amount)
    {
        parent::__construct($status, $message);

        $this->trans_id = $trans_id;
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getTransId()
    {
        return $this->trans_id;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getResponseAmount()
    {
        return $this->response_amount;
    }

    /**
     * @param mixed $response_amount
     */
    public function setResponseAmount($response_amount)
    {
        $this->response_amount = $response_amount;
    }


}
