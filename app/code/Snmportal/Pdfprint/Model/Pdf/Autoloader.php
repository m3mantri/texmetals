<?php
namespace Snmportal\Pdfprint\Model\Pdf;

class Autoloader
{
    //AUIT
    const PREFIX = 'Snmportal\External\Dompdf';
    const PREFIXSVG = 'Snmportal\External\Svg';
    const PREFIXFONTLIB = 'Snmportal\External\FontLib';
    const PREFIXCPDF = 'Snmportal\External\Dompdf\lib\Cpdf';
    static $registration = false;
    /**
     * Register the autoloader
     */
    public static function register()
    {
        if (!self::$registration) {
            require_once(__DIR__ . '/../../Test/M1/fpdi/fpdf.php');
            require_once(__DIR__ . '/../../Test/M1/fpdi/fpdi.php');
            require_once(__DIR__ . '/../../Test/M1/fpdi/FPDI_Protection.php');
            spl_autoload_register(array(new self, 'autoload'), false, true);
            self::$registration = true;
        }
    }

    /**
     * Autoloader
     *
     * @param string
     */
    public static function autoload($class)
    {
        //   error_log("\n" . print_r($class, true), 3, 'autoload.log');
        $prefixLength = strlen(self::PREFIXCPDF);
        if (0 === strncmp(self::PREFIXCPDF, $class, $prefixLength)) {
            require_once __DIR__ . "/../../Test/M1/Dompdf/lib/Cpdf.php";
            return;
        }
        $prefixLength = strlen(self::PREFIXFONTLIB);
        if (0 === strncmp(self::PREFIXFONTLIB, $class, $prefixLength)) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $prefixLength));
            $file = __DIR__ . '/../../Test/M1/php-font-lib/src/FontLib' . (empty($file) ? '' : DIRECTORY_SEPARATOR) . $file . '.php';
            $file = realpath($file);
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }

        $prefixLength = strlen(self::PREFIXSVG);
        if (0 === strncmp(self::PREFIXSVG, $class, $prefixLength)) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $prefixLength));
            $file = __DIR__ . '/../../Test/M1/php-svg-lib/src/Svg' . (empty($file) ? '' : DIRECTORY_SEPARATOR) . $file . '.php';
            $file = realpath($file);
            if (file_exists($file)) {
                require_once $file;
            }
            return;
        }

        $prefixLength = strlen(self::PREFIX);
        if (0 === strncmp(self::PREFIX, $class, $prefixLength)) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $prefixLength));
            $file = realpath(__DIR__ . '/../../Test/M1/Dompdf/src' . (empty($file) ? '' : DIRECTORY_SEPARATOR) . $file . '.php');
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}
