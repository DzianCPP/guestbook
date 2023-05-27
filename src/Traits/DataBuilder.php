<?php


namespace App\Traits;

use Symfony\Component\HttpFoundation\Request;

trait DataBuilder
{
    private function addItem(string $key = "", mixed $value = null): void
    {
        $this->data[$key] = $value;
    }
}