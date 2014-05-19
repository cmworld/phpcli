<?php

class Logger
{
	public $logs=array();

    private $_logPath;
    private $_logFile = 'api';

    public function __construct($logPath = null)
    {
        if ($logPath === null)
            $this->setLogPath( ROOT_PRO_PATH. DIRECTORY_SEPARATOR . 'runtime/logs');
    }

    public function getLogPath()
    {
        return $this->_logPath;
    }

    public function setLogPath($value)
    {
        $logPath = realpath($value);
        if ($logPath == false || !is_dir($logPath) || !is_writable($logPath))
            throw new Exception("Log path '{$value}' does not point to a valid directory.");

        $logPath.=DIRECTORY_SEPARATOR . date('Y' . DIRECTORY_SEPARATOR . 'm');
        if (!is_dir($logPath)) {
            $this->CreateFolder($logPath, 0777);
        }

        $this->_logPath = $logPath;
    }

    public function getLogFile()
    {
        return $this->_logFile . '_' . date('d') . '.log';
    }

    public function setLogFile($value)
    {
        $this->_logFile = $value;
    }

    public function CreateFolder($dir, $mode = 0777)
    {
        if (!is_dir($dir)) {
            $this->CreateFolder(dirname($dir), $mode);
            mkdir($dir, $mode);
        }
        return true;
    }
	
    public function processLogs($logs)
    {
        $logFile = $this->getLogPath() . DIRECTORY_SEPARATOR . $this->getLogFile();

        $fp = @fopen($logFile, 'a');
        @flock($fp, LOCK_EX);
        foreach ($logs as $log)
            @fwrite($fp, $this->formatLogMessage($log[0], $log[1], $log[2], $log[3]));
        @flock($fp, LOCK_UN);
        @fclose($fp);
    }

    public function formatLogMessage($message, $level, $category, $time)
    {
		$log = @date('Y/m/d H:i:s',$time);
		if($level){
			$log .= " [$level]";
		}
		
		if($category){
			$log .= " [$category]";
		}
		
		$log .= " $message";
		return $log."\n";
    }

}