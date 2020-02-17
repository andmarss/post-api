<?php

namespace App\Traits\ControllersTraits;

use App\Contracts\FromFillableInterface;

trait IndexTrait
{
    public function index()
    {
        $response = $this->messages[__FUNCTION__];

        $response['payload'] = static::$class::all()->map(function (FromFillableInterface $model) {
            return $model->getFromFillable();
        })->all();

        return response()->json($response);
    }
}