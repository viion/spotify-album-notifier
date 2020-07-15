<?php

namespace App\Service\Spotify;

class SpotifyConfig
{
    private static $config = [];
    
    public static function set(array $config)
    {
        self::$config = array_merge(self::$config, $config);
    }
    
    public static function reset()
    {
        self::$config = [];
    }
    
    public static function get()
    {
        return self::$config;
    }
    
    public static function id()
    {
        return self::$config['client_id'] ?? null;
    }
    
    public static function secret()
    {
        return self::$config['client_secret'] ?? null;
    }
    
    public static function token()
    {
        return self::$config['access_token'] ?? null;
    }
    
    public static function refreshToken()
    {
        return self::$config['refresh_token'] ?? null;
    }
    
    public static function scopes()
    {
        return self::$config['scopes'] ?? null;
    }
    
    public static function redirect()
    {
        return self::$config['redirect'] ?? null;
    }
}
