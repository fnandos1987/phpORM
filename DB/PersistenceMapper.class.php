<?php
namespace Db;
/**
 * Classe para mapeamento da persistência no banco de dados: schemas, tabelas, foreing keys
 * @author fernando.schwmbach
 */
class PersistenceMapper {

    /**
     * @var string $schema: esquema do banco de dados se ele suportar
     */
    protected $schema = null;

    /**
     * @var string $tableName: nome da tabela afetada pela persistência 
     */
    protected $tableName;

    /**
     * @var Array $mapping: mapeamentos dos campos da tabela
     */
    protected $mapping = array();

    /**
     * @var Array $join mapeamento de foreing keys
     */
    protected $joins = array();

    /**
     * @var string $Model: configura o modelo vinculado a essa persistência  
     */
    protected $Model;

    public function setTableName($sName, $sSchem = null) {
        $this->tableName = $sName;
        $this->schema = $sSchem;
    }

    public function getTableName($useSchema = true) {
        if (!is_null($this->schema) && $useSchema) {
            return "{$this->schema}.{$this->tableName}";
        }
        return $this->tableName;
    }

    public function getModel() {
        return $this->Model;
    }

    public function instantiateModel() {
        if (!isset($this->Model)) {
            throw new \Exception('Modelo não definido para ' . get_class($this));
        }
        return new $this->Model();
    }

    public function setModel($Model) {
        $this->Model = $Model;
    }

    public function getMapping() {
        return $this->mapping;
    }

    public function getAllJoins() {
        return $this->joins;
    }

    public function getJoinByName($sId) {
        if (isset($this->joins[$sId])) {
            return $this->joins[$sId];
        }
        return false;
    }

    /**
     * Método para o mapeamento ORM
     * @param string $sNameDb nome do campo no banco de dados
     * @param string $sNameClass nome da propriedade na classe de modelo
     * @param string $bKey indica se o campo faz parte da chave primária da tabela
     */
    public function mapColumn($sNameDb, $sNameClass, $bKey = false) {
        $oMap = new ModelMapper($sNameDb, $sNameClass, $bKey);
        $this->mapping[] = $oMap;
    }

    /**
     * Método para mapeamento ORM das chaves estrangeiras
     * @param string $sName nome da classe de origem da chave
     * @param string $sAlias alias da tabela
     * @return JoinMapper $oJoin
     */
    public function mapJoin($sName, $sAlias = false) {
        $oJoin = new JoinMapper($sName, $sAlias);
        $this->joins[$sName] = $oJoin;
        return $oJoin;
    }

    /**
     * Retorna a coluna que corresponde ao nome no banco de dados passado como parâmetro
     * @param string $sColumn
     * @return ModelMapper|false
     */
    public function getColumnByNameDb($sColumn) {
        foreach ($this->mapping as $oModelMapper) {
            if ($oModelMapper->getNameDb() === $sColumn) {
                return $oModelMapper;
            }
        }
        return false;
    }

    /**
     * Retorna a coluna que corresponde ao nome na classe Modelo passado como parâmetro
     * @param string $sColumn
     * @return ModelMapper|false
     */
    public function getColumnByNameClass($sColumn) {
        foreach ($this->mapping as $oModelMapper) {
            if ($oModelMapper->getNameClass() === $sColumn) {
                return $oModelMapper;
            }
        }
        return false;
    }

    /**
     * Retorna todas as colunas da persistência mapeada 
     * @return array
     */
    public function getAllColumns() {
        $sAlias = $this->getTableName(false);
        $aCol = array();

        foreach ($this->getMapping() as $oMap) {
            $aCol[] = "{$sAlias}.{$oMap->getNameDb()} as \"{$oMap->getNameClass()}\"";
        }
        return $aCol;
    }

    /**
     * Retorna o array de colunas transfomado
     * @param Array $aColumns
     * @return Array
     */
    public function getColumns(Array $aColumns) {
        $sAlias = $this->getTableName(false);
        $aCol = array();

        foreach ($aColumns as $sColumn) {
            if ($oMap = $this->getColumnByNameDb($sColumn)) {
                $aCol[] = "{$sAlias}.{$oMap->getNameDb()} as \"{$oMap->getNameClass()}\"";
            }
        }
        return $aCol;
    }

}