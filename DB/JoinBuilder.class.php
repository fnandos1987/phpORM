<?php
namespace Db;
/**
 * Classe facilitadora para criação de joins
 * @author fernando.schwambach
 */
class JoinBuilder {

    private $table; //tabela do join
    private $aliasTable; //alias da tabela do join
    private $tableJoin; //tabela a ser unida
    private $typeJoin; //tipo do join
    private $columns = Array(); // colunas do link
    private $fixedValues = Array(); //filtros fixos

    public function __construct($table, $tableJoin, $aliasTable = null, $type = JoinMapper::JOIN) {
        $this->table = $table;
        $this->tableJoin = $tableJoin;
        $this->typeJoin = $type;
        $this->aliasTable = $aliasTable;
    }
    
    /**
     * Trata alias aplicado na tabela do join
     * @return string
     */
    private function getTableName() {
        if ($this->aliasTable) {
            return $this->aliasTable;            
        }        
        return $this->table;
    }

    /**
     * Mapeia a relação tableJoin.col operador table.col
     * @param string $tableCol
     * @param string $tableJoinCol
     * @param string $sOperator
     * @return JoinBuilder
     */
    public function addKeyColumns($tableCol, $tableJoinCol = null, $sOperator = Filter::IGUAL) {
        if (!isset($tableJoinCol)) {
            $tableJoinCol = $tableCol;
        }
        $this->columns[] = sprintf('%s.%s %s %s.%s', $this->tableJoin, $tableJoinCol, $sOperator, $this->getTableName(), $tableCol);
        return $this;
    }

    /**
     * Adiciona filtros com valores fixos no join, ex.: id = 1
     * @param string $tableCol
     * @param mixed $value
     * @param string $operator
     * @return JoinBuilder
     */
    public function addFixedValues($tableCol, $value, $operator = Filter::IGUAL) {
        $this->fixedValues[] = new Filter(sprintf('%s.%s', $this->getTableName(), $tableCol), $operator, $value);
        return $this;
    }

    /**
     * Retorna o sql do join
     * @return string
     */
    public function dump() {
        foreach ($this->fixedValues as /* @var $filter Filter */ $filter) {
            $this->columns[] = $filter->dump();
        }

        return sprintf('%s %s %s ON %s', $this->typeJoin, $this->table, $this->aliasTable, implode(' AND ', $this->columns));
    }

}
