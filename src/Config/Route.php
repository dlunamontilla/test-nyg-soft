<?php

namespace App\Config;

use App\Auth\Auth;
use DLTools\Config\DLConfig;
use DLTools\Config\DLRealPath;
use DLTools\Database\DLDatabase;
use DLTools\HttpRequest\DLRequest;



/**
 * Procesa las rutas del proyecto.
 * 
 * @package CodeJeran
 * 
 * @author Code Jeran <codejeran@gmail.com>
 * @version v1.0.0
 */
class Route {
    private static ?self $instance = NULL;

    /**
     * Rutas de la aplicación
     *
     * @var array
     */
    private static array $routes = [];

    /**
     * Datos que se pasan en la petición
     *
     * @var array
     */
    private static array $data = [];

    /**
     * Instancia de la base datos del proyecto
     *
     * @var DLDatabase
     */
    private static DLDatabase $db;

    /**
     * Rutas del panel de administración.
     *
     * @var array $administrationPanelRoutes
     */
    private static array $administrationPanelRoutes = [];

    private function __construct() {
        self::$db = DLDatabase::getInstance();
        self::run();
    }

    /**
     * Procesa una petición enviada utilizando el método POST
     *
     * @param string $uri
     * @param string $controllerMethod
     * @return void
     */
    public static function post(string $uri, string $controllerMethod, array $data = []): void {
        $uri = self::clean($uri);
        $uri = self::getRoute($uri);

        self::$routes['POST'][$uri] = $controllerMethod;
        
        if (Auth::getMarkedRoutes()) {
            self::$administrationPanelRoutes[] = $uri;
        }

        foreach($data as $key => $value) {
            self::$data[$key] = $value;
        }
    }

    /**
     * Procesa una petición utilizando el método GET
     *
     * @param string $uri
     * @param string $controllerMethod
     * @return void
     */
    public static function get(string $uri, string $controllerMethod, array $data = []): void {
        $uri = self::clean($uri);
        $uri = self::getRoute($uri);

        self::$routes['GET'][$uri] = $controllerMethod;


        foreach($data as $key => $value) {
            self::$data[$key] = $value;
        }
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new Route();
        }

        return self::$instance;
    }

    public static function run(): void {
        /**
         * URI del sitio Web que se compararán con las rutas
         * registradas.
         * 
         * @var string $uri
         */
        $uri = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'];
        
        $uri = self::clean($uri);

        /**
         * Método de envío.
         * 
         * @var string $method
         */
        $method = self::getMethod();

        if (!isset(self::$routes[$method])) {
            http_response_code(404);
            
            header("content-type: application/json; charset=utf-8");

            echo json([
                "code" => 404,
                "message" => "Se ejecuta sin el protocolo HTTP"
            ], true);

            return;
        }

        if (!isset(self::$routes[$method][$uri])) {
            http_response_code(404);

            header("content-type: application/json; charset=utf-8");

            echo json([
                "code" => 404,
                "message" => "La página en la ruta '{$uri}' no se existe",
                'basedir' => route(''),
                'uri' => $uri
            ], true);

            return;
        }
        
        /**
         * Se obtiene el controlador y el método.
         * 
         * @var string $controllerMethod
         */
        $controllerMethod = self::$routes[$method][$uri];

        [$controller, $method] = explode('@', $controllerMethod);

        self::$data['db'] = self::$db;
        self::$data['preview'] = '';

        /**
         * Instancia de la clase DLConfig
         * 
         * @var DLConfig $config
         */
        $config = DLConfig::getInstance();

        /**
         * Credenciales obtenidas de la variable de entorno (`.env`).
         * 
         * @var object $credentials
         */
        $credentials = $config->getCredentials();

        self::$data['gSiteKey'] = $credentials->G_SITE_KEY ?? '';
        self::$data['token'] = substr(base64_encode(bin2hex(random_bytes(32))), 0, 40);

        /**
         * Errores personalizados y más descriptivos. Ayuda al desarrollador
         * a darse cuenta de forma rápida que no ha definido un controlador.
         */
        if (!class_exists($controller)) {
            http_response_code(500);

            echo view('errors.controller-error', [
                'title' => "Error en el controlador",
                "message" => "El controlador <strong>{$controller}</strong> no existe"
            ]);

            exit;
        }

        /**
         * Errores personalizados y más descriptivos.
         * 
         * Ayuda al desarrollador a enterarse rápidamente de que no ha definido un método
         * en el controlador que se está llamando.
         */
        if (!method_exists($controller, $method)) {
            http_response_code(500);
            
            echo view('errors.controller-error', [
                'title' => "Error en el método <code>{$method}</code>",
                "message" => "El método <strong><code>{$method}</code></strong> no existe"
            ]);

            exit;
        }

        $controller = new $controller;
        $data = $controller->$method((object) self::$data, DLRequest::getInstance());

        if (is_array($data) || is_object($data)) {
            header("content-type: application/json; charset=utf-8");
            
            if (isset(self::$data['pretty']) && self::$data['pretty'] === "pretty") {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }else {
                echo json_encode($data);
            }
        }

        if (is_string($data)) {
            echo $data;
        }
    }

    /**
     * Elimina la última barra diagonal (`/`) de la ruta.
     *
     * @param string $uri
     * @return string
     */
    private static function clean(string $uri): string {
        $uri = trim($uri);
        $uri = preg_replace("/\?(.*)/i", "", $uri);

        if (strlen($uri) < 1) {
            return $uri;
        }

        return $uri;
        return preg_replace("/\/+$/", "", $uri);
    }

    /**
     * Inyecta la ruta del directorio desde donde se ejecuta el script
     * principal a las rutas registradas.
     *
     * @param string $uri
     * @return string
     */
    public static function getRoute(string $uri): string {

        $path = DLRealPath::getInstance();
        $uri = $path->getURIFromWordDir() . $uri;

        $uri = preg_replace('/^\/\//', DIRECTORY_SEPARATOR, $uri);
        return $uri;
    }

    /**
     * Devuelve la URI del sitio Web.
     * 
     * @return string
     */
    public static function getURI(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $uri;
    }

    /**
     * Devuelve el método de envío que se esté utilizando.
     *
     * @return string
     */
    public static function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Devuelve las rutas del panel de admnistración.
     *
     * @return array
     */
    public static function getAdministrationPanelRoutes(): array {
        return self::$administrationPanelRoutes;
    }
}