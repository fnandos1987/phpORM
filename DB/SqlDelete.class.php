<?php
namespace Db;
/**
 * Classe para manipulação de instrucões DELETE no banco de dados
 */
final class SqlDelete extends SqlInstruction {

    /**
     * retorna o SQL em forma de string, método obrigatório
     */
    public function getInstruction() {
        $this->sql = "DELETE FROM {$this->entity}";

        //retorna a clausula WHERE do objeto $this->criteria
        if ($this->criteria) {
            $expression = $this->criteria->dump();

            if ($expression) {
                $this->sql .= " WHERE " . $expression;
            }
        }

        return $this->sql;
    }

}