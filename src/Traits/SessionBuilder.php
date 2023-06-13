<?php

namespace App\Traits;

trait SessionBuilder
{
    public function addSessionItem(string $attribute = '', string $value = '')
    {
        if (!$attribute || !$value) {
            return;
        }

        $session = $this->request->getSession();
        $session->set($attribute, $value);
    }
}