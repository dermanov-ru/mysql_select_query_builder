<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 31.05.2018
 * Time: 17:23
 *
 *
 */


namespace Core\Db;

/*
 * TODO implement singleton
 * */
class DbManager
{
    public static function getConnection(  )
    {
        // TODO get params from config more accuratly :)
        require $_SERVER["DOCUMENT_ROOT"] . "/lib/conf/db.php";
    
        return new DbConnection($user, $pass, $db);
    }
}