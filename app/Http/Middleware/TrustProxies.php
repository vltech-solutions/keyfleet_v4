<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
//use Fideloper\Proxy\TrustProxies as Middleware; // for Laravel <= 7
use Illuminate\Http\Middleware\TrustProxies as Middleware; // for Laravel 8+

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string|null
     */
    protected $proxies = '*'; // You can also specify the NPM container's IP

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
