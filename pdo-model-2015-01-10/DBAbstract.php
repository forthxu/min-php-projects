<?php

class DB_DBAbstract extends PDO {
    
    private static $instance;
    
    public $trans_started = false;
    public $trans_ok = true;
    
    public function __construct($dsn, $username, $passwd, $options=array()) {
       
        parent::__construct($dsn, $username, $passwd, $options);
    }
    
    /**
     * @final DB_DBAbstract $db
     * @param DB_DBAbstract $db
     * @return DB_DBAbstract
     */
    public static function getInstance(&$db=-1,$config=array()) {
        if(!self::$instance) {
            if(empty($config)) {
                try{
                    self::$instance = new self(
                            Yaf_Application::app()->getConfig()->get('db_dsn').';dbname='.
                            Yaf_Application::app()->getConfig()->get('db_name'),
                            Yaf_Application::app()->getConfig()->get('db_user'),
                            Yaf_Application::app()->getConfig()->get('db_password'),
                            array(
                                PDO::ATTR_TIMEOUT => 2,
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                            )
                    );
                }  catch (PDOException $err) {
                    throw new Exception($err->getMessage(), $err->getCode());
                }  catch (Exception $e) {
                    
                }
                self::$instance->query('SET names `'.Yaf_Application::app()->getConfig()->get('db_charset').'`');
            }else {
                try {
                    self::$instance = new self(
                        $config['dsn'].';dbname='.$config['name'],
                        $config['user'],
                        $config['password'],
                        array(
                            PDO::ATTR_TIMEOUT => 2,
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                        )
                    );
                }  catch (PDOException $err) {
                    throw new Exception($err->getMessage(), $err->getCode());
                }
                self::$instance->query('SET names `'.$config['charset'].'`');
            }
        }
        if($db!==-1)
            $db = self::$instance;
        return self::$instance;
    }
    
    public function setTransOk($ok=true) {
        $this->trans_ok = $ok;
    }
    
    public function trans_start() {
        if(!$this->trans_started && $this->beginTransaction())
            $this->trans_started = true;
        return $this->trans_started;
    }
    
    public function trans_stop() {
        if($this->inTransaction()) {
            if($this->trans_ok)
                $this->commit();
            else
                $this->rollBack();
        }
        $this->trans_started = false;
    }
}