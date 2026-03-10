<?php

namespace App\Services;

class FirebirdService
{
    public static function connect()
    {
        $conn = ibase_connect(
            env('FB_HOST') . ':' . env('FB_DATABASE'),
            env('FB_USERNAME'),
            env('FB_PASSWORD'),
            env('FB_CHARSET', 'UTF8')
        );

        if (!$conn) {
            throw new \Exception(ibase_errmsg());
        }

        return $conn;
    }
}
