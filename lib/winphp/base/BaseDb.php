<?php
class BaseDb{
	
    public static $db;
	private static $_models = array();

	public static function model($className=__CLASS__)
	{
		if(isset(self::$_models[$className]))
			return self::$_models[$className];
		else
		{
			$model=self::$_models[$className]=new $className(null);
			return $model;
		}
	}

	public function getDb()
	{
		if(self::$db!==null)
			return self::$db;
		else
		{
			return WinBase::app()->getDb();
		}
	}
	
	public function query($sql){
		return $this->getDb()->exec($sql);
	}

	public function update($table, $data, $where) {
		return $this->getDb()->update($table, $data, $where);
	}

	public function delete($table, $where) {
		return $this->getDb()->delete($table, $where);
	}

	public function insert($table, $bind,$return_last_id = false, $ignore= false) {
		return $this->getDb()->insert($table, $bind,$return_last_id,$ignore);
	}

	public function fetch($sql){
		return $this->getDb()->fetch($sql);
	}

	public function fetch_all($sql) {
		return $this->getDb()->fetchAll($sql);
	}
	
	public function lastId(){
		return $this->getDb()->lastInsertId();
	}

	public function import($array, $glue = ','){
		return $this->getDb()->implode($array, $glue = ',');
	}
	
	public function limit($page,$page_size){
		$start =($page-1) * $page_size;
		return " Limit $start , $page_size";
	}
	
	public function order($orderby){
		$arr = explode('_',$orderby);
		if(count($arr) > 2){
			$sort = array_pop($arr);
			$order = implode('_',$arr);
		}else{
			$order = $arr[0];
			$sort = $arr[1];
		}
		
		return " ORDER BY $order $sort ";
	}
	
	public function tranStart(){
		$this->getDb()->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
		$this->getDb()->beginTransaction();
	}
	
	public function commit(){
		$this->getDb()->commit();
	}
	
	public function tranEnd(){
		$this->getDb()->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
	}
}
?>
