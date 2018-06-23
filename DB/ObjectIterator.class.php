<?php
include_once '../Util/QueryIterator.class.php';

/**
 * Classe interadora sobre objetos stdClass do PHP
 * @author fernando.schwambach
 */
class ObjectIterator extends QueryIterator{
    
    public function __construct(PDOStatement $stmt) {
        parent::__construct($stmt);
    }
    
    protected function fetchObject() {
        if($aResult = $this->Query->fetch(PDO::FETCH_ASSOC)){
            return $this->createObject($aResult);            
        }
        return false;        
    }
    
    /**
     * Cria um objeto com os valores retornados pela query com propriedades no padrão Camel Case
     * @param array $aRow
     * @return \stdClass
     */
    private function createObject($aRow){
        $oClass = new stdClass();
        foreach ($aRow as $key => $value) {
            $sProperty = $this->toCamelCase($key);
            $oClass->$sProperty = $value;
        }
        return $oClass;
    }
    
    /**
     * Converte a string para o padrão Camel Case
     * @param string $sField
     * @return string
     */    
    private function toCamelCase($sField){
        $aField = explode('_', $sField);
        $saida = Array();
        foreach ($aField as $key => $value) {
            $saida[] = ($key > 0)? ucfirst($value): $value;                         
        }
        return implode('', $saida);
    }
}
