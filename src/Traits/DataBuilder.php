<?php


namespace App\Traits;

use Symfony\Component\HttpFoundation\Request;

trait DataBuilder
{
    private function addItem(string $key = "", mixed $value = null): void
    {
        $this->data[$key] = $value;
    }

    private function addItems(array $keys_values = []): void
    {
        foreach ($keys_values as $key => $value) {
            $this->addItem($key, $value);
        }
    }
}