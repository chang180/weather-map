<?php

class RedisCache
{
    const TTL_SECONDS = 10800;

    private $redis;
    private $available = false;

    public function __construct($host = null, $port = null, $password = null)
    {
        if (!extension_loaded('redis')) {
            return;
        }

        $host = $host ?: getenv('REDIS_HOST');
        $port = (int) ($port ?: (getenv('REDIS_PORT') ?: 6379));
        $password = $password !== null ? $password : getenv('REDIS_PASSWORD');

        if (!$host || $password === false || $password === '') {
            return;
        }

        try {
            $redis = new Redis();
            $connected = $redis->connect($host, $port, 2.5);

            if (!$connected || !$redis->auth($password)) {
                return;
            }

            $this->redis = $redis;
            $this->available = true;
        } catch (Throwable $error) {
            $this->redis = null;
            $this->available = false;
        }
    }

    public function isAvailable()
    {
        return $this->available;
    }

    public function get($key)
    {
        if (!$this->available) {
            return null;
        }

        try {
            $payload = $this->redis->get($key);
            if ($payload === false || $payload === null || $payload === '') {
                return null;
            }

            $decoded = json_decode($payload, true);
            return is_array($decoded) ? $decoded : null;
        } catch (Throwable $error) {
            return null;
        }
    }

    public function set($key, array $data, $ttl = self::TTL_SECONDS)
    {
        if (!$this->available) {
            return false;
        }

        try {
            $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                return false;
            }

            return $this->redis->setex($key, (int) $ttl, $encoded);
        } catch (Throwable $error) {
            return false;
        }
    }
}
