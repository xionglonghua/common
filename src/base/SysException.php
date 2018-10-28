<?php

namespace xionglonghua\common\base;

use xionglonghua\common\helpers\SysMsg;

class SysException extends \Exception
{
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        $this->message = SysMsg::getErrMsg($message);
        return parent::__construct($message, $code, $previous);
    }
}
