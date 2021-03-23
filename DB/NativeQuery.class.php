<?php
namespace Db;
/**
 * Classe para montagem e execução de consultas SQL sem o mapeamento ORM de uma persistência
 * @author fernando.schwambach
 */
class NativeQuery extends BaseQuery {

    private $tableName;
    private $aJoins = Array();
    private $aColumns = Array();

    public function __construct($sTableName) {        
        parent::__construct(null);
        $this->tableName = $sTableName;
    }

    /**
     * Adiciona uma coluna na consulta, sendo possível definir um alias para a coluna
     * @param string $sColumn
     * @param mixed $sAliasColumn
     */
    public function addColumn($sColumn, $sAliasColumn = false) {
        if ($sAliasColumn) {
            $this->aColumns[$sAliasColumn] = $sColumn . ' as "' . $sAliasColumn . '"';
        } else {
            $this->aColumns[$sColumn] = $sColumn;
        }
    }

    public function addJoinScript($sSql) {
        $this->aJoins[] = $sSql;
    }


    protected function getSelect() {
        $oSql = new SqlSelect();
        $oSql->setEntity($this->tableName);
        $oSql->addColumn(implode(', ', $this->getColumns()));
        foreach ($this->aJoins as $sJoin) {
            $oSql->addJoinInstruction($sJoin);
        }
        $oSql->setCriteria($this->getCriteria());
        return $oSql->getInstruction();
    }
    
    public function getColumns() {
        return $this->aColumns;
    }

    /**
     * Retorna um objeto Modelo com o registro afetado pelo critério
     * @return Object
     */
    public function getOneByQuery() {
        Transaction::open();
        $stmt = Transaction::get()->prepare($this->getSelect());
        foreach ($this->getBind() as $aBind) {
            $stmt->bindValue($aBind[0], $aBind[1], $aBind[2]);
        }

        $stmt->execute();
        $oClass = false;

        if ($aRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $oClass = $this->createObject($aRow);
        }

        Transaction::close();
        return ($oClass) ? $oClass : $this->createEmptyObject();
    }

    /**
     * Retorna todos os registros afetados pelo critério de pesquisa
     * @return \ObjectIterator
     */
    public function getAllByQuery() {
        Transaction::open();
        $stmt = Transaction::get()->prepare($this->getSelect());        
        foreach ($this->getBind() as $aBind) {
            $stmt->bindValue($aBind[0], $aBind[1], $aBind[2]);
        }
        return new ObjectIterator($stmt);
    }

    /**
     * Executa um COUNT(*) na tabela aplicando o critério definido
     * @return int
     */
    public function getCount() {
        Transaction::open();
        $this->addColumn('count(*) as "total"');
        $stmt = Transaction::get()->prepare($this->getSelect());
        foreach ($this->getBind() as $aBind) {
            $stmt->bindValue($aBind[0], $aBind[1], $aBind[2]);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_OBJ);
        Transaction::close();
        return $row->total;
    }

    /**
     * Retorna o próximo valor da coluna informada por parâmetro
     * @param string $sCodigo
     * @return int
     */
    public function getNext($sCodigo) {
        Transaction::open();
        $this->addColumn('max(' . $sCodigo . ') + 1 as "next"');
        $stmt = Transaction::get()->prepare($this->getSelect());
        foreach ($this->getBind() as $aBind) {
            $stmt->bindValue($aBind[0], $aBind[1], $aBind[2]);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_OBJ);
        Transaction::close();
        return isset($row->next)? $row->next: 1;
    }

    /**
     * Cria um objeto com os valores retornados pela query com propriedades no padrão Camel Case
     * @param array $aRow
     * @return \stdClass
     */
    private function createObject($aRow) {
        $oClass = new stdClass();
        foreach ($aRow as $key => $value) {
            $sProperty = $this->toCamelCase($key);
            $oClass->$sProperty = $value;
        }
        return $oClass;
    }

    /**
     * Cria um objeto vazio com propriedades no padrão Camel Case
     * @return \stdClass
     */
    private function createEmptyObject() {
        $oEmpty = new stdClass();
        foreach (array_keys($this->getColumns()) as $sColumn) {
            $sColumn = $this->toCamelCase($sColumn);
            $oEmpty->$sColumn = null;
        }
        return $oEmpty;
    }

    /**
     * Converte a string para o padrão Camel Case
     * @param string $sField
     * @return string
     */
    private function toCamelCase($sField) {
        $aField = explode('_', $sField);
        $saida = Array();
        foreach ($aField as $key => $value) {
            $saida[] = ($key > 0) ? ucfirst($value) : $value;
        }
        return implode('', $saida);
    }

}