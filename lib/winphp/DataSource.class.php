<?php
    abstract class PDODataSource
    { 
        private $dsn;
        private $username;
        private $password;
        private $_pdo;
        public $charset;
        
        public $options = array();

        protected function __construct($dsn, $username, $password,$persistent=false,$charset="utf8")
        {
            $this->dsn = $dsn;
            $this->username = $username;
            $this->password = $password;
            
            if($persistent){
                $this->options[PDO::ATTR_PERSISTENT] = true;
            }
            
            $this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . $charset;

            $this->connect();
        }
        
        public function connect()
        {
            if($this->_pdo===null)
            {
                try
                {
                    $pdo= new PDO($this->dsn, $this->username, $this->password,$this->options);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

                    $this->_pdo=$pdo;
                }
                catch(Exception $e)
                {
                    throw new Exception('Db Connection failed to open the DB connection.',(int)$e->getCode(),$e->errorInfo);
                }     
            }
        }
        
        public function getPdoInstance()
        {
            return $this->_pdo;
        }
    }
?>
