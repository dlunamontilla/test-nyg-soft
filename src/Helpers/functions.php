<?php


use App\Config\Config;
use App\Config\Route;
use App\Helpers\FileName;
use DLTools\Auth\DLAuth;
use DLTools\Auth\DLRecaptcha;
use DLTools\Compilers\DLView;
use DLTools\Database\DLDatabase;
use DLTools\HttpRequest\DLHost;
use DLTools\HttpRequest\DLRequest;
use MallardDuck\HtmlFormatter\Formatter;

use Dompdf\Dompdf;

FileName::getInstance();
DLView::getInstance();



if (!function_exists('view')) {

    /**
     * Procesador de vistas para devolverlas en formato HTML
     *
     * @param string $view Nombre de la vista
     * @param array|object $data
     * @return string
     */
    function view(string $view, array | object $data = []): string {
        $config = Config::getInstanceConfig();

        $vars = [];

        foreach ($data as $key => $value) {
            $vars[$key] = $value;
        }

        $files = FileName::getFiles();

        $vars['css'] = $files->css ?? '';
        $vars['js'] = $files->js ?? '';
        $vars['cssInLine'] = $files->cssInLine ?? '';
        $vars['jsInLine'] = $files->jsInLine ?? '';

        ob_start();
        DLView::load($view, $vars);
        $html = ob_get_clean();

        $formatter = new Formatter;
        $html = $formatter->beautify($html);

        return $html;
    }
}

if (!function_exists('viewPDF')) {
    /**
     * Similar a la función `view`, pero se diferencia en que devuelve
     * contenido en formato PDF.
     *
     * @param string $view Vista a ser procesada. Todas las vistas se encuentra en el directorio
     * raiz `resources`.
     * 
     * @param array $data Son los datos que pasamos como parámetros. Es decir, es un array que 
     * tiene una clave y un valor asociado. En ellas, cada clave es una variable en la vista.
     * 
     * @param array $options Las opciones de renderizado del documento PDF.
     * @return string
     */
    function viewPDF(string $view, array|object $data = [], array $options = []): string {
        $params = [
            'isHtml5ParserEnabled' => true
        ];

        foreach ($options as $key => $value) {
            $params[$key] = $value;
        }

        $html = view($view, $data);

        $pdf = new Dompdf($params);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $stream = $pdf->output();

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="dl-document.pdf"');
        header('Content-Length: ' . strlen($stream));

        return $stream;
    }
}

if (!function_exists('getHost')) {

    /**
     * Devuelve el nombre de host actual
     *
     * @return string
     */
    function getHost(): string {
        $host = DLHost::getHostname();
        return $host;
    }
}

if (!function_exists('assets')) {

    /**
     * Permite acceder al directorio `public` desde la raíz.
     *
     * @param string $url
     * @return string
     */
    function assets(string $url): string {
        return FileName::assets($url);
    }
}

if (!function_exists('route')) {

    /**
     * Es un alias de `assets`. El objetivo es dejar claro que se va
     * acceder a una ruta.
     *
     * @param string $route
     * @return string
     */
    function route(string $route): string {
        return assets($route);
    }
}

if (!function_exists('fileContents')) {

    /**
     * Devuelve el contenido de un archivo si existe, de lo
     * contrario, devolverá una cadena vacía.
     *
     * @param string $file
     * @return string
     */
    function fileContents(string $file): string {
        $root = $_SERVER['DOCUMENT_ROOT'] ?? '';

        $filename = dirname($root, 1) . "/{$file}";

        if (!file_exists($filename)) {
            return "";
        }

        return trim(
            file_get_contents($filename)
        );
    }
}

if (!function_exists('getURL')) {

    /**
     * Devuelve la URL del sitio Web
     *
     * @return string
     */
    function getURL(): string {
        $hostname = DLHost::getHostname();
        $http = DLHost::isHTTPS() ? "https://" : "http://";

        $url = $http . $hostname . DIRECTORY_SEPARATOR;
        return $url;
    }
}

if (!function_exists('isHuman')) {

    /**
     * Valida si eres humano, de lo contrario, 
     * devolverá `false`.
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $isHuman = isHuman();
     * ```
     *
     * @return boolean
     */
    function isHuman(): bool {
        $recaptcha = DLRecaptcha::getInstance();
        $isHuman = $recaptcha->post();
        return $isHuman;
    }
}

if (!function_exists('paddingZero')) {

    /**
     * Agrega ceros a un número en formato de texto.
     *
     * @param int $number El número que se le agregarán los ceros.
     * @param int $length La cantidad total de dígitos que tendrá el número después de agregar los ceros.
     * @param int $paddingType La posición de los ceros agregados:
     *  - 0 o STR_PAD_LEFT: los ceros se agregarán a la izquierda.
     *  - 1 o STR_PAD_RIGHT: los ceros se agregarán a la derecha.
     *  - 2 o STR_PAD_BOTH: los ceros se agregarán tanto a la izquierda como a la derecha.
     *
     * @return string El número con los ceros agregados en formato de texto.
     */
    function paddingZero(int $number, int $length = 6, int $paddingType = STR_PAD_LEFT): string {
        return str_pad((string) $number, $length, '0', $paddingType);
    }
}

if (!function_exists('isValidRef')) {

    /**
     * Valida si la referencia es válida, de lo contrario,
     * devolverá `false`.
     * 
     * El objetivo es proteger contra ataques de referencias cruzadas (CSRF, por sus siglas en inglés)
     *
     * Ejemplo de uso:
     * 
     * ```
     * if (isValidRef()) {
     *  # Tus instrucciones aquí
     * }
     * ```
     * 
     * @return boolean
     */
    function isValidRef(): bool {
        $auth = new DLAuth;
        $request = DLRequest::getInstance();

        /**
         * Devuelve un token previamente almacenado en la sesión.
         * 
         * @var string $token
         */
        $token = $auth->getToken();

        /**
         * Token obtenido del formulario del usuario.
         * 
         * @var string $formToken
         */
        $formToken = ($request->getValues())['csrf-token'] ?? '';

        if (empty(trim($token))) {
            return false;
        }

        if ($token !== $formToken) {
            return false;
        }

        return true;
    }
}

if (!function_exists('setPassword')) {

    /**
     * setPassword genera un hash de contraseña seguro y aleatorio utilizando el algoritmo Argon2i.
     * Este algoritmo se considera uno de los más seguros para almacenar y proteger contraseñas.
     *
     * @param string $password La contraseña que se va a encriptar.
     * @return string El hash de contraseña generado de manera aleatoria y segura.
     */
    function setPassword(string $password): string {
        $auth = new DLAuth;
        return $auth->setPasswordToken($password);
    }
}

if (!function_exists('isValidEmail')) {

    /**
     * Comprueba si un correo electrónico es válido.
     *
     * @param string $email El correo electrónico que se va a validar.
     * @return bool True si el correo electrónico es válido, false en caso contrario.
     */
    function isValidEmail(string $email): bool {
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
        return is_string($isValid);
    }
}

if (!function_exists('getRandomNumber')) {

    /**
     * Devuelve números aleatorios de una cantidad de cifras
     * determinadas por `$length`.
     *
     * @param integer $length
     * @return integer
     */
    function getRandomNumber(int $length = 6): int {
        /**
         * Variable auxiliar que nos permitirá concatenar
         * números como cadenas de caracteres para establecer
         * una cifra con una cantidad de dígitos determinadas
         * por `$length`.
         * 
         * @var string $str
         */
        $str = "";

        for ($i = 0; $i < $length; $i++) {
            $str .= "9";
        }

        /**
         * Obtiene un número de una cantidad de dígitos
         * determinada por `$length`.
         * 
         * @var int $digit
         */
        $digit = (int) $str;

        $number = rand(0, $digit);
        return $number;
    }
}

if (!function_exists('getAccountCode')) {

    /**
     * Devuelve el código de la cuenta. El código devuelto
     * permitirá activar o recuperar el acceso a la cuenta.
     *
     * @param integer $length Longitud de los dígitos a devolver en
     * formato numérico.
     * 
     * @return string
     */
    function getAccountCode(int $length): string {
        $number = getRandomNumber($length);
        return paddingZero($number, $length, STR_PAD_LEFT);
    }
}

if (!function_exists('configure')) {

    /**
     * Si no encuentra ningún usuario en la tabla de usuarios
     * redirecciona automáticamente hacia el formulario de
     * creación de usuarios.
     *
     * @return void
     */
    function configure(): void {

        $db = DLDatabase::getInstance();
        $user = $db->from('dl_users')->count();

        /**
         * Cantidad de registros en la tabla usuarios.
         * 
         * @var int $count
         */
        $count = $user['count'] ?? 0;

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        $configure = ($uri !== Route::getRoute("/install") && $method !== "POST") && $count < 1;

        if ($configure) {
            header("Location: " . route('install'));
        }
    }
}

if (!function_exists('getRandomToken')) {

    /**
     * Devuelve un token aleatorio.
     *
     * @return string
     */
    function getRandomToken(): string {
        $auth = new DLAuth();
        return $auth->getRandomToken();
    }
}

if (!function_exists('getUsersData')) {

    /**
     * Obtiene de los datos del usuario de una cookie si
     * previamente ha iniciado sesión.
     *
     * @return object
     */
    function getUsersData(): object {
        /**
         * @var object $userData
         */
        $userData = json_decode($_COOKIE['user-data'] ?? "{}");

        return (object) $userData;
    }
}

if (!function_exists('json')) {

    /**
     * Devuelve un array en formato JSON como cadena de texto.
     *
     * @param array $array
     * @param boolean $pretty Indicar si devolverá o no un JSON formateado
     * @return object
     */
    function json(array $array = [], bool $pretty = false): string {
        return $pretty
            ? (string) json_encode($array ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : (string) json_encode($array ?? []);
    }
}