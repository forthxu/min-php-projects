<?php
require 'Sysconf.php';
SysconfModel::getInstance($sysconf);

//get data
$item = $sysconf->select(array('item'))
    ->where('tuid=:uid',array(':uid' => 100))
    ->getFirst();

$insertData = array(
    array('item' => 'abcd'),
    array('item' => 'abcd'),
    array('item' => 'abcd'),
    array('item' => 'abcd'),
);
//insert multirows data with REPLACE scheme
$sysconf->insert($sysconf->table(SysconfModel::table),$insertData,true,true);

//insert multirows data with ON DUPLICATE KEY UPDATE scheme 
$sysconf->insert($sysconf->table(SysconfModel::table),$insertData,true,false,true);

//update data
$sysconf->where('tuid=:uid AND priv<:priv',array(':uid'=>1,':priv'=>2))
    ->update($sysconf->table(SysconfModel::table),array(
        ':item' => 'asdf'
    ));