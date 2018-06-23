<?php

/**
 *  Classe que provê uma interface para filtros de seleção
 */
class Filter extends Expression {

    const IGUAL = '=';
    const DIFERENTE = '<>';
    const MAIOR_QUE = '>';
    const MAIOR_IGUAL_QUE = '>=';
    const MENOR_QUE = '<';
    const MENOR_IGUAL_QUE = '<=';
    const BETWEEN = 'BETWEEN';
    const IN = 'IN';
    const NOT_IN = 'NOT IN';
    const LIKE = 'LIKE';
    const ILIKE = 'ILIKE';
    const IS_NULL = 'IS NULL';
    const IS_NOT_NULL = 'IS NOT NULL';

    private $variable; //variável
    private $operator; //operador
    private $value; //valor um
    private $valueTwo; //valor dois

    /**
     * Construtor instanciando um novo filtro
     * @param $variable = variável
     * @param $operator = operador (>,<,=,in,not in,...)
     * @param $value = valor a ser comparado
     * @param $valueTwo = segundo valor a ser comparado
     * @param bool $prepared = indica se é uma condição de prepared statement, não aplica a função transform
     */

    public function __construct($variable, $operator, $value = false, $valueTwo = false, $prepared = false) {
        $this->variable = $variable;
        $this->operator = $operator;

        if (!$prepared) {
            //transforma o valor de acordo com certas regras
            $this->value = $this->transform($value);
            $this->valueTwo = $this->transform($valueTwo);
        } else {
            //não transformamos condições preparadas
            $this->value = $value;
            $this->valueTwo = $valueTwo;
        }
    }

    /**
     * recebe um valor e faz as modificações necessárias para ele ser interpretado pelo banco de dados
     * @param $value = valor a ser transformado
     */
    public function transform($value) {
        //se for um comando SQL select
        if (!is_array($value) && preg_match('/^SELECT\s+(?:ALL\s+|DISTINCT\s+)?(?:.*?)\s+FROM\s+(.*)$/i', $value)) {
            return $value;
        }
        //se for um array
        if (is_array($value)) {
            //percorre os valores
            foreach ($value as $x) {
                //se for integer
                if (is_numeric($x)) {
                    $foo[] = $x;
                } else if (is_string($x)) {
                    //se for string
                    $foo[] = "'$x'";
                }
            }
            $resul = implode(",", $foo);
        } else if (is_string($value)) {
            //adiciona aspas      
            $resul = "'$value'";
        } else if (is_null($value)) {
            //caso seja nulo
            $resul = 'NULL';
        } else if (is_bool($value)) {
            //caso seja booleano armazena true ou false
            $resul = $value ? 'TRUE' : 'FALSE';
        } else {
            $resul = $value;
        }

        return $resul;
    }

    /**
     * retorna em forma de expressão
     */
    public function dump() {
        //concatena a expressão
        switch ($this->operator) {
            case self::BETWEEN:
                return "{$this->variable} {$this->operator} {$this->value} AND {$this->valueTwo}";
            case self::IS_NULL:
            case self::IS_NOT_NULL:
                return "{$this->variable} {$this->operator}"; 
            case self::IN:
            case self::NOT_IN:
                return "{$this->variable} {$this->operator} ({$this->value})";
            case self::LIKE:
            case self::ILIKE:
                return "{$this->variable} {$this->operator} '{$this->value}%'";
            default:
                return "{$this->variable} {$this->operator} {$this->value}";
        }
    }
}
