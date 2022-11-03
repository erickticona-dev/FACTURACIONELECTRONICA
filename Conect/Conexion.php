<?php
namespace Conect;

 date_default_timezone_set('America/Lima'); 
 class Conexion{
      
        const HOST = '127.0.0.1';
        const USER = 'root';
        const PASSWORD = '';
        const BDNAME = 'bdsistema';
    public static function conectar() {
       
        $link = new \PDO("mysql:host=".self::HOST."; dbname=".self::BDNAME.";",
                        self::USER, self::PASSWORD);
        $link->exec("set names utf8");
        return $link;
    }
}
