<?php
class backgroundCli extends BaseCli{
	
	private $_pid = 0;
	
	function init(){
        $this->_pid = posix_getpid();
	}

	function getPid(){
		return $this->_pid;
	}

	function cache($group){
		static $_cache = array();
		if(!isset($_cache[$group]) || !$_cache[$group]){
			$config_group = WinBase::app()->getSetting('redis');
			$config = $config_group[$group];
			$_cache[$group] = new RdCache($config);
		}
		return $_cache[$group];
	}
	
	function log($msg){
		
	}

	function output($msg,$type='notice'){
		$log_level = WinBase::app()->getSetting('setting.log_level');

		if(in_array($type, $log_level)){
			$data = date('m-d H:i:s');
			echo "[$data][$type] ".$msg."\n";
		}
		
		if($type == 'error'){
			exit(1);
		}
	}

	function output_progress($i){

		$log_level = WinBase::app()->getSetting('setting.log_level');

		if(in_array('notice', $log_level)){
			echo "\033[5D";
			if($i == '100'){
				echo "Done!\n";
			}else{
				echo str_pad($i, 3, ' ', STR_PAD_LEFT) . " %";
			}
		}
	}
		
}