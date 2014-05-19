<?php

if (!defined("ROOT_TOP_PATH")) {
    define("ROOT_TOP_PATH", dirname(__FILE__));
}

class WinBase {

    private static $_app;
    private static $_includePaths = array();
    public static $classMap = array();
    private static $_logger;
    static $_coreClasses = array(
        "Config" => "/lib/winphp/Config.class.php",
        "PDODataSource" => "/lib/winphp/DataSource.class.php",
        "MysqlDb" => "/lib/winphp/MysqlDb.class.php",
        "SystemException" => "/lib/winphp/Exception.class.php",
        "BizException" => "/lib/winphp/Exception.class.php",
        "BaseDb" => "/lib/winphp/base/BaseDb.php",
        "BaseAction" => "/lib/winphp/base/BaseAction.php",
        "BaseController" => "/lib/winphp/base/BaseController.php",
        "Application" => "/lib/winphp/base/Application.php",
        "BaseCli" => "/lib/winphp/base/BaseCli.php",
        "ConsoleApplication" => "/lib/winphp/base/ConsoleApplication.php",
    );

    public static function createApp($config) {
        return new Application($config);
    }

    public static function createConsole($config) {
        return new ConsoleApplication($config);
    }

    public static function app() {
        return self::$_app;
    }

    public static function setApp($app) {
        if (self::$_app === null || $app === null)
            self::$_app = $app;
        else
            throw new Exception('Application can only be created once.');
    }

    public static function setIncludePath($path) {
        if (self::$_includePaths === null) {
            self::$_includePaths = array_unique(explode(PATH_SEPARATOR, get_include_path()));
            if (($pos = array_search('.', self::$_includePaths, true)) !== false)
                unset(self::$_includePaths[$pos]);
        }

        array_unshift(self::$_includePaths, $path);

        set_include_path('.' . PATH_SEPARATOR . implode(PATH_SEPARATOR, self::$_includePaths));
    }

    public static function setClassMap($classMap) {
        self::$classMap = array_merge(self::$classMap, $classMap);
    }

    public static function autoload($className) {
        if (isset(self::$classMap[$className]))
            include(self::$classMap[$className]);
        else if (isset(self::$_coreClasses[$className]))
            include(ROOT_TOP_PATH . self::$_coreClasses[$className]);
        else {
            foreach (self::$_includePaths as $path) {
                if (is_file($path . DIRECTORY_SEPARATOR . $className . '.php')) {
                    include($path . DIRECTORY_SEPARATOR . $className . '.php');
                    break;
                } elseif (is_file($path . DIRECTORY_SEPARATOR . $className . '.class.php')) {
                    include($path . DIRECTORY_SEPARATOR . $className . '.class.php');
                    break;
                }
            }

            return class_exists($className, false) || interface_exists($className, false);
        }
        return true;
    }

}

spl_autoload_register(array('WinBase', 'autoload'));
