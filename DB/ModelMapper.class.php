<?php
namespace Db;
/**
 * Classe que controla o mapeamento das colunas nas persistências
 * @author fernando.schwambach
 */
class ModelMapper {

    private $nameDb;
    private $nameClass;
    private $tableKey;    
    
    public function __construct($sNameDb, $sNameClass, $bKey = false) {
        $this->nameDb = $sNameDb;
        $this->nameClass = $sNameClass;
        $this->tableKey = $bKey;
    }

    public function getNameDb() {
        return $this->nameDb;
    }

    public function getNameClass() {
        return $this->nameClass;
    }

    public function getTableKey() {
        return $this->tableKey;
    }

    public function setNameDb($nameDb) {
        $this->nameDb = $nameDb;
    }

    public function setNameClass($nameClass) {
        $this->nameClass = $nameClass;
    }

    public function setTableKey($tableKey) {
        $this->tableKey = $tableKey;
    }

}