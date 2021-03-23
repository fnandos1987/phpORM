<?php
namespace Db;
/**
 * Classe para mapeamento de foreing keys
 * @author fernando.schwmbach
 */
class JoinMapper {

    const JOIN = 'JOIN';
    const LEFT_JOIN = 'LEFT JOIN';
    const RIGHT_JOIN = 'RIGHT JOIN';
    const CROSS_JOIN = 'CROSS JOIN';

    private $name;
    private $persistenceKey; //persistência de onde parte a chave
    private $aliasJoin; //alias da tabela no join
    private $fields = array(); // em caso de não poder ser join natural mapeia as colunas
    private $aAdditionalConditions = array(); //armazena condições com valores fixos

    public function __construct($sName, $sPersistenceKey, $sAlias = false) {
        $this->name = $sName;
        $this->persistenceKey = $sPersistenceKey;
        $this->aliasJoin = $sAlias;
    }

    /**
     * Seta no nome identificador do Join
     * @param string $param
     */
    public function setName($param) {
        $this->name = $param;
    }

    /**
     * Método que mapeia as chaves caso os nomes entre origem e destino da foreing key sejam diferentes, caso sejam iguais será feito o "join natural" chave a chave
     * @param string $sDes nome da coluna na tabela que recebe a chave
     * @param string $sOri nome da coluna na tabela que fornece a chave
     */
    public function mapKey($sDes, $sOri) {
        $this->fields[$sDes] = $sOri;
    }

    /**
     * Adiciona condições com valores fixos no join
     * @param string $sField
     * @param string $sValue
     * @param string $sOperator
     */
    public function addFixedCondition($sField, $sValue, $sOperator = Filter::IGUAL) {
        $this->aAdditionalConditionsValues[] = (object) Array('field' => $sField, 'operator' => $sOperator, 'value' => $sValue);
    }

    /**
     * Retorna todas as colunas da persistência mapeada como Join considerando o alias configurado 
     * @return array
     */
    public function getAllColumns() {
        $this->persistenceKey = $this->instantiatePersistence();
        $sAlias = ($this->aliasJoin) ? $this->aliasJoin : $this->persistenceKey->getTableName(false);
        $aCol = array();

        foreach ($this->persistenceKey->getMapping() as $oMap) {
            $aCol[] = "{$sAlias}.{$oMap->getNameDb()} as \"{$this->name}.{$oMap->getNameClass()}\"";
        }
        return $aCol;
    }

    /**
     * Retorna o array de colunas transfomado
     * @param Array $aColumns
     * @return Array
     */
    public function getColumns(Array $aColumns) {
        $this->persistenceKey = $this->instantiatePersistence();
        $sAlias = ($this->aliasJoin) ? $this->aliasJoin : $this->persistenceKey->getTableName(false);
        $aCol = array();

        foreach ($aColumns as $sColumn) {
            if ($oMap = $this->persistenceKey->getColumnByNameDb($sColumn)) {
                $aCol[] = "{$sAlias}.{$oMap->getNameDb()} as \"{$this->name}.{$oMap->getNameClass()}\"";
            }
        }
        return $aCol;
    }

    /**
     * Retorna o script SQL do Join
     * @param PersistenceMapper $oAtual
     * @param string $sTypeJoin
     * @return string
     */
    public function getScriptJoin(PersistenceMapper $oAtual, $sTypeJoin = self::LEFT_JOIN) {
        $this->persistenceKey = $this->instantiatePersistence();
        $this->persistenceReference = $oAtual;
        $oJoin = new JoinBuilder($this->persistenceKey->getTableName(false), $this->persistenceReference->getTableName(false), $this->aliasJoin, $sTypeJoin);
        
        if (!count($this->fields)) {
            foreach ($this->persistenceKey->getMapping() as $oMap) {
                if ($oMap->getTableKey()) {
                    $oJoin->addKeyColumns($oMap->getNameDb(), $oMap->getNameDb());
                }
            }
        } else {
            foreach ($this->fields as $sKey => $sColumn) {
                $oJoin->addKeyColumns($sKey, $sColumn);
            }
        }
        
        if (count($this->aAdditionalConditions)) {
            foreach ($this->aAdditionalConditions as $oCondiction) {
                if ($this->persistenceReference->getColumnByNameDb($oCondiction->field)) {
                    $oJoin->addFixedValues($oCondiction->field, $oCondiction->value, $oCondiction->operator);
                }
            }
        }
        
        return $oJoin->dump();
    }

    /**
     * Instância a persistencia origem e retorna a instância
     * @return Persistence
     */
    private function instantiatePersistence() {
        if (!($this->persistenceKey instanceof PersistenceMapper)) {
            $this->persistenceKey = new $this->persistenceKey();
        }
        return $this->persistenceKey;
    }

}