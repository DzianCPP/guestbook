<?php


namespace App\Traits;

use Symfony\Component\HttpFoundation\Request;

trait Greet
{
    private function setGreet(Request $request): string
    {
        if ($request->query->get('hello')) {
            return $request->query->get('hello');
        }

        return '';
    }
}