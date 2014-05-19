<?php

class RdCache {

	static $_default_config = array(
		'host' => '127.0.0.1',
		'password' => NULL,
		'port' => 6379,
		'timeout' => 0
	);

	public $_redis = null;

	public function __construct($config){

		if (extension_loaded('redis')){
			$this->_setup_redis($config);
		}else{
			throw new Exception('The Redis extension must be loaded to use Redis cache.');
		}
	}

	private function _setup_redis($config){        

		$config = array_merge(self::$_default_config, $config);

		try{
			$redis = new Redis();
			$link = $redis->connect($config['host'], $config['port'], 5);
		    if ($link === false) {
				throw new Exception($redis->getLastError(),XS_INTERNAL_SERVER_ERROR);
		    }

			if (isset($config['password'])){
				$link = $redis->auth($config['password']);
				if ($link === false) {
					throw new Exception($redis->getLastError(),XS_INTERNAL_SERVER_ERROR);
				}
			}

			if(isset($config['db'])){
				$redis->select($config['db']);
			}

		}catch (RedisException $e){
			throw new Exception('Redis connection refused. ' . $e->getMessage(),XS_INTERNAL_SERVER_ERROR);
		}

		$this->_redis = $link ? $redis : null;
	}

	function is_supported(){
		return !is_null($this->_redis) ? true : false;
	}	

    public function list_add_element($obj,$key,$array,$direction='left'){
        if(!is_array($array)){
            $array=array($array);
        }
        foreach($array as $val){
            ($direction == 'left') ? $this->_redis->lPush($key, json_encode($val)) : $this->_redis->rPush($key, json_encode($val));
        }
    }

    public function list_pop_element($obj,$key,$num=1,$direction='right') {
        for($i=0;$i<$num;$i++){
           $value = ($direction == 'right') ? $this->_redis->rPop($key) : $this->_redis->lPop($key);
           $data[]=json_decode($value);
        }
        return $data;
    }	

	public function __call($method, $args = array()){

		if (method_exists($this, $method)){
			return call_user_func_array(array($this, $method), $args);
		}else if(method_exists($this->_redis, $method)){
			return call_user_func_array(array($this->_redis, $method), $args);
		}

		throw new ApiException("No such method '{$method}'", XS_INTERNAL_SERVER_ERROR);
	}

	public function __destruct(){
		if (!is_null($this->_redis) && is_object($this->_redis)){
			$this->_redis->close();
		}
	}		
}