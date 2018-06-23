<?php
include_once '../Util/ModelUtil.class.php';
/**
 * Classe base para as classes de consulta, implementa os métodos comuns a todas elas
 * @author fernando.schwambach
 */
class BaseQuery {

    private $Persistence;
    private $Criteria;
    private $aColumns = Array();
    private $aBind = Array();
    private $i = 1;

    public function __construct($oPersiste) {
        $this->Persistence = $oPersiste;
        $this->Criteria = new Criteria();
    }

    /**
     * Retorna a persistência setada na query
     * @return /Persistence
     */
    public function getPersistence() {
        return $this->Persistence;
    }
    
    /**
     * Retorna o critério de seleção da query
     * @return /Criteria
     */
    public function getCriteria() {
        return $this->Criteria;        
    }

    /**
     * Adiciona uma coluna na consulta
     * @param string $sColumn
     */
    public function addColumn($sColumn) {
        $this->aColumns[] = $sColumn;
    }

    /**
     * Retorna as colunas setadas na query
     * @return array
     */
    public function getColumns() {
        return $this->aColumns;
    }

    /**
     * Adiciona condições com valores preparados na consulta
     * @param string $sColumn
     * @param string $sOperator
     * @param mixed $sValue
     * @param mixed $sValue1
     * @param string $sType
     */
    public function addPreparedCondition($sColumn, $sOperator, $sValue, $sValue1 = false, $sType = Expression::AND_OPERATOR) {
        $this->Criteria->add(new Filter($sColumn, $sOperator, '?', ($sValue1) ? '?' : $sValue1, true), $sType);
        $this->addBind($sValue);
        if ($sValue1) {
            $this->addBind($sValue1);
        }
    }

    /**
     * Adiciona condições com valores fixos na consulta
     * @param string $sColumn
     * @param string $sOperator
     * @param mixed $sValue
     * @param mixed $sValue1
     * @param string $sType
     */
    public function addFixedCondition($sColumn, $sOperator, $sValue, $sValue1 = false, $sType = Expression::AND_OPERATOR) {
        $this->Criteria->add(new Filter($sColumn, $sOperator, $sValue, $sValue1, false), $sType);
    }

    /**
     * Monta e retorna o SQL da query
     * @return string
     */
    protected function getSelect() {
        $oSql = new SqlSelect();
        $oSql->setEntity($this->Persistence->getTableName());
        $oSql->addColumn(implode(',', $this->getColumns()));
        $oSql->setCriteria($this->Criteria);             
        return $oSql->getInstruction();
    }

    /**
     * Adiciona um valor em um bind da consulta PDO
     * @param mixed $sValue
     */
    public function addBind($sValue) {
        $this->aBind[] = Array($this->i, $sValue, $this->bindType($sValue));
        $this->i++;
    }

    /**
     * Retorna o array de binds setados na query
     * @return array
     */
    public function getBind() {
        return $this->aBind;
    }

    /**
     * Adiciona colunas para a cláusula ORDER BY
     * @param string $sColumn
     */
    public function addOrderBy($sColumn) {
        $this->Criteria->addOrderBy($sColumn);
    }

    /**
     * Adiciona colunas para a cláusula GROUP BY
     * @param string $sColumn
     */
    public function addGroupBy($sColumn) {
        $this->Criteria->addGroupBy($sColumn);
    }

    /**
     * Método para adicionar outros comandos, podem ser comandos específicos do banco de dados como LIMIT e OFFSET por exemplo ou genéricos como HAVING
     * @param string $sName
     * @param mixed $xValue
     */
    public function addOtherCommand($sName, $xValue) {
        $this->Criteria->addOtherCommand($sName, $xValue);
    }
    
    protected function bindType($xValue) {
        switch (true) {
            case is_numeric($xValue): return PDO::PARAM_INT;
            case is_bool($xValue): return PDO::PARAM_BOOL;
            case is_null($xValue): return PDO::PARAM_NULL;
            default : return PDO::PARAM_STR;
        }
    }
}