<?php

class Application {

    public $defaultController = 'default';
    private $_libraries = array();
    public $config = array();

    public function __construct($config) {
        WinBase::setApp($this);
        WinBase::setIncludePath(ROOT_PRO_PATH . DIRECTORY_SEPARATOR . 'model');
        WinBase::setIncludePath(ROOT_PRO_PATH . DIRECTORY_SEPARATOR . 'component');
        WinBase::setIncludePath(ROOT_PRO_PATH . DIRECTORY_SEPARATOR . 'libraries');

        set_exception_handler(array($this, 'handleException'));
        set_error_handler(array($this, 'handleError'), error_reporting());

        $extMap = $this->_loadExt();
        WinBase::setClassMap($extMap);

        $this->configure($config);

        $this->init();
    }

    /**
     * @return Array(apexsdkExt]=>/data/www-data/hupu.com/phpcli/spider//ext/apexsdk/apexsdkExt.php)
     */
    private function _loadExt() {

        $path = $this->getExtPath();
        if (($dir = @opendir($path)) === false)
            return array();
        $commands = array();
        while (($name = readdir($dir)) !== false) {
            if ($name == "." || $name == "..")
                continue;

            $file = $path . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $name . 'Ext.php';

            if (is_file($file)) {
                $commands[$name . 'Ext'] = $file;
            }
        }

        closedir($dir);

        return $commands;
    }

    protected function init() {
        /*
          echo '----------GET---------:\n';
          print_r($_GET);
          echo '----------POST--------:\n';
          print_r($_POST);
          echo '----------header-------:\n';
          $req = WinBase::app()->getRequest();
          print_r($req->getHeaders());
          exit;
         */
    }

    function configure($config) {
        //date_default_timezone_set($value);
        //ini_set("arg_seperator.output", "&amp;");
        //ini_set("magic_quotes_runtime", 0 );
        date_default_timezone_set('Asia/Shanghai');

        define('TIMESTAMP', time());

        $this->config = $config;
    }

    public function getControllerPath() {
        return ROOT_PRO_PATH . DIRECTORY_SEPARATOR . 'controller';
    }

    public function getViewPath() {
        return ROOT_PRO_PATH . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * @return 返回路径/data/www-data/hupu.com/phpcli/spider//ext
     */
    public function getExtPath() {
        return ROOT_PRO_PATH . DIRECTORY_SEPARATOR . 'ext';
    }

    public function process() {
        $route = $this->getUri()->parseUrl();
        //$route = str_replace("content", "", $route);
        //echo $route;

        if (($ca = $this->routing($route)) !== null) {
            list($controller, $actionID) = $ca;
            $controller->init();
            $controller->run($actionID);
        } else
            throw new Exception("Unable to resolve the request {$route}");
    }

    private function routing($route) {
        if (($route = trim($route, '/')) === '')
            $route = $this->defaultController;

        $route.='/';
        while (($pos = strpos($route, '/')) !== false) {
            $id = substr($route, 0, $pos);
            if (!preg_match('/^\w+$/', $id))
                return null;

            $route = (string) substr($route, $pos + 1);

            if (!isset($basePath)) {
                $basePath = $this->getControllerPath();
                $controllerID = '';
            } else
                $controllerID.='/';
            $className = $id . 'Controller'; //ucfirst($id)
            $classFile = $basePath . DIRECTORY_SEPARATOR . $className . '.php';

            if (is_file($classFile)) {
                if (!class_exists($className, false))
                    require($classFile);
                if (class_exists($className, false) && is_subclass_of($className, 'BaseController')) {
                    return array(
                        new $className($controllerID . $id),
                        $this->parseActionParams($route),
                    );
                }
                return null;
            }
            $controllerID.=$id;
            $basePath.=DIRECTORY_SEPARATOR . $id;
        }
    }

    protected function parseActionParams($pathInfo) {
        if (($pos = strpos($pathInfo, '/')) !== false) {
            $this->getUri()->parsePathInfo((string) substr($pathInfo, $pos + 1));
            $actionID = substr($pathInfo, 0, $pos);

            return $actionID;
        } else
            return $pathInfo;
    }

    public function handleException($exception) {
        restore_error_handler();
        restore_exception_handler();

        $category = 'exception.' . get_class($exception);
        if ($exception instanceof CHttpException)
            $category.='.' . $exception->statusCode;

        $message = $exception->__toString();
        if (isset($_SERVER['REQUEST_URI']))
            $message.="\nREQUEST_URI=" . $_SERVER['REQUEST_URI'];
        if (isset($_SERVER['HTTP_REFERER']))
            $message.="\nHTTP_REFERER=" . $_SERVER['HTTP_REFERER'];
        $message.="\n---";
        //Yii::log($message,CLogger::LEVEL_ERROR,$category);

        $this->displayException($exception);
    }

    public function handleError($code, $message, $file, $line) {
        if ($code & error_reporting()) {
            restore_error_handler();
            restore_exception_handler();

            $log = "$message ($file:$line)\nStack trace:\n";
            $trace = debug_backtrace();
            // skip the first 3 stacks as they do not tell the error position
            if (count($trace) > 3)
                $trace = array_slice($trace, 3);
            foreach ($trace as $i => $t) {
                if (!isset($t['file']))
                    $t['file'] = 'unknown';
                if (!isset($t['line']))
                    $t['line'] = 0;
                if (!isset($t['function']))
                    $t['function'] = 'unknown';
                $log.="#$i {$t['file']}({$t['line']}): ";
                if (isset($t['object']) && is_object($t['object']))
                    $log.=get_class($t['object']) . '->';
                $log.="{$t['function']}()\n";
            }
            if (isset($_SERVER['REQUEST_URI']))
                $log.='REQUEST_URI=' . $_SERVER['REQUEST_URI'];
            //Yii::log($log,CLogger::LEVEL_ERROR,'php');

            $this->displayError($code, $message, $file, $line);
        }
    }

    public function displayError($code, $message, $file, $line) {
        if (IS_DEBUG) {
            echo "<h1>PHP Error [$code]</h1>\n";
            echo "<p>$message ($file:$line)</p>\n";
            echo '<pre>';

            $trace = debug_backtrace();
            // skip the first 3 stacks as they do not tell the error position
            if (count($trace) > 3)
                $trace = array_slice($trace, 3);
            foreach ($trace as $i => $t) {
                if (!isset($t['file']))
                    $t['file'] = 'unknown';
                if (!isset($t['line']))
                    $t['line'] = 0;
                if (!isset($t['function']))
                    $t['function'] = 'unknown';
                echo "#$i {$t['file']}({$t['line']}): ";
                if (isset($t['object']) && is_object($t['object']))
                    echo get_class($t['object']) . '->';
                echo "{$t['function']}()\n";
            }

            echo '</pre>';
        }
        else {
            echo "<h1>PHP Error [$code]</h1>\n";
            echo "<p>$message</p>\n";
        }
    }

    public function displayException($exception) {
        if (IS_DEBUG) {
            echo '<h1>' . get_class($exception) . "</h1>\n";
            echo '<p>' . $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ')</p>';
            echo '<pre>' . $exception->getTraceAsString() . '</pre>';
        } else {
            echo '<h1>' . get_class($exception) . "</h1>\n";
            echo '<p>' . $exception->getMessage() . '</p>';
        }
    }

    /*
      public function loadClass($lib){

      if(is_string($lib))
      {
      $class=$lib;
      $config=array();
      }
      else if(is_array($lib))
      {
      $class=$lib['class'];
      $classFile = $lib['filePath'];

      unset($lib['class']);
      $config = $lib;

      if(is_file($classFile))
      require($classFile);
      }
      else
      throw new Exception('Object configuration must be an array containing a "class" element.');

      if(isset($this->_libraries[$class]))
      return $this->_libraries[$class];
      else
      {
      if(!class_exists($class,false) && !WinBase::autoload($class))
      throw new Exception("class $class not found!");

      if(($n=func_num_args())>1)
      {
      $args=func_get_args();
      unset($args[0]);
      $reflectionClass=new ReflectionClass($class);
      $object=call_user_func_array(array($reflectionClass,'newInstance'),$args);
      }
      else
      $object=new $class;

      foreach($config as $key=>$value)
      $object->$key=$value;

      return $this->_libraries[$class]=$object;
      }
      } */

    function getSetting($srh, $_setting = array()) {

        if (empty($_setting)) {
            $_setting = $this->config;
        }

        $k = explode('.', $srh);

        if (count($k) == 1) {
            return isset($_setting[$k[0]]) ? $_setting[$k[0]] : null;
        } else {
            $f = array_shift($k);
            $srh = implode('.', $k);

            if (!isset($_setting[$f])) {
                return null;
            }

            return $this->getSetting($srh, $_setting[$f]);
        }
    }

    public function getDb() {
        static $db = null;
        if ($db === null) {
            $db = new MysqlDb($this->config['db']['dns'], $this->config['db']['username'], $this->config['db']['password']);
        }
        return $db;
    }

    public function getCache() {
        //return $this->loadClass('cache');
    }

    public function getRequest() {
        static $rep = null;
        if ($rep === null) {
            return new WinRequest();
        }
        return $rep;
    }

    public function getUri() {

        static $uri = null;
        if ($uri === null) {
            return new Uri();
        }
        return $uri;
    }

}

?>
