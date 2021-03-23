<?php
namespace Db;
use PDOStatement, Util\QueryIterator, Util\ModelUtil;

/**
 * Classe interadora sobre consultas mapeadas (ex.: cid_codigo as "cidCodigo")
 * @author fernando.schwambach
 */
class MappedIterator extends QueryIterator {

    private $Persistence;

    public function __construct(PersistenceMapper $Pers, PDOStatement $stmt) {
        $this->Persistence = $Pers;
        parent::__construct($stmt);
    }

    protected function fetchObject() {
        if ($aResult = $this->Query->fetch(PDO::FETCH_ASSOC)) {
            $oClass = $this->Persistence->instantiateModel();
            foreach (array_keys($aResult) as $sKey) {
                 ModelUtil::getSetter($oClass, $sKey, array($aResult[$sKey]));
            }
            return $oClass;
        }
        return false;
    }
}