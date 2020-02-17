<?php

namespace App\Http;

use App\Models\News;
use App\Traits\ControllersTraits\IndexTrait;

class NewsController extends MainController
{
    use IndexTrait;

    protected static $class = News::class;

    protected $messages = [
        'index' => [
            'status'  => 'ok',
            'payload' => []
        ]
    ];

    protected $errors = [
        'index' => [
            'status' => 'error',
            'message' => ''
        ]
    ];
}