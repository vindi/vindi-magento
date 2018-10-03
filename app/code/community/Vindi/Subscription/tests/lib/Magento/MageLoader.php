<?php


class MageLoader
{

    /**
     * @param null $magentoPath
     */
    public static function bootstrap($magentoPath = null)
    {
        if ($magentoPath === null) {
            require_once __DIR__ . '/../../../../../../../Mage.php';
        } else {
            require_once $magentoPath;
        }

        self::patchMagentoAutoloader();
        self::init();
    }

    private static function patchMagentoAutoloader()
    {
        $mageErrorHandler = set_error_handler(
            function () {
                return false;
            }
        );
        set_error_handler(
            function ($errno, $errstr, $errfile) use ($mageErrorHandler) {
                if (substr($errfile, -19) === 'Varien/Autoload.php') {
                    return null;
                }

                return is_callable($mageErrorHandler) ? call_user_func_array(
                    $mageErrorHandler,
                    func_get_args()
                ) : false;
            }
        );
    }

    /**
     * Initialize application
     */
    public static function init()
    {
        Mage::app('', 'store', ['config_model' => '']);
        Mage::setIsDeveloperMode(true);
        self::patchMagentoAutoloader();
        $_SESSION = [];
    }

    /**
     * Reset application
     */
    public function reset()
    {
        Mage::reset();
        self::init();
    }
}
MageLoader::bootstrap();