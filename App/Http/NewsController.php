<?php

namespace App\Http;

use App\Models\News;
use App\Traits\ControllersTraits\IndexTrait;
use App\Traits\ControllersTraits\ShowTrait;

class NewsController extends MainController
{
    use IndexTrait;
    use ShowTrait;

    protected static $class = News::class;

    protected $messages = [
        'index' => [
            'status'  => 'ok',
            'payload' => []
        ],
        'show'  => [
            'status'  => 'ok',
            'payload' => []
        ]
    ];

    protected $errors = [
        'index' => [
            'status' => 'error',
            'message' => ''
        ],
        'show'  => [
            'status'  => 'error',
            'message' => ''
        ]
    ];
}