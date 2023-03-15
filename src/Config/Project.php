<?php

namespace App\Config;

use DLTools\Auth\DLAuth;
use DLTools\Config\DLRealPath;

class Project {

    /**
     * Instancia de Proyect
     *
     * @var self|null
     */
    private static ?self $instance = null;


    /**
     * Instancia de la clase DLRealPath
     *
     * @var DLRealPath
     */
    private DLRealPath $root;

    private function __construct() {
        $this->root = DLRealPath::getInstance();
    }

    public function run(): void {
        include $this->root->getDocumentRoot() . "/src/Helpers/functions.php";
        include $this->root->getDocumentRoot() . "/src/Routes/web.php";

        // configure();

        Route::getInstance();

        // $auth = new DLAuth;
        // $auth->setSessionExpireTime($expireTime);
    }

    /**
     * Devuelve la instancia del objeto
     *
     * @return self
     */
    public static function getInstance(): self {
        if (!(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}