<?php

include_once "../Util/FileUtil.class.php";
FileUtil::includeUtil("ModelUtil");

if (!function_exists('autoLoaderDB')) {

    function autoLoaderDB($class) {
        FileUtil::includeDb($class);
    }

}
spl_autoload_register('autoLoaderDB');

/**
 * Description of Persistence
 *
 * @author fernando.schwambach
 */
abstract class Persistence extends PersistenceMapper {

    public function __construct() {
        $this->configure();
    }

    abstract protected function configure();

    protected function bindType($xValue) {
        switch (true) {
            case is_numeric($xValue): return PDO::PARAM_INT;
            case is_bool($xValue): return PDO::PARAM_BOOL;
            case is_null($xValue): return PDO::PARAM_NULL;
            default : return PDO::PARAM_STR;
        }
    }

    private function addLog($sEntry) {
        Transaction::setLogger(new LoggerTXT('../log.txt'));
        Transaction::log($sEntry);
    }

    private function createCriteria() {
        $oCriteria = new Criteria();
        foreach ($this->mapping as $oMap) {
            if ($oMap->getTableKey()) {
                $oCriteria->add(new Filter($oMap->getNameDb(), Filter::IGUAL, ':' . $oMap->getNameDb(), false, true));
            }
        }
        return $oCriteria;
    }

    public function insert($aDados) {
        $oInsert = new SqlInsert();
        $oInsert->setEntity($this->getTableName());
        foreach ($this->mapping as $oMap) {
            $oInsert->setRowPrepared($oMap->getNameDb());
        }

        Transaction::open();
        $stmt = Transaction::get()->prepare($oInsert->getInstruction());

        foreach ($this->mapping as $oMap) {
            if (array_key_exists($oMap->getNameClass(), $aDados)) {
                $stmt->bindValue(':' . $oMap->getNameDb(), $aDados[$oMap->getNameClass()], $this->bindType($aDados[$oMap->getNameClass()]));
            } else {
                $stmt->bindValue(':' . $oMap->getNameDb(), null, PDO::PARAM_NULL);
            }
        }

        try {
            $stmt->execute();
            Transaction::commit();
            return true;
        } catch (Exception $ex) {
            $this->addLog($ex->getMessage());
            Transaction::rollback();
            return false;
        }
    }

    public function update($aDados) {
        $oUpdate = new SqlUpdate();
        $oUpdate->setEntity($this->getTableName());
        foreach ($this->mapping as $oMap) {
            if (array_key_exists($oMap->getNameClass(), $aDados)) {
                $oUpdate->setRowPrepared($oMap->getNameDb());
            }
        }
        $oUpdate->setCriteria($this->createCriteria());
        Transaction::open();
        $stmt = Transaction::get()->prepare($oUpdate->getInstruction());
        foreach ($this->mapping as $oMap) {
            if (array_key_exists($oMap->getNameClass(), $aDados)) {
                $stmt->bindValue(':' . $oMap->getNameDb(), $aDados[$oMap->getNameClass()], $this->bindType($aDados[$oMap->getNameClass()]));
            }
        }

        try {
            $stmt->execute();
            Transaction::commit();
            return true;
        } catch (Exception $ex) {
            $this->addLog($ex->getMessage());
            Transaction::rollback();
            return false;
        }
    }

    public function delete($aDados) {
        $oDelete = new SqlDelete();
        $oDelete->setEntity($this->getTableName());

        $oCriteria = new Criteria();
        foreach ($this->mapping as $oMap) {
            if ($oMap->getTableKey() && isset($aDados[$oMap->getNameClass()])) {
                $oCriteria->add(new Filter($oMap->getNameDb(), Filter::IGUAL, ':' . $oMap->getNameDb(), false, true));
            }
        }
        $oDelete->setCriteria($oCriteria);
        Transaction::open();
        $stmt = Transaction::get()->prepare($oDelete->getInstruction());
        foreach ($this->mapping as $oMap) {
            if ($oMap->getTableKey() && isset($aDados[$oMap->getNameClass()])) {
                $stmt->bindValue(':' . $oMap->getNameDb(), $aDados[$oMap->getNameClass()], $this->bindType($aDados[$oMap->getNameClass()]));
            }
        }

        try {
            $stmt->execute();
            Transaction::commit();
            return true;
        } catch (Exception $ex) {
            $this->addLog($ex->getMessage());
            Transaction::rollback();
            return false;
        }
    }

    /**
     * Retorna todos os registros da tabela
     * @return \ModelIterator
     */
    public function getAllFromTable() {
        $oSelect = new SqlSelect();
        $oSelect->setEntity($this->getTableName());
        $oSelect->addColumn('*');

        Transaction::open();
        $stmt = Transaction::get()->prepare($oSelect->getInstruction());

        return new ModelIterator($this, $stmt);
    }

    /**
     * Busca um registro com base na chave passada, caso não seja informado uma chave retorna o primeiro registro que encontrar
     * @param Array $aId
     * @return Object
     */
    public function findById($aId) {
        $oSelect = new Query($this);
        $oSelect->addColumn('*');

        foreach ($this->mapping as $oMap) {
            if ($oMap->getTableKey() && isset($aId[$oMap->getNameClass()])) {
                $oSelect->addPreparedCondition($oMap->getNameDb(), Filter::IGUAL, $aId[$oMap->getNameClass()]);
            }
        }
        return $oSelect->getOneByQuery();
    }

    /**
     * Busca as colunas especificadas um registro com base na chave passada, caso não seja informado uma chave retorna o primeiro registro que encontrar
     * @param Array $aId
     * @return Object
     */
    public function findColumns(array $aCols, Array $aId) {
        $oSelect = new Query($this);
        $oSelect->addColumn(implode(',', $aCols));

        foreach ($this->mapping as $oMap) {
            if ($oMap->getTableKey() && isset($aId[$oMap->getNameClass()])) {
                $oSelect->addPreparedCondition($oMap->getNameDb(), Filter::IGUAL, $aId[$oMap->getNameClass()]);
            }
        }
        return $oSelect->getOneByQuery();
    }

    public function getAllFromTableAsJson($iLimit, $iOffset, array $aOrder = array()) {
        $oQryCount = new Query($this);       
        $oQryData = new Query($this);
        if (count($aOrder)) {
            $oQryData->addOrderBy(implode(',', $aOrder));
        }
        $oQryData->addOtherCommand('LIMIT', $iLimit);
        $oQryData->addOtherCommand('OFFSET', $iOffset);
        
        $aReturn = Array();
        $aReturn['total'] = $oQryCount->getCount();
        foreach ($oQryData->getAllByQuery() as $oModel) {
            $aTemp = Array();
            foreach ($this->getMapping() as $oMap) {
                $aTemp[$oMap->getNameClass()] = ModelUtil::getGetter($oModel, $oMap->getNameClass());
            }
            $aReturn['rows'][] = $aTemp;
        }
        return json_encode($aReturn);
    }

}