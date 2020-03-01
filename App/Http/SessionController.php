<?php

namespace App\Http;

use App\Models\Participant;
use App\Models\Session;
use App\System\Request;
use App\System\Validator;
use App\Traits\ControllersTraits\IndexTrait;
use App\Traits\ControllersTraits\ShowTrait;

class SessionController extends MainController
{
    use IndexTrait;
    use ShowTrait;

    protected static $class = Session::class;

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

    /**
     * @param Request $request
     * @param $id
     * @return \App\System\Response
     * @throws \Exception
     */
    public function subscribe(Request $request, $id)
    {
        /**
         * @var Validator $validator
         */
        $validator = $this->validate($request, [
            'Email' => [
                'required' => true,
                'exists'   => 'participant'
            ],
            'id' => [
                'required' => true,
                'exists'   => 'session'
            ]
        ]);

        if (!$validator->passed()) {

            $messages = [];

            if ($validator->hasError('Email.exists')) {
                $messages[] = 'Только зарегестрированные пользователи могут записываться на лекции';
            }

            if ($validator->hasError('Email.required')) {
                $messages[] = 'Поле "Email" обязательно для заполнения';
            }

            if ($validator->hasError('id.exists')) {
                $messages[] = 'Лекция не найдена';
            }

            if ($validator->hasError('id.required')) {
                $messages[] = 'Поле "id" обязательно для заполнения';
            }

            return response()->json([
                'status' => 'error',
                'message' => implode(PHP_EOL, $messages)
            ]);
        }
        /**
         * @var Session $session
         */
        $session = static::$class::find($id);

        $timeOfEvent = strtotime($session->TimeOfEvent);
        $now = strtotime(date('Y-m-d H:i:s'));

        if ($timeOfEvent > $now) {

            $exist = $session->participants->where(['Email' => $request->get('Email')])->first();

            if ($exist) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Пользователь с таким Email уже записан на лекцию'
                    ]
                );
            }

            $maxParticipants = (int) $session->ParticipantMaxNumber;
            $participantsCount = $session->participants->count();

            if ($participantsCount < $maxParticipants) {
                /**
                 * @var Participant $participant
                 */
                $participant = Participant::where(['Email' => $request->get('Email')])->first();

                $session->participants()->attach($participant->ID);

                return response()->json([
                    'status'  => 'ok',
                    'message' => 'Спасибо, вы успешно записаны!'
                ]);
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Извините, все места заняты!'
                ]);
            }

        } else {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Лекция уже окончена. Запись на неё более невозможна'
                ]
            );
        }
    }
}