<?php

namespace Smarty\Models;

use Smarty\Controllers\RouterController;

final class Router extends RouterController
{
    public array $routes = [
        ['path' => '/', 'page' => 'home'],
        ['path' => '/article/{id}', 'page' => 'article'],
    ];

}
