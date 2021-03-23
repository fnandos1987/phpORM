<?php
namespace Db;
/**
 * Classe para manipulação de instrucões SELECT simples no banco de dados
 */
final class SqlSelect extends SqlInstruction {

    private $columns = Array(); //colunas a serem retornadas
    private $joins = Array(); //script join adicionados

    /**
     * adiciona uma coluna ser retornada
     * @param $column = nome da coluna
     */

    public function addColumn($column) {
        $this->columns[] = $column;
    }
    
    /**
     * adiciona uma instrução join
     * @param string $sSql
     */
    public function addJoinInstruction($sSql) {
        $this->joins[] = $sSql;
    }

    /**
     * retorna o SQL em forma de string, método obrigatório
     * @return string
     */
    public function getInstruction() {
        $this->sql = 'SELECT ';  
        
        //monta string com o nome das colunas
        if (count($this->columns)) {
            $this->sql .= implode(', ', $this->columns);
        } else {
            $this->sql .= '*';
        }

        $this->sql .= ' FROM ' . $this->entity;
        
        foreach ($this->joins as $sJoin) {
            $this->sql .= ' '.$sJoin;            
        }

        //retorna a clausula WHERE do objeto $this->criteria
        if ($this->criteria) {
            $expression = $this->criteria->dump();

            if ($expression) {
                $this->sql .= ' WHERE ' . $expression;
            }

            //retona comandos adicionais do objeto $this->criteria tipo Order By, Group By, ect...
            $aCommands = $this->criteria->getCommands();

            foreach ($aCommands as $sChave => $command) {
                if ($sChave === 'GROUP' || $sChave === 'ORDER') {
                    $aCrit[$sChave] = $command;
                    unset($aCommands[$sChave]);
                }
            }

            if (isset($aCrit['GROUP'])) {
                $this->sql .= ' GROUP BY ' . implode(', ', $aCrit['GROUP']) . ' ';
            }
            if (isset($aCrit['ORDER'])) {
                $this->sql .= ' ORDER BY ' . implode(', ', $aCrit['ORDER']) . ' ';
            }

            foreach ($aCommands as $sChave => $xValue) {
                $this->sql .= ' '.$sChave . ' ' . $xValue . ' ';
            }
        }

        return $this->sql;
    }
}