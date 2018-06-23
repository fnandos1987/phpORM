<?php

/**
 * Classe base que provê os métodos comuns entre todas as instruções SQL (Select, Insert, Update, Delete)
 */
abstract class SqlInstruction {

    protected $sql; //armazena a instrução sql
    protected $criteria; //armazena o objeto Criteria
    protected $entity; //tabela do BD a ser manipulada
    protected $columnValues = array(); //armazena array de colunas e valores

    /**
     * define o nome da entidade (table) manipula pela instrução sql
     * @param $entity = nome da tabela do banco de dados
     */
    final public function setEntity($entity) {
        $this->entity = $entity;
    }

    /**
     * retorna o nome da entidade (table) manipula pela instrução sql
     */
    final public function getEntity() {
        return $this->entity;
    }

    /**
     * define o criterio de seleção dos dados através de um objeto Criteria
     * @param $criteria = objeto Criteria
     */
    public function setCriteria(Criteria $criteria) {
        $this->criteria = $criteria;
    }

    /**
     * este método é obrigatório para todas as classes filhas de SqlInstruction
     */
    abstract function getInstruction();
}