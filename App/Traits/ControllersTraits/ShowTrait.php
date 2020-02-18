<?php

namespace App\Traits\ControllersTraits;

use App\Contracts\FromFillableInterface;
use App\System\Request;

trait ShowTrait
{
    /**
     * @param Request $request
     * @param int $id
     * @return \App\System\Response
     */
    public function show(Request $request, int $id)
    {
        $response = $this->messages[__FUNCTION__];
        /**
         * @var FromFillableInterface $model
         */
        $model = static::$class::find($id);

        if (is_null($model)) {
            $response['payload'] = [];
        } else {
            $response['payload'] = $model->getFromFillable();
        }

        return response()->json($response);
    }
}