<?php

namespace App\Config;

use DLTools\Database\DLDatabase;

class Config {
    private DLDatabase $db;

    private static ?self $instance = NULL;

    /**
     * Tabla para obtener los datos de las redes sociales
     *
     * @var string
     */
    private string $socialMediaTable = "dl_social_media";

    /**
     * Tabla de configuración del sistema
     *
     * @var string
     */
    private string $configTable = "dl_config";

    private function __construct() {
        $this->db = DLDatabase::getInstance();
    }

    /**
     * Devuelve una única instancia de la clase Config
     *
     * @return self
     */
    public static function getInstanceConfig(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}