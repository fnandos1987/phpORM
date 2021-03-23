<?php
namespace Db;
/**
 * Classe para manipulação de instrucões INSERT no banco de dados
 */
final class SqlInsert extends SqlInstruction {

    /**
     * define o os valores das colunas da entity (table) que será inserida
     * @param $column = nome da coluna
     * @param $value = valor a ser inserido
     */
    public function setRowData($column, $value) {
        //cria um array pelo nome da coluna
        if (is_string($value)) {
            //adiciona \ em aspas
            $value = addslashes($value);

            $this->columnValues[$column] = "'{$value}'";
        } else if (is_bool($value)) {
            $this->columnValues[$column] = $value ? 'TRUE' : 'FALSE';
        } else {
            //caso seja nulo
            $this->columnValues[$column] = "null";
        }
    }
    
    /**
     * Define os valores e os binds das colunas da entity (table) que será inserida com query prepared do PDO
     * @param string $column
     */
    public function setRowPrepared($column) {
        $this->columnValues[$column] = ":".$column;        
    }

    /**
     * inserts não possuem criterio de seleção, então lança uma exception
     * @param $criteria = objeto Criteria
     */
    public function setCriteria(Criteria $criteria) {
        throw new \Exception("Não é possível instânciar um critério de " . __CLASS__);
    }

    /**
     * retorna o SQL em forma de string, método obrigatório
     */
    public function getInstruction() {        
        if(!count($this->columnValues)){
            throw new \Exception('Nenhuma coluna para inserção identificada na instrução!');            
        }
        
        //monta string com os nomes das colunas
        $columns = implode(', ', array_keys($this->columnValues));
        //monta string contendo os valores
        $values = implode(', ', array_values($this->columnValues));
        
        $this->sql = "INSERT INTO {$this->entity} (";
        $this->sql .= $columns . ")";
        $this->sql .= " VALUES ({$values})";

        return $this->sql;
    }
}