<?php
class BaseAction
{
	private $_id;
	private $_controller;

	public function __construct($controller,$id)
	{
		$this->_controller=$controller;
		$this->_id=$id;
	}
	
	public function getController()
	{
		return $this->_controller;
	}

	public function getId()
	{
		return $this->_id;
	}
	
	public function run()
	{
		$method='action'.$this->getId();
		$this->getController()->$method();
	}
	
}
?>