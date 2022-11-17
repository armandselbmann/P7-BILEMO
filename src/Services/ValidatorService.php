<?php

namespace App\Services;

use Symfony\Component\Validator\Validator\ValidatorInterface;

Class ValidatorService {

    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function checkValidation($object): array|string
    {
        $message = [];

        $errors = $this->validator->validate($object);
        if ($errors->count() > 0) {
            foreach($errors as $error) {
                $message[] = $error->getMessage();
            }
            return $message;
        }
        return '';
    }

}
