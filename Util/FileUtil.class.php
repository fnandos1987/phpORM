<?php

/**
 * Description of FileUtil
 * @author fernando.schwambach
 */
final class FileUtil {

    const MODEL = 'Model';
    const PERSISTENCE = 'Persistencia';
    const DB = 'DB';
    const CONTROLLER = 'Controller';
    const UTIL = 'Util';

    public static function includeAll(Array $aFiles, $sType) {
        switch ($sType) {
            case self::MODEL: $sMethod = 'includeModel';
                break;
            case self::PERSISTENCE: $sMethod = 'includePersistence';
                break;
            case self::CONTROLLER: $sMethod = 'includeController';
                break;
            case self::DB: $sMethod = 'includeDb';
                break;
            case self::UTIL: $sMethod = 'includeUtil';
                break;
        }

        foreach ($aFiles as $sFile) {
            self::$sMethod($sFile);
        }
    }

    public static function includeModel($sName) {
        if (!class_exists($sName)) {
            $sFile = self::getDocRoot() . "Model/{$sName}.class.php";
            if (file_exists($sFile)) {
                include($sFile);
            }
        }
    }

    public static function includePersistence($sName) {
        if (!class_exists($sName)) {
            $sFile = self::getDocRoot() . "Persistencia/{$sName}.class.php";
            if (file_exists($sFile)) {
                include($sFile);
            }
        }
    }

    public static function includeController($sName) {
        if (!class_exists($sName)) {
            $sFile = self::getDocRoot() . "Controller/{$sName}.class.php";
            if (file_exists($sFile)) {
                include($sFile);
            }
        }
    }

    public static function includeDb($sName) {
        if (!class_exists($sName)) {
            $sFile = self::getDocRoot() . "DB/{$sName}.class.php";
            if (file_exists($sFile)) {
                include($sFile);
            }
        }
    }

    public static function includeUtil($sName) {
        if (!class_exists($sName)) {
            $sFile = dirname(__FILE__) . "Util/{$sName}.class.php";
            if (file_exists($sFile)) {
                include($sFile);
            }
        }
    }

    private static function getDocRoot() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return $_SERVER['DOCUMENT_ROOT'] . "/EnterSimv1.0/";            
        }else{
            return '/var/www/webroot/EnterSimv10/';            
        }
    }
}