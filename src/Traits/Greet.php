<?php


namespace App\Traits;

use Symfony\Component\HttpFoundation\Request;

trait Greet
{
    private function setGreet(Request $request, string $name = ""): string
    {
        if ($request->query->get('hello')) {
            return $request->query->get('hello');
        }

        if (strlen($name) > 0) {
            return $name;
        }

        return '';
    }
}