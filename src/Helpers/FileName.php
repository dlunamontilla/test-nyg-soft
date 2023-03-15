<?php

namespace App\Helpers;

use DLTools\Config\DLRealPath;
use DLTools\HttpRequest\DLHost;

/**
 * Permite obtener a partir de un archivo `manifest.json` los archivos
 * CSS y JavaScript que se hayan generado.
 * 
 * Ejemplo de uso:
 * 
 * ```
 * $css = FileName::getFiles()->css;
 * $js = FileName::getFiles()->js;
 * ```
 * 
 * @package CodeJeran
 * 
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * 
 */
class FileName {
    /**
     * Instancia de la clase FileNames
     *
     * @var self|null
     */
    private static ?self $instance = NULL;

    /**
     * Archivos CSS y JavaScript tomados del archivo `manifest.json`
     *
     * @var [type]
     */
    private static $files;

    private function __construct(string $filename) {
        self::$files = self::readManifest($filename);
    }

    /**
     * Devuelve la instancia de la clase `FileName`
     *
     * @param string $filename
     * @return self
     */
    public static function getInstance(string $filename = "manifest.json"): self {
        if (!self::$instance) {
            self::$instance = new self($filename);
        }

        return self::$instance;
    }

    /**
     * Devuelve de forma dinámica en un objeto los nombres de los archivos
     * CSS y JavaScripts a partir de un archivo `manifest.json`
     *
     * @param string $filename
     * @return object
     */
    private static function readManifest(string $filename = "manifest.json"): object {
        $path = DLRealPath::getInstance();

        $root = $_SERVER['DOCUMENT_ROOT'] ?? "./";
        $filename = $path->getDocumentRoot() . "/public/build/{$filename}";

        if (!file_exists($filename)) {
            return (object) [
                "css" => '',
                "js" => ''
            ];
        }

        $json = file_get_contents($filename);
        $filenames = json_decode($json, true);

        /**
         * Ruta del archivo CSS generado por `manifest.json`
         * 
         * @var string $cssFile
         */
        $cssFile = "build/" . $filenames['index.css']['file'];
        
        /**
         * Ruta del archivo JavaScript generado a partir
         * de un archivo `manifest.json`
         * 
         * @var string $jsFile
         */
        $jsFile = "build/" . $filenames['index.html']['file'];

        $css = self::assets($cssFile);
        $js = self::assets($jsFile);

        $codeInLine = [
            "cssInLine" => '<style></style>',
            "jsInLine" => ''
        ];

        if (file_exists($cssFile)) {
            $codeInLine['cssInLine'] = "<style>" . file_get_contents($cssFile) . "</style>";
        }

        if (file_exists($js)) {
            $codeInLine['jsInLine'] = file_get_contents($jsFile);
        }

        return (object) [
            "css" => "<link rel=\"stylesheet\" href=\"{$css}\" />",
            "js" => "<script type=\"module\" crossorigin src=\"{$js}\"></script>",
            ...$codeInLine
        ];
    }

    /**
     * Devuelve de forma dinámica en un objeto los nombres de los archivos
     * CSS y JavaScripts a partir de un archivo `manifest.json`
     *
     * @return object
     */
    public static function getFiles(): object {
        return self::$files;
    }

    /**
     * Devuelve una ruta completa a partir de la raíz de ejecución
     * del documento.
     *
     * @param string $url
     * @return string
     */
    public static function assets(string $url): string {
        $path = DLRealPath::getInstance();

        $basedir = $path->getURIFromWordDir();
        $basedir = preg_replace('/^\//', '', $basedir);

        $http = DLHost::isHTTPS() ? 'https://' : 'http://';
        
        $host = DLHost::getHostname();

        $host .= "/{$basedir}/{$url}";

        $host = preg_replace('/\/\//', DIRECTORY_SEPARATOR, $host);

        $uri = $http . $host;

        return $uri;
    }
}
