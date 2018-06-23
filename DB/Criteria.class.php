<?php

/**
 *  Classe que provê uma interface para criterios de seleção
 */
class Criteria extends Expression {

    private $expressions = Array(); //armazena um array de expressões
    private $operators = Array(); //armazena um array de operadores
    private $commands = Array(); //armazena comandos ORDER BY, GROUP BY, etc...

    /**
     * Adiciona expressões ao criterio
     * @param $expression = expressão (objeto Expression)
     * @param $operator = operador lógico de comparação
     */

    public function add(Expression $expression, $operator = self::AND_OPERATOR) {
        //retira o operador lógico na primeira adição
        if (empty($this->expressions)) {
            $operator = '';
        }

        //incrementa arrays de expressões
        $this->expressions[] = $expression;
        $this->operators[] = $operator;
    }

    /**
     * Adiciona colunas para a cláusula ORDER BY
     * @param string $sColumn
     */
    public function addOrderBy($sColumn) {
        $this->commands['ORDER'][] = $sColumn;
    }

    /**
     * Adiciona colunas para a cláusula GROUP BY
     * @param string $sColumn
     */
    public function addGroupBy($sColumn) {
        $this->commands['GROUP'][] = $sColumn;
    }

    /**
     * Método para adicionar outros comandos, podem ser comandos específicos do banco de dados tipo LIMIT, OFFSET ou genéricos como HAVING por exemplo
     * @param string $sName
     * @param mixed $xValue
     */
    public function addOtherCommand($sName, $xValue) {
        $this->commands[$sName] = $xValue;
    }

    /**
     * Retonar os camandos adicionais do objeto Criteria
     * @return array
     */
    public function getCommands() {
        return $this->commands;
    }

    /**
     *  retorna a expressão final
     */
    public function dump() {
        $result = array();
        if (count($this->expressions)) {
            foreach ($this->expressions as $i => $expression) {
                $operator = $this->operators[$i];
                //concatena o operador com a expressão
                $result[] = $operator . $expression->dump();
            }
            return "(" . implode(' ', $result) . ")";
        }
        return false;
    }

}
