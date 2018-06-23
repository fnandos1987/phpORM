<?php
include_once '../Util/ModelUtil.class.php';

/**
 * Classe para montagem e execução de consultas SQL sem Joins
 * @author fernando.schwambach
 */
class Query extends BaseQuery {

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

        $oClass = $this->getPersistence()->instantiateModel();
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            foreach ($this->getPersistence()->getMapping() as $oMap) {
                if (isset($row[$oMap->getNameDb()])) {
                    ModelUtil::getSetter($oClass, $oMap->getNameClass(), array($row[$oMap->getNameDb()]));
                }
            }
        }

        Transaction::close();
        return $oClass;
    }

    /**
     * Retorna todos os registros afetados pelo critério de pesquisa
     * @return \ModelIterator
     */
    public function getAllByQuery() {
        Transaction::open();
        $stmt = Transaction::get()->prepare($this->getSelect());
        foreach ($this->getBind() as $aBind) {
            $stmt->bindValue($aBind[0], $aBind[1], $aBind[2]);
        }
        return new ModelIterator($this->getPersistence(), $stmt);
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
     * Retorna o próximo valor da coluna informada por parâmetro aplicando o critério definido
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
}