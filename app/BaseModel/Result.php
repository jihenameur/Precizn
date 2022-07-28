<?php

namespace App\BaseModel;

class Result
{
    public bool $success;
    public string $message;
    public int $code;
    public $data;
    public function __construct()
    {
        $this->success = false;
        $this->message = "";
        $this->code = 200;
        $this->data = array();
    }

    public function success($data = null, $message = null)
    {
        $this->data = $data;
        $this->success = true;
        $this->code = 200;
        $this->message = $message == null ? "success" : $message;
    }

    public function fail($msg = "failed", int $code = 200)
    {
        $this->success = false;
        $this->message = $msg;
        $this->code = $code;
        $this->data = null;
    }
}
