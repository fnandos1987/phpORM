<?php
/**
*  Classe abstrata para gerenciamento de expressões
*/
abstract class Expression{
    
  //operadores lógicos
  const AND_OPERATOR = 'AND ';
  const OR_OPERATOR = 'OR ';
  
  //metdodo dump é obrigatório para todos os filhos de Expression
  abstract public function dump();	
}