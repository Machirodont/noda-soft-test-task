<?php

namespace NodaSoft\Request;

interface Request
{
    /**
     * @param string $key
     * @return mixed
     */
    public function getData(string $key);
}
