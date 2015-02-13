<?php

class DB_ModelAbstract {
    
    /**
     * @var DB_DBAbstract PDO实例
     */
    public $db;
    
    /**
     * @var string 表前缀
     */
    public $dbprefix;
    
    const JOIN_LFET = ' LEFT JOIN ';
    const JOIN_RIGHT = ' RIGHT JOIN ';
    const JOIN_INNER = ' INNER JOIN ';
    const JOIN = 'JOIN';
    
    const ORDER_DESC = ' DESC ';
    const ORDER_ASC = ' ASC ';
    
    private $ar_select = '*';
    private $ar_where = '1';
    private $ar_where_bind = array();
    private $ar_join = '';
    private $ar_order = '';
    private $ar_limit = '';
    private $ar_group = '';
    private $ar_having = '';

    private $cache_select = 0;
    private $cache_where = 0;
    private $cache_join = 0;
    private $cache_order = 0;
    private $cache_limit = 0;
    private $cache_group = 0;
    private $cache_having = 0;
    
    private $cache_ar = 0;
    
    protected $end_line = "\r\n";

    private static $instance;

    /**
     * 
     * @param type $config dsn,user,password,charset,prefix
     */
    public function __construct($config=array()) {
        DB_DBAbstract::getInstance($this->db,$config);
        $this->dbprefix = !empty($config) ? $config['prefix']:Yaf_Application::app()->getConfig()->get('db_prefix');
    }

    public function select($fields = array()) {
        //将`表名`.字段名 处理成 `表名`.`字段名`
        if(!empty($fields))
            $this->ar_select = '';
        if(is_string($fields))
            $this->ar_select = $fields;
        else {
            foreach($fields as $v)
                $this->ar_select .= $this->field($v).',';
            $this->ar_select = substr($this->ar_select, 0, -1);
        }
        if($this->cache_ar)
            $this->cache_select = 1;
        return $this;
    }
    
    /**
     * 
     * @param type $table 带前缀的表名
     * @param type $where 条件 表名须带前缀
     * @param type $type
     * @return \DB_ModelAbstract
     */
    public function join($table, $where, $type=self::JOIN_LFET) {
        $this->ar_join .= $type. ''.$table.' ON '.$where;
        if($this->cache_ar)
            $this->cache_join = 1;
        return $this;
    }
    
    /**
     * 
     * @param str $field 字段名
     * @param array $arg 选项列表
     * @param bool $not 是否是Not语句
     * @return string  返回  `field` IN ('abc',...)语句
     */
    public function where_in($field, $arg=array(), $not=false) {
        $sql = $this->field($field).($not ? ' NOT IN (' : ' IN (');
        $tmp = '';
        foreach ($arg as $item)
            $tmp .= $this->db->quote ($item).',';
        $sql .= substr($tmp, 0, -1);
        return  $sql.') ';
    }
    
    /**
     * 
     * @param type $field
     * @param type $min
     * @param type $max
     * @param bool $not 是否是NOT 语句
     * @return string 返回 `field` BETWEEN min AND max
     */
    public function between($field, $min, $max, $not=false) {
        return $this->field($field).
                ' BETWEEN '.$this->db->quote($min, PDO::PARAM_INT).
                ' AND '.$this->db->quote($max, PDO::PARAM_INT);
    }
    
    public function where($where=' 1', $args=array()) {
        $this->ar_where = $where;
        $this->ar_where_bind = $args;
        if($this->cache_ar)
            $this->cache_where = 1;
        return $this;
    }
    
    public function order($order_by, $type=self::ORDER_ASC) {
        $this->ar_order = ' ORDER BY ';
        if(is_string($order_by))
             $this->ar_order .= $order_by.' '.$type;
        else {
            foreach ($order_by as $field => $type) {
                $this->ar_order .= $field.' '.$type.',';
            }
            $this->ar_order = substr($this->ar_order, 0, -1);
        }
        if($this->cache_ar)
            $this->cache_order = 1;
        return $this;
    }
    
    public function group($group_by) {
        $this->ar_group = ' GROUP BY ';
        if(is_string($group_by))
            $this->ar_group .= $group_by;
        else{
            foreach ($group_by as $field)
                $this->ar_group .= $field.',';
            $this->ar_group = substr($this->ar_group, 0, -1);
        }
        if($this->cache_ar)
            $this->cache_group = 1;
        return $this;
    }
    
    public function limit($num, $offset=0) {
        $this->ar_limit = ' LIMIT '.$offset.','.$num;
        if($this->cache_ar)
            $this->cache_limit = 1;
        return $this;
    }
    
    public function having($condition) {
        $this->ar_having = ' HAVING '.$condition;
        if($this->cache_ar)
            $this->cache_having = 1;
        return $this;
    }
    
    public function trans_start() {
        return $this->db->trans_start();
    }
    
    public function trans_stop() {
        $this->db->trans_stop();
    }
    
    private function prepareGet($table) {
        $stmt = $this->db->prepare('SELECT '.$this->ar_select.' FROM '.$table.$this->ar_join.' WHERE '.$this->ar_where.$this->ar_group.$this->ar_order.$this->ar_having.$this->ar_limit);
        if($stmt===false) {
            $this->onError('Get stmt object failed, sql maybe invalid:'.'SELECT '.$this->ar_select.' FROM '.$table.$this->ar_join.' WHERE '.$this->ar_where.$this->ar_group.$this->ar_order.$this->ar_having.$this->ar_limit);
            return false;
        }
        foreach($this->ar_where_bind as $k=>$v) {
            $stmt->bindValue ($k, $v);
        }
        return $stmt;
    }
    
    private function clearAr() {
        if(!$this->cache_group)
            $this->ar_group = '';
        if(!$this->cache_having)
            $this->ar_having = '';
        if(!$this->cache_join)
            $this->ar_join = '';
        if(!$this->cache_limit)
            $this->ar_limit = '';
        if(!$this->cache_order)
            $this->ar_order = '';
        if(!$this->cache_select)
            $this->ar_select = '*';
        if(!$this->cache_where) {
            $this->ar_where = ' 1';
            $this->ar_where_bind = array();
        }
    }
    
    /**
     * 开启缓存
     */
    public function cache_start() {
        $this->cache_ar = 1;
    }
    
    /**
     * 关闭缓存
     */
    public function cache_stop() {
        $this->cache_ar = 0;
    }
    
    /**
     * 清除缓存
     */
    public function cache_flush() {
        $this->ar_group = '';
        $this->ar_having = '';
        $this->ar_join = '';
        $this->ar_limit = '';
        $this->ar_order = '';
        $this->ar_select = '*';
        $this->ar_where = ' 1';
        $this->ar_where_bind = array();
        
        $this->cache_group = 0;
        $this->cache_having = 0;
        $this->cache_join = 0;
        $this->cache_limit = 0;
        $this->cache_order = 0;
        $this->cache_select = 0;
        $this->cache_where = 0;
    }
    
    /**
     * 
     * @param type $table 须带表前缀
     */
    public function get($table=null) {
        if($table===null)
            $table = $this->table ($this::table);
        $stmt = $this->prepareGet($table);
        if($stmt===false) {
            $this->clearAr();
            return false;
        }
        if($this->queryStmt($stmt)===false) {
            $this->clearAr();
            return false;
        }
        $this->clearAr();
        return $this->procStmt($stmt);
    }
    
    /**
     * 获取第一行第一列的值
     */
    public function getFirst($table=null) {
        if($table===null)
            $table = $this->table ($this::table);
        $stmt = $this->prepareGet($table);
        if($stmt===false) {
            $this->clearAr();
            return false;
        }
        if($this->queryStmt($stmt)===false) {
            $this->clearAr();
            return false;
        }
        $this->clearAr();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }
    
    public function getRow($table=null) {
        if($table===null)
            $table = $this->table ($this::table);
        $stmt = $this->prepareGet($table);
        if($stmt===false) {
            $this->clearAr();
            return false;
        }
        if($this->queryStmt($stmt)===false) {
            $this->clearAr();
            return false;
        }
        $this->clearAr();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 查询结果集行数
     * @param type $table
     */
    public function count($table=null) {
        if($table===null)
            $table = $this->table ($this::table);
        $this->ar_select = 'COUNT(*) AS num';
        $stmt = $this->prepareGet($table);
        if($stmt===false) {
            $this->clearAr();
            return false;
        }
        if($this->queryStmt($stmt)===false) {
            $this->clearAr();
            return false;
        }
        $this->clearAr();
        $result = $this->procStmt($stmt, PDO::FETCH_COLUMN);
        return empty($result) ? 0 : (int)$result[0];
    }
    
    /**
     * 查询是否有符合条件的记录
     * @param type $table
     */
    public function hasOne($table=null) {
        if($table===null)
            $table = $this->table ($this::table);
        $this->ar_select = '1';
        $stmt = $this->prepareGet($table);
        if($stmt===false) {
            $this->clearAr ();
            return false;
        }
        if($this->queryStmt($stmt)===false) {
            $this->clearAr();
            return false;
        }
        $this->clearAr();
        $result = $this->procStmt($stmt, PDO::FETCH_COLUMN);
        return empty($result) ? 0 : (int)$result[0];
    }
    
    /**
     * 
     * @param string $table
     * @param array $set array(':username' => Abc, 'fieldname'=>Bcd ) 键名不以:开头则不进行bind
     */
    public function update($table, $set) {
        $sql = 'UPDATE '.$table.$this->ar_join.' SET ';
        foreach ($set as $field => $val) {
            if(substr($field, 0, 1)!==':')
                $sql .= $field.'='.$val.',';
            else {
                $tmp = substr($field, 1);
                $sql .= $this->field($tmp).'='.$field.',';
            }
        }
        $sql = substr($sql, 0, -1);
        $sql .= ' WHERE '.$this->ar_where.$this->ar_order.$this->ar_limit;
        
        $stmt = $this->db->prepare($sql);
        if($stmt===false) {
            $this->onError('Get stmt failed,sql maybe invalid:'.$sql);
            $this->clearAr();
            return false;
        }
        foreach ($set as $field => $val) {
            if(substr($field, 0, 1)===':')
                $stmt->bindValue ($field, $val);
        }
        foreach ($this->ar_where_bind as $key=>$val)
            $stmt->bindValue ($key, $val);
        $this->clearAr();
        return $this->queryStmt($stmt);
    }
    
    public function delete($table=null) {
        if($table===null)
            $table = $this->table ($this::table);
        $stmt = $this->db->prepare('DELETE FROM '.$table.' WHERE '.$this->ar_where.$this->ar_order.$this->ar_limit);
        if($stmt===false) {
            $this->onError('Get stmt failed,sql maybe invalid:'.'DELETE FROM '.$table.' WHERE '.$this->ar_where.$this->ar_order.$this->ar_limit);
            $this->clearAr();
            return false;
        }
        foreach ($this->ar_where_bind as $k=>$v)
            $stmt->bindValue ($k, $v);
        $this->clearAr();
        return $this->queryStmt($stmt);
    }
    
    /**
     * 插入数据
     * @param str $table
     * @param array $set
     * @param bool $multi 是否是多行插入
     * @param bool $dup 是否在sql末尾加入 on duplicate update语句，与$replace参数冲突
     */
    public function insert($table, $set, $multi=false, $replace=false, $dup=false) {
        $sql = $replace ? 'REPLACE INTO ' : 'INSERT INTO ';
        $sql .= $table.'(';
        $binds = array();
        $dupSql = ' ON DUPLICATE KEY UPDATE ';
        if($multi) {
            foreach($set as $item) {
                $keys = array_keys($item);
                break;
            }
            foreach ($keys as $v) {
                $v = $this->field($v);
                $sql .= $v.',';
                if($dup)
                    $dupSql .= $v.'=VALUES('.$v.'),';
            }
            $sql = substr($sql, 0, -1).') VALUES ';
            foreach ($set as $item) {
                $sql .= '(';
                foreach ($keys as $v) {
                    $binds[] = $item[$v];
                    $sql .= '?,';
                }
                $sql = substr($sql, 0, -1);
                $sql .= '),';
            }
            $sql = substr($sql, 0, -1);
        }else {
            $tmp = ') VALUES (';
            foreach($set as $field => $val) {
                $field = $this->field($field);
                $sql .= $field.',';
                $tmp .= '?,';
                $binds[] = $val;
                if($dup)
                    $dupSql .= $field.'=VALUES('.$field.'),';
            }
            $sql = substr($sql, 0, -1).  substr($tmp, 0, -1).')';
        }
        if($dup)
            $sql .= substr($dupSql,0,-1);
        $stmt = $this->db->prepare($sql);
        if($stmt===false) {
            $this->onError('Get stmt failed,sql maybe invalid:'.$sql);
            $this->clearAr();
            return false;
        }
        foreach ($binds as $k=>$v)
            $stmt->bindValue (++$k, $v);
        unset($sql, $tmp, $binds);
        $this->clearAr();
        return $this->queryStmt($stmt);
    }
    
    public function last_id() {
        return $this->db->lastInsertId();
    }
    
    public function table($table) {
        return '`'.$this->dbprefix.$table.'`';
    }
    
    /**
     * 处理字段名 , 含 * 号的字段忽略
     * 字段名 => `字段名`
     * `表名`.字段名 => `表名`.`字段名`
     * COUNT(key) => COUNT(`key`)
     * COUNT(`table`.key) => COUNT(`table`.`key`)
     * @param string $v 字段名
     */
    public function field($v) {
        if(strpos($v, '*')!==false) return $v;
        
        $prefix = $suffix = '';
        if(strpos($v, '(')!==false) {
            //字段里有函数(SUM/AVG...)操作
            $offsetLeft = strrpos($v, '(')+1;
            $offsetRight = strpos($v, ')');
            $prefix = substr($v, 0, $offsetLeft);
            $suffix = substr($v, $offsetRight);
            $v = substr($v, $offsetLeft, $offsetRight-$offsetLeft);
        }
        return strpos ($v, '.')===false ? 
                $prefix.'`'.$v.'`'.$suffix :
                $prefix.substr($v, 0, strpos ($v, '.')+1).'`'.substr ($v, strpos ($v, '.')+1).'`'.$suffix;
    }
    
    /**
     * 
     * @param PDOStatement $stmt
     * @return bool
     */
    protected function queryStmt(&$stmt) {
        $bool = false;
        try {
            $bool = $stmt->execute();
            if($this->db->trans_started) $this->db->trans_ok = $bool;
            if(!$bool) {
                $info = $stmt->errorInfo();
                $this->onError ($stmt->queryString.'['.$info[1].']'.$info[2].$this->end_line);
            }
        } catch (Exception $ex) {
            if($this->db->trans_started) $this->db->trans_ok = $bool;
            $info = $stmt->errorInfo();
            $this->onError($stmt->queryString.'['.$info[1].']'.$info[2].'['.$ex->getCode().']'.$ex->getMessage().$this->end_line);
        }
        return $bool;
    }
    
    public function setTransOk($ok=true) {
        $this->db->setTransOk($ok);
    }
    
    /**
     * 处理执行sql后的PDOStatement 对象
     * @param PDOStatement $stmt
     */
    protected function procStmt(&$stmt, $mode=PDO::FETCH_ASSOC, $args=array()) {
        if(empty($args))
            return $stmt->fetchAll($mode);
        else
            return $stmt->fetchAll ($mode, $args);
    }

    /**
     * 执行一条单独的sql
     * @param type $sql
     */
    public function query($sql) {
        try {
            $stmt = $this->db->query($sql);
            if($stmt===false) {
                if($this->db->trans_started)
                    $this->db->trans_ok = false;
                $info = $this->db->errorInfo();
                $this->onError('SQL:'.$sql.'['.$info[1].']'.$info[2].$this->end_line);
            }
            return $this->procStmt($stmt);
        } catch (Exception $ex) {
            if($this->db->trans_started) $this->db->trans_ok = $bool;
            $info = $this->db->errorInfo();
            $this->onError('SQL:'.$sql.'['.$info[1].']'.$info[2].'['.$ex->getCode().']'.$ex->getMessage().$this->end_line);
        }
    }
    
    /**
     * 将若干条件拼接
     * @param array $cons 二维数组 array( array('a=:a', 'OR'), ... )
     * @return string 返回拼接好的语句
     */
    public function buildWhere($cons=array()) {
        $sql = ' 1 ';
        foreach($cons as $con) {
            if(!isset($con[1]))
                $con[] = ' AND ';
            $sql .= $con[1].$con[0];
        }
        return $sql;
    }
    
    public function onError($msg) {
        echo __CLASS__.':'.$msg;
    }
    
    
    /**
     * 
     * @param DB_ModelAbstract $model
     * @return DB_ModelAbstract $instance
     */
    public static function getInstance(&$model=-1,$config=array()) {
        if(!self::$instance)
            self::$instance = new self($config);
        if($model!==-1)
            $model = self::$instance;
        else
            return self::$instance;
    }
}