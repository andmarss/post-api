<?php

require 'vendor/autoload.php';

use App\Routing\Route;
use App\System\Request;

Route::load('routes.php')
    ->direct(Request::uri(), Request::method());