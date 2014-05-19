<?php
class BaseController{
	
	public $defaultAction='index';
	
	private $_id;
	private $_action;
	
	function __construct($id){
		$this->_id=$id;
	}
	
	public function init()
	{
	}
	
	public function run($actionID)
	{
		if($actionID==='')
			$actionID=$this->defaultAction;
		if(($action=$this->createAction($actionID))!==null){
			$this->_action=$action;
			if($this->beforeAction($action))
			{
				$action->run();
			}
		}
		else
			throw new Exception("The system is unable to find the requested action '" . $actionID. "'");
	}
	
	public function createAction($actionID)
	{
		if($actionID==='')
			$actionID=$this->defaultAction;
		if(method_exists($this,'action'.$actionID) && strcasecmp($actionID,'s')) // we have actions method
			return new BaseAction($this,$actionID);
		
		return null;
	}
	
	public function getViewFile($viewName)
	{
		$viewFile=WinBase::app()->getViewPath().DIRECTORY_SEPARATOR.$viewName;

		if(is_file($viewFile.'.php'))
			return $viewFile.'.php';
		else
			return false;			
	}
	
	public function render($view,$data=null,$return=false)
	{
		if(($viewFile=$this->getViewFile($view)) === false)
		{
			throw new Exception("Cannot find the requested view '{$view}'.");	
		}
		
		$output=$this->renderFile($viewFile,$data,true);

		if($return)
			return $output;
		else
			echo $output;
	}	

	public function renderFile($_viewFile,$_data=null,$return=false){
		if(is_array($_data))
			extract($_data,EXTR_PREFIX_SAME,'data');
		else
			$data=$_data;
		if($return)
		{
			ob_start();
			ob_implicit_flush(false);
			require($_viewFile);
			return ob_get_clean();
		}
		else
			require($_viewFile);
	}

	public function getId(){
		return $this->_id;
	}
	
	public function getAction()
	{
		return $this->_action;
	}
	
	protected function beforeAction($action)
	{
		return true;
	}
}
?>
