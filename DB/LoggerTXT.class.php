<?php
namespace Db;
/**
 *  implementa o algoritmo de LOG em arquivos .txt
 */
class LoggerTXT extends Logger {

    /**
     * escreve a mensagem do log no arquivo
     * @param $message = mensagem a ser escrita
     */
    public function write($message) {
        $time = date("Y-m-d H:i:s");

        //monta a string
        $text = "{$time}: {$message}\n";

        //adiciona no fim do arquivo
        file_put_contents($this->filename, $text, FILE_APPEND);
    }

}