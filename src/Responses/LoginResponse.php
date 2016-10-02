<?php
namespace lntn\Epay\Responses;

class LoginResponse extends EpayResponse
{
    protected $session_id;
    protected $trans_id;

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * @param mixed $session_id
     */
    public function setSessionId($session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * @param mixed $trans_id
     */
    public function setTransId($trans_id)
    {
        $this->trans_id = $trans_id;
    }

    /**
     * @return mixed
     */
    public function getTransId()
    {
        return $this->trans_id;
    }


}
