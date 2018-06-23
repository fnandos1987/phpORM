<?php

/**
 *  Classe que provê recursos para definição de algoritmos de LOG
 */
abstract class Logger {

    protected $filename; //local onde está o arquivo de log

    /**
     *  método construtor instanciando um log
     * @param $filename = local do arquivo
     */

    public function __construct($filename) {
        $this->filename = $filename;
    }

    //metodo write é obrigatório para os filhos de Logger
    abstract function write($message);
}