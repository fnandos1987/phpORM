<?php
namespace Db;
use PDOStatement, Util\ModelUtil, Util\QueryIterator;
/**
 * Classe interadora de modelos
 * @author fernando.schwmbach
 */
class ModelIterator extends QueryIterator {
    
    private $Persistence;
    
    public function __construct(PersistenceMapper $Pers, PDOStatement $stmt) {
        $this->Persistence = $Pers;
        parent::__construct($stmt);
    }
    
    protected function fetchObject() {
        if($aResult = $this->Query->fetch(PDO::FETCH_ASSOC)){
            $oClass = $this->Persistence->instantiateModel();
            foreach ($this->Persistence->getMapping() as $oMap) {
                if(isset($aResult[$oMap->getNameDb()])){
                    ModelUtil::getSetter($oClass, $oMap->getNameClass(), array($aResult[$oMap->getNameDb()]));                                
                }
            }   
            return $oClass;            
        }     
        return false;
    }

}