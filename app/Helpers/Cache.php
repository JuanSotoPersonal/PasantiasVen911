<?php
/**
 * HELPER: Cache
 * Propósito: Gestionar la persistencia de datos en disco (L2 Cache).
 * Reduce la carga del servidor al evitar consultas repetitivas a la BD.
 */

namespace App\Helpers;

class Cache {
    private static string $dir = __DIR__ . '/../../storage/cache/';

    /**
     * Guarda datos en un archivo de caché.
     */
    public static function guardar(string $clave, $datos, int $ttl = 3600): void {
        if (!is_dir(self::$dir)) {
            mkdir(self::$dir, 0777, true);
        }
        $archivo = self::$dir . md5($clave) . '.cache';
        $contenido = [
            'expira' => time() + $ttl,
            'datos'  => $datos
        ];
        file_put_contents($archivo, serialize($contenido));
    }

    /**
     * Recupera datos del caché si no han expirado.
     */
    public static function obtener(string $clave) {
        $archivo = self::$dir . md5($clave) . '.cache';
        if (!file_exists($archivo)) {
            return null;
        }

        $contenido = unserialize(file_get_contents($archivo));
        if (time() > $contenido['expira']) {
            unlink($archivo);
            return null;
        }

        return $contenido['datos'];
    }

    /**
     * Elimina un archivo de caché específico.
     */
    public static function borrar(string $clave): void {
        $archivo = self::$dir . md5($clave) . '.cache';
        if (file_exists($archivo)) {
            unlink($archivo);
        }
    }

    /**
     * Limpia todo el directorio de caché.
     */
    public static function limpiarTodo(): void {
        if (!is_dir(self::$dir)) return;
        $archivos = glob(self::$dir . '*.cache');
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) unlink($archivo);
        }
    }
}
