<?php

namespace TimFeid\Traits;

trait DynamicGet
{
    public function __get($key)
    {
        $method = 'get'.ucfirst($key);
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        }
    }
}
