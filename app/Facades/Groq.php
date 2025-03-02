<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array chat(array $messages, array $options = [])
 * @method static array testConnection()
 * 
 * @see \App\Services\GroqClient
 */
class Groq extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'groq';
    }
}
