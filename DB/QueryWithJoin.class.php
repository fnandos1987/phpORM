<?php
/**
 * Classe para montagem e execução de consultas SQL usando Joins
 * @author fernando.schwambach
 */
class QueryWithJoin extends BaseQuery{

    private $aJoin = Array();

    public function __construct($oPersiste, Array $aColunas = Array()) {
        parent::__construct($oPersiste);
        if (count($aColunas)) {
            $this->addColumn(implode(', ', $this->getPersistence()->getColumns($aColunas)));
        } else {
            $this->addColumn(implode(', ', $this->getPersistence()->getAllColumns()));
        }
    }

    /**
     * Adiciona um join ou mais na consulta montando suas condições
     * @param string $sName
     * @param array $aColumns
     * @param string $sType
     * @throws Exception
     */
    public function addJoin($sName, Array $aColumns = Array(), $sType = JoinMapper::JOIN) {
        $aJoins = explode('.', $sName);
        if (count($aJoins) >= 2) {
            $this->addAllJoins($aJoins, $aColumns, $sType);
        } else {
            if ($oJoin = $this->getPersistence()->getJoinByName($sName)) {
                if (!isset($this->aJoin[$sName])) {                   
                    $this->aJoin[$sName] = $oJoin->getScriptJoin($this->getPersistence(), $sType);
                }
                if (count($aColumns)) {
                    $this->addColumn(implode(', ', $oJoin->getColumns($aColumns)));
                } else {
                    $this->addColumn(implode(', ', $oJoin->getAllColumns()));
                }
            } else {
                throw new Exception('Join ' . $sName . ' não mapeado!');
            }
        }
    }

    /**
     * No caso do join ser informado como Telefone.TipoTelefone por exemplo cria um join para a tabela de telefone com a principal e um join para a tabela tipo_telefone com a tabela telefone e assim vai
     * @param Array $aClass
     * @param Array $aColumns
     * @param string $sType
     * @throws Exception
     */
    private function addAllJoins($aClass, $aColumns, $sType) {
        $Persistence = $this->getPersistence();
        $aNameClass = Array();
        foreach ($aClass as $sClass) {
            if ($oJoin = $Persistence->getJoinByName($sClass)) {
                if (!isset($this->aJoin[$sClass])) {
                    $this->aJoin[$sClass] = $oJoin->getScriptJoin($Persistence, $sType);
                }

                $aNameClass[] = $sClass;
                $class = $oJoin->getPersistence();
                $Persistence = new $class();
            } else {
                throw new Exception('Join ' . $sClass . ' não mapeado!');
            }
        }

        $oJoin->setName(implode('.', $aNameClass));
        if (count($aColumns)) {
            $this->addColumn(implode(', ', $oJoin->getColumns($aColumns)));
        } else {
            $this->addColumn(implode(', ', $oJoin->getAllColumns()));
        }
    }

    protected function getSelect() {
        $oSql = new SqlSelect();
        $oSql->setEntity($this->getPersistence()->getTableName());
        $oSql->addColumn(implode(', ', $this->getColumns()));
        foreach ($this->aJoin as $sJoin) {
            $oSql->addJoinInstruction($sJoin);
        }
        $oSql->setCriteria($this->getCriteria());       
        return $oSql->getInstruction();
    }

    /**
     * Retorna todos os registros afetados pelo critério de pesquisa
     * @return \MappedIterator
     */
    public function getAllFromQuery() {
        Transaction::open();
        $stmt = Transaction::get()->prepare($this->getSelect());
        foreach ($this->getBind() as $aBind) {
            $stmt->bindValue($aBind[0], $aBind[1], $aBind[2]);
        }
        return new MappedIterator($this->getPersistence(), $stmt);
    }

    /**
     * Retorna um objeto Modelo com o registro afetado pelo critério
     * @return Object
     */
    public function getOneFromQuery() {
        Transaction::open();
        $stmt = Transaction::get()->prepare($this->getSelect());
        foreach ($this->getBind() as $aBind) {
            $stmt->bindValue($aBind[0], $aBind[1], $aBind[2]);
        }

        $stmt->execute();
        $oClass = $this->getPersistence()->instantiateModel();

        if ($aResult = $stmt->fetch(PDO::FETCH_ASSOC)) {
            foreach (array_keys($aResult) as $sKey) {
                ModelUtil::getSetter($oClass, $sKey, array($aResult[$sKey]));
            }
        }

        Transaction::close();
        return $oClass;
    }
}