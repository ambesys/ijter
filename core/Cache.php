<?php

// core/Cache.php

class Cache
{
    private static $cache = [];
    private static $driver = 'memory'; // 'memory', 'file', 'redis', etc.

    public static function set($key, $value, $ttl = 3600)
    {
        switch (self::$driver) {
            case 'memory':
                self::$cache[$key] = [
                    'value' => $value,
                    'expires' => time() + $ttl
                ];
                break;
            case 'file':
                // Implement file-based caching
                break;
            case 'redis':
                // Implement Redis caching
                break;
        }
    }

    public static function get($key)
    {
        switch (self::$driver) {
            case 'memory':
                if (isset(self::$cache[$key])) {
                    if (self::$cache[$key]['expires'] > time()) {
                        return self::$cache[$key]['value'];
                    }
                    self::clear($key);
                }
                break;
            case 'file':
                // Implement file-based cache retrieval
                break;
            case 'redis':
                // Implement Redis cache retrieval
                break;
        }
        return null;
    }

    public static function clear($key = null)
    {
        switch (self::$driver) {
            case 'memory':
                if ($key === null) {
                    self::$cache = [];
                } else {
                    unset(self::$cache[$key]);
                }
                break;
            case 'file':
                // Implement file cache clearing
                break;
            case 'redis':
                // Implement Redis cache clearing
                break;
        }
    }

    public static function setDriver($driver)
    {
        self::$driver = $driver;
    }
}
