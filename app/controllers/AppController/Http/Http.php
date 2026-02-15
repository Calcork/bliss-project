<?php

namespace App\Controllers\AppController\Http;

use App\Base\App;

abstract class Http
{

    public function __construct(protected App $app)
    {}

}
