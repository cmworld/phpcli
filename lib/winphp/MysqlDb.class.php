<?php

class MysqlDb extends PDODataSource
{
	private $connection = null;
	public $statement = null;
	private $fetchMode = PDO::FETCH_ASSOC;
 
	public function __construct($dsn, $username, $password)
	{
		parent::__construct($dsn, $username, $password);
	 	$this->connection = $this->getPdoInstance();
	}
	
	public function setAttribute($name,$value){
		$this->connection->setAttribute($name,$value);
	}
	
	public function exec($sql){
		try{
			$this->connection->exec($sql);
		}catch(Exception $e){
			
			throw new SystemException("SqlError : $sql, Message : ".$e->getMessage().", Trace : ".$e->getTraceAsString());
		}
		
		return true;
	}
	
	public function prepare($sql)
	{
		try
		{	
		    return $this->connection->prepare($sql);
		}
		catch (Exception $e)
		{
			throw new SystemException("SqlError : $sql, Message : ".$e->getMessage().", Trace : ".$e->getTraceAsString());
		}
	}
	
    public function fetchAll($sql)
    {
        return $this->connection->query($sql)->fetchAll($this->fetchMode);
    }
	
    public function fetch($sql)
    {
         return $this->connection->query($sql)->fetch($this->fetchMode);
    }
	
	public function praseBindColumn($where = array()){
		$column = array();

		foreach($where as $k=>$v){
			$bindFeild = ':'.$k;
			$column[] = $k .'='.$bindFeild;
		}
			
		return $column;
	}
	
	public function implode($array, $glue = ',') {
		$sql = $comma = '';
		$glue = ' ' . trim($glue) . ' ';
		foreach ($array as $k => $v) {
			$sql .= $comma . $this->quote_field($k) . '=' . $this->quote($v);
			$comma = $glue;
		}
		return $sql;
	}
	
	public function quote_field($field) {
		if (is_array($field)) {
			foreach ($field as $k => $v) {
				$field[$k] = $this->quote_field($v);
			}
		} else {
			if (strpos($field, '`') !== false)
				$field = str_replace('`', '', $field);
			$field = '`' . $field . '`';
		}
		return $field;
	}
	
	public function quote($str, $noarray = false) {

		if (is_string($str))
			return '\'' . addcslashes($str, "\n\r\\'\"\032") . '\'';

		if (is_int($str) or is_float($str))
			return '\'' . $str . '\'';

		if (is_array($str)) {
			if($noarray === false) {
				foreach ($str as &$v) {
					$v = $this->quote($v, true);
				}
				return $str;
			} else {
				return '\'\'';
			}
		}

		if (is_bool($str))
			return $str ? '1' : '0';

		return '\'\'';
	}

    public function insert($table, $bind,$return_last_id = false,$ignore =false)
    {
        $sql = $this->implode($bind);
		$ignore_query = $ignore ? 'IGNORE' : '';
		$query = "INSERT $ignore_query INTO $table SET $sql";
        $this->exec($query);
		
		if($return_last_id){
			return $this->lastInsertId();
		}
		
        return true;
    }
	
    public function lastInsertId(){
	    return $this->connection->lastInsertId();
	}	
    
    public function update($table, $data, $condition)
    {
		$sql = $this->implode($data);
		if(empty($sql)) {
			return false;
		}

		$where = '';
		if (empty($condition)) {
			return true;
		} elseif (is_array($condition)) {
			$where = $this->implode($condition, ' AND ');
		} else {
			$where = $condition;
		}

		$sql = "UPDATE $table SET $sql WHERE $where";
        $this->exec($sql);
		
		return true;
    }
    
    public function delete($table, $where)
    {
		if (empty($where)) {
			return false;
		} elseif (is_array($where)) {
			$where = $this->implode($where, ' AND ');
		} else {
			$where = $condition;
		}

		$sql = "DELETE FROM $table WHERE $where " ;
        return $this->connection->exec($sql);
    }

    public function beginTransaction()
    {
		$this->connection->beginTransaction();
    }

    public function commit()
    {
		$this->connection->commit();     
	}
	
	public function rollBack()
    {
		$this->connection->rollBack();     
    }

	public function getConnection()
	{
		return $this->connection;
	}
}
?>
