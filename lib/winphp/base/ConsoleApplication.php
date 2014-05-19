<?php

class ConsoleApplication extends Application {

    private $_runner;
    private $_commandMaps = array();

    protected function init() {
        parent::init();
        if (!isset($_SERVER['argv']))
            die('This script must be run from the command line.');

        $this->_commandMaps = $this->_loadCommands();
    }

    /**
     * @return 返回路径/phpcli/spider//commands
     */
    public function getCommandPath() {
        return ROOT_BIN_PATH . DIRECTORY_SEPARATOR . 'commands';
    }

    /**
     *  @return 返回/phpcli/spider/commands下的全部*Cli.php的名称
     */
    private function _loadCommands() {

        $path = $this->getCommandPath();
        if (($dir = @opendir($path)) === false)
            return array();
        $commands = array();
        while (($name = readdir($dir)) !== false) {
            $file = $path . DIRECTORY_SEPARATOR . $name;
            if (!strcasecmp(substr($name, -7), 'Cli.php') && is_file($file))
                $commands[strtolower(substr($name, 0, -7))] = $file;
        }
        closedir($dir);

        return $commands;
    }

    public function process() {
        $args = $_SERVER['argv'];
        array_shift($args);

        if (isset($args[0])) {
            $name = $args[0];
            array_shift($args);
        } else
            $this->help();

        if (($cli = $this->routing($name)) !== null) {
            $cli->init();
            $cli->run($args);
        } else
            $this->help();

        echo "\n\n";
        exit(0);
    }

    /**
     * @return /phpcli/spider/commands下的{$name}Cli.php的实例
     */
    private function routing($name) {
        $name = strtolower($name);
        if (isset($this->_commandMaps[$name])) {
            $className = substr(basename($this->_commandMaps[$name]), 0, -4);
            if (!class_exists($className, false))
                require_once($this->_commandMaps[$name]);

            return new $className();
        }

        return null;
    }

    public function help() {
        echo "Help : <cli> <action> [--<option>=<value>]\n\n";
        exit(1);
    }

}

?>
