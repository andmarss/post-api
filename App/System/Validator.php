<?php

namespace App\System;

use App\Traits\PluralizeTrait;

final class Validator
{
    use PluralizeTrait;

    protected static $errors = [];

    /**
     * @param $request
     * @param array $validationArray
     * @return $this
     *
     * Функция принимает объект Router'а, и сравнивает, совпадают ли переданные в запросе данные с теми
     * что указаны в правилах массива items
     */

    public function validate(Request $request, array $validationArray = [])
    {
        $this->clearErrors();

        foreach ($validationArray as $field => $rules) {
            foreach ($rules as $rule => $rule_value) {

                $value = $request->get($field);

                switch ($rule) {
                    case 'required' && empty(trim($value)):
                        $this->addError($field,"Поле {$field} обязательно для заполнения");
                        break;
                    case 'min':
                        if(mb_strlen(trim($value)) < $rule_value) {
                            $this->addError($field,sprintf("\"{$field}\" должно быть больше {$rule_value} %s.", $this->pluralize(intval($rule_value), ['знака', 'знаков','знаков'])));
                        }
                        break;
                    case 'max':
                        if(mb_strlen(trim($value)) > $rule_value) {
                            $this->addError($field,sprintf("\"{$field}\" должно быть меньше {$rule_value} %s.", $this->pluralize(intval($rule_value), ['знака', 'знаков','знаков'])));
                        }
                        break;
                    case 'match':
                        if (trim($value) !== $request->{$rule_value}) {
                            $this->addError($field,"\"{$field}\" должно быть совпадать с {$rule_value}.");
                        }
                        break;
                    case 'unique':
                        $check = DB::table($rule_value)->where([$field => $value])->first();

                        if ($check) {
                            $this->addError($field,"Пользователь с указанными данными: \"{$value}\", уже существует.");
                        }
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->addError($field,"\"{$field}\" должно быть валидным email-адресом.");
                        }
                        break;
                    case 'int':
                        if (!filter_var((int) $value, FILTER_VALIDATE_INT)) {
                            $this->addError($field,"\"{$field}\" должно иметь целочисленное значение.");
                        }
                        break;
                }
            }
        }

        return $this;
    }

    /**
     * @param $item
     * @param $error
     *
     * Добавляет ошибку в массив по имени поля
     */

    private function addError($item, $error)
    {
        if(!isset(static::$errors[$item])) {
            static::$errors[$item] = [];
        }

        static::$errors[$item][] = $error;
    }

    /**
     * @return array
     *
     * Получить весь массив ошибок
     */

    protected function getErrors()
    {
        return static::$errors;
    }

    /**
     * Очищает массив ошибок
     */

    protected function clearErrors()
    {
        static::$errors = [];
    }

    /**
     * @return bool
     *
     * Проверяет, есть ли ошибки в щапросе
     */

    protected function passed()
    {
        return count(static::$errors) === 0;
    }
}