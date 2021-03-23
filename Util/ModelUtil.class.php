<?php
namespace Util;
/**
 * Classe para manipulação de modelos
 * @author fernando.schwambach
 */
class ModelUtil {

    const GETTER = 1;
    const SETTER = 2;

    private static function process($oObject, $sMethod, $iType, $aArgs = array()) {
        if ($aMatch = self::extractClass($sMethod)) {
            $oNovo = self::getGetter($oObject, $aMatch[1]);
            return self::process($oNovo, $aMatch[2], $iType, $aArgs);
        } else {
            return self::invokeMethod($oObject, self::getMethodAsString($sMethod, $iType), $aArgs);
        }
    }

    private static function extractClass($sMethod) {
        $sClass = str_repeat('\.*\w*', 1);
        preg_match('/(' . $sClass . ')\.(.*)/', $sMethod, $aMatch);
        if (count($aMatch)) {
            return $aMatch;
        }
        return false;
    }

    public static function getGetter($oObject, $sMethod) {
        return self::process($oObject, $sMethod, self::GETTER);
    }

    public static function getSetter($oObject, $sMethod, array $aArgs) {
        return self::process($oObject, $sMethod, self::SETTER, $aArgs);
    }

    private static function invokeMethod($oObject, $sMethod, $aArgs = array()) {
        if (is_object($oObject)) {
            if (method_exists($oObject, $sMethod)) {
                return call_user_func_array(array($oObject, $sMethod), $aArgs);
            } else {
                throw new \Exception('Metodo ' . $sMethod . ' não existente na classe ' . get_class($oObject));
            }
        }
    }

    private static function getMethodAsString($sMethod, $iType) {
        if ($iType == self::GETTER) {
            return 'get' . ucfirst($sMethod);
        } else {
            return 'set' . ucfirst($sMethod);
        }
    }

}