<?php

namespace App\Helpers;

/**
 * Helper de Caché - Gestión de persistencia temporal
 * Soporta Redis, Memcached y File System.
 */
class Cache {
    private static $driver = null;
    private static $instancia = null;
    private static $cacheDir = 'storage/cache/';

    /**
     * Inicializa el driver más rápido disponible
     */
    private static function init() {
        if (self::$instancia !== null) return;

        // Intentar Redis
        if (class_exists('Redis')) {
            try {
                $redis = new \Redis();
                if ($redis->connect('127.0.0.1', 6371)) { // Puerto común o config
                    self::$driver = 'redis';
                    self::$instancia = $redis;
                    return;
                }
            } catch (\Exception $e) { /* Fallback */ }
        }

        // Intentar Memcached
        if (class_exists('Memcached')) {
            try {
                $mem = new \Memcached();
                if ($mem->addServer('127.0.0.1', 11211)) {
                    self::$driver = 'memcached';
                    self::$instancia = $mem;
                    return;
                }
            } catch (\Exception $e) { /* Fallback */ }
        }

        // Fallback: File System
        self::$driver = 'file';
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
    }

    /**
     * Obtiene un valor de la caché
     */
    public static function obtener(string $key) {
        self::init();
        $key = self::sanitize($key);

        if (self::$driver === 'redis') {
            $val = self::$instancia->get($key);
            return $val ? unserialize($val) : null;
        }

        if (self::$driver === 'memcached') {
            return self::$instancia->get($key) ?: null;
        }

        // File Driver
        $path = self::$cacheDir . md5($key) . '.cache';
        if (!file_exists($path)) return null;

        $content = file_get_contents($path);
        $data = unserialize($content);

        if (!is_array($data) || !isset($data['expira']) || !isset($data['valor'])) {
            unlink($path);
            return null;
        }

        if ($data['expira'] < time()) {
            unlink($path);
            return null;
        }

        return $data['valor'];
    }

    /**
     * Guarda un valor en la caché
     */
    public static function guardar(string $key, $valor, int $ttl = 3600) {
        self::init();
        $key = self::sanitize($key);

        if (self::$driver === 'redis') {
            return self::$instancia->set($key, serialize($valor), $ttl);
        }

        if (self::$driver === 'memcached') {
            return self::$instancia->set($key, $valor, $ttl);
        }

        // File Driver
        $path = self::$cacheDir . md5($key) . '.cache';
        $data = [
            'expira' => time() + $ttl,
            'valor'  => $valor
        ];
        return file_put_contents($path, serialize($data));
    }

    /**
     * Patrón Remember: Obtiene el valor o lo genera y guarda si no existe
     */
    public static function remember(string $key, int $ttl, callable $callback) {
        $valor = self::obtener($key);
        if ($valor !== null) return $valor;

        $valor = $callback();
        self::guardar($key, $valor, $ttl);
        return $valor;
    }

    /**
     * Elimina una llave de la caché
     */
    public static function borrar(string $key) {
        self::init();
        $key = self::sanitize($key);

        if (self::$driver === 'redis') {
            return self::$instancia->del($key);
        }

        if (self::$driver === 'memcached') {
            return self::$instancia->delete($key);
        }

        $path = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($path)) {
            return unlink($path);
        }
        return true;
    }

    /**
     * Limpia toda la caché
     */
    public static function limpiarTodo() {
        self::init();
        if (self::$driver === 'file') {
            $files = glob(self::$cacheDir . '*.cache');
            foreach ($files as $file) unlink($file);
        } elseif (self::$driver === 'redis') {
            self::$instancia->flushDB();
        } elseif (self::$driver === 'memcached') {
            self::$instancia->flush();
        }
    }

    private static function sanitize(string $key): string {
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $key);
    }
}
