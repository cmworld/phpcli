<?php

abstract class BaseCli {

    public $defaultAction = 'index';

    public function init() {
        
    }

    public function run($args) {
        list($action, $options, $args) = $this->resolveRequest($args);
        $methodName = 'action' . ucfirst($action);
        if (!preg_match('/^\w+$/', $action) || !method_exists($this, $methodName))
            $this->showError("Unknown action: " . $action);

        $method = new ReflectionMethod($this, $methodName);
        $params = array();
        // named and unnamed options
        foreach ($method->getParameters() as $i => $param) {
            $name = $param->getName();
            if (isset($options[$name])) {
                if ($param->isArray())
                    $params[] = is_array($options[$name]) ? $options[$name] : array($options[$name]);
                else if (!is_array($options[$name]))
                    $params[] = $options[$name];
                else
                    $this->showError("Option --$name requires a scalar. Array is given.");
            }
            else if ($name === 'args')
                $params[] = $args;
            else if ($param->isDefaultValueAvailable())
                $params[] = $param->getDefaultValue();
            else
                $this->showError("Missing required option --$name.");
            unset($options[$name]);
        }

        // try global options
        if (!empty($options)) {
            $class = new ReflectionClass(get_class($this));
            foreach ($options as $name => $value) {
                if ($class->hasProperty($name)) {
                    $property = $class->getProperty($name);
                    if ($property->isPublic() && !$property->isStatic()) {
                        $this->$name = $value;
                        unset($options[$name]);
                    }
                }
            }
        }

        if (!empty($options))
            $this->showError("Unknown options: " . implode(', ', array_keys($options)));

        $exitCode = $method->invokeArgs($this, $params);

        return $exitCode;
    }

    /**
     * @param type $args 已经在ConsoleApplication->process处理过了
     * @return 分解url的参数
     */
    protected function resolveRequest($args) {
        $options = array();
        $params = array();
        foreach ($args as $arg) {
            if (preg_match('/^--(\w+)(=(.*))?$/', $arg, $matches)) {  // an option
                $name = $matches[1];
                $value = isset($matches[3]) ? $matches[3] : true;
                if (isset($options[$name])) {
                    if (!is_array($options[$name]))
                        $options[$name] = array($options[$name]);
                    $options[$name][] = $value;
                } else
                    $options[$name] = $value;
            }
            else if (isset($action))
                $params[] = $arg;
            else
                $action = $arg;
        }
        if (!isset($action))
            $action = $this->defaultAction;

        return array($action, $options, $params);
    }

    public function showError($message) {
        echo "Error: $message\n\n";
        exit(1);
    }

}

?>
