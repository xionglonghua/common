<?php

namespace xionglonghua\common\traits;

trait MsgLogTrait
{
    final public function log($type, ...$args)
    {
        if (!method_exists($this, "log$type")) {
            return;
        }
        $logger = clone $this;
        return call_user_func([$logger, "log$type"], ...$args);
    }

    protected function logEmail(array $ret, array $config)
    {
        return;
    }

    protected function logSms(array $ret, array $config)
    {
        return;
    }

    protected function logPush(array $ret, array $config)
    {
        return;
    }
}
