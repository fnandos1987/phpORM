<?php

/**
 * Classe para manipulação de instrucões UPDATE no banco de dados
 */
final class SqlUpdate extends SqlInstruction {

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
        } else if (isset($value)) {
            //caso seja outro tipo de dado
            $this->columnValues[$column] = $value;
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
     * retorna o SQL em forma de string, método obrigatório
     */
    public function getInstruction() {
        $this->sql = "UPDATE {$this->entity}";

        //monta os pares coluna = valor
        $set = array();
        if ($this->columnValues) {
            foreach ($this->columnValues as $column => $value) {
                $set[] = "{$column} = {$value}";
            }
        }
        
        if(!count($set)){
            throw new Exception('Nenhuma coluna para alteração identificada na instrução!');            
        }
        
        $this->sql .= " SET " . implode(', ', $set);

        //retorna a clausula WHERE do objeto $this->criteria
        if ($this->criteria) {
            $this->sql .= " WHERE " . $this->criteria->dump();
        }

        return $this->sql;
    }

}