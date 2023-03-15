<?php

namespace App\Auth;

use DLTools\Auth\DLAuth;
use DLTools\Auth\DLUser;

class Auth extends DLAuth {
    /**
     * Instancia de la clase DLUser
     * 
     * @var DLUser $user
     */
    private DLUser $user;

    /**
     * Valor de tipo booleano que ayuda a determinar
     * si se inicio sesión o no.
     *
     * @var boolean
     */
    private bool $verified = false;

    /**
     * Marcador de rutas del panel de administración.
     * 
     * @var bool $markedRoutes
     */
    private static bool $markedRoutes = false;

    /**
     * Establece el nombre de la tabla usuarios que se va a usar
     * para el sistema de autenticación.
     *
     * @param string $tableUser
     */
    public function __construct(string $tableUser = "dl_users") {
        parent::__construct();
        $this->user = new DLUser([
            'usersTable' => trim($tableUser)
        ]);

        $this->verified = $this->authenticated($this->user, function() {});
    }

    /**
     * Ejecuta código si el usuario se ha autenticado.
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $auth->verified(function($data) {
     *  # Todas las instrucciones que deban ejecutarse aquí
     * });
     * ```
     *
     * @param callable $callback
     * @return void
     */
    public function verified(callable $callback): void {
        self::$markedRoutes = true;

        $this->verified = $this->authenticated($this->user, function(array $data) use ($callback) {
            $callback($data);
            return;
        });
    }

    /**
     * Ejecuta instrucciones para los usuarios que no están 
     * autenticados.
     *
     * Ejemplo de uso:
     * ```
     * $auth->unverified(function() {
     *  # Se ejecutan instrucciones aquí para los usuarios no autenticados.
     * });
     * ```
     * @param callable $callback
     * @return void
     */
    public function unverified(callable $callback): void {
        if ($this->verified) return;
        $callback();
    }

    /**
     * Devuelve `true` o ´false` para indicar si las rutas son 
     * o no del panel de administración
     *
     * @return boolean
     */
    public static function getMarkedRoutes(): bool {
        return self::$markedRoutes;
    }
}