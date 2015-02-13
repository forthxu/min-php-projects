<?php

class SysconfModel extends DB_ModelAbstract {

    private static $instance;

    const table = 'sysconf';

    public static function getInstance(&$model = -1, $config = array()) {
        if (!self::$instance)
            self::$instance = new self($config);
        if ($model !== -1)
            $model = self::$instance;
        else
            return self::$instance;
    }

}
