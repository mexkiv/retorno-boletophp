<?php

/** 
 * Exemplo de uso da classe para processamento de arquivo de retorno de cobranças 
 * em formato FEBRABAN/CNAB400, testado com arquivo de retorno do Banco do Brasil.
 * Este exemplo não utiliza a class RetornoFactory para descobrir o tipo de arquivo
 * de retorno. Aqui assume-se que todos os arquiros a serem lidos são apenas
 * no formato CNAB400 e assim já instancia a classe
 * @see com\manoelcampos\RetornoBoleto\RetornoCNAB400 diretamente.
 * 
 * @license <a href="https://opensource.org/licenses/MIT">MIT License</a>
 * @author <a href="http://manoelcampos.com/contact">Manoel Campos da Silva Filho</a>
 * @version 1.0
 */

require_once("../vendor/autoload.php");

use ManoelCampos\RetornoBoleto\LeituraArquivo;
use ManoelCampos\RetornoBoleto\RetornoCNAB400;
use ManoelCampos\RetornoBoleto\RetornoInterface;
use ManoelCampos\RetornoBoleto\LinhaArquivo;


/**
 * Função de callback que será chamada cada vez que uma linha for lida do
 * arquivo de retorno. Esta versão da função de callback imprime campos
 * específicos de cada linha lida.
 * 
 * @param RetornoInterface $retorno Objeto responsável pela leitura do arquivo de retorno
 * @param LinhaArquivo $linha Objeto contendo os dados da linha lida
 */
$processarLinha1 = function (RetornoInterface $retorno, LinhaArquivo $linha) {
    if($linha->dados["registro"] == $retorno->getIdHeaderArquivo()){
        echo "<b>Tipo de Arquivo de Retorno: " . get_class($retorno) . "</b><p/>";
        echo "<table>\n";
        echo "<tr><th>Linha</th><th>Nosso Número</th><th>Data Pag</th>".
             "<th>Valor Título</th><th>Valor Pago</th></tr>\n";
    }
    else if($linha->dados["registro"] == $retorno->getIdTrailerArquivo()){
        echo "</table>\n";
    }
    else if($linha->dados["registro"] == $retorno->getIdDetalhe()){ 
        printf(
            "<tr><td>%d</td><td>%d</td><td>%s</td><td>%.2f</td><td>%.2f</td></tr>\n",
            $linha->numero, 
            $linha->dados['nosso_numero'], 
            $linha->dados["data_pagamento"],
            $linha->dados["valor_titulo"],
            $linha->dados["valor_pagamento"]);

        echo "</tr>\n";
    }
};

/**
 * Função de callback que será chamada cada vez que uma linha for lida do
 * arquivo de retorno. Esta versão da função de callback imprime todos os campos
 * da linha lida.
 * 
 * @param RetornoInterface $retorno Objeto responsável pela leitura do arquivo de retorno
 * @param LinhaArquivo $linha Objeto contendo os dados da linha lida
 */
$processarLinha2 = function (RetornoInterface $retorno, LinhaArquivo $linha) {
    if($linha->dados["registro"] == $retorno->getIdHeaderArquivo()){
        echo "<b>Tipo de Arquivo de Retorno: " . get_class($retorno) . "</b><p/>";
        echo "<table>\n";
    }
    else if($linha->dados["registro"] == $retorno->getIdTrailerArquivo()){
        echo "</table>\n";
    }
    else {
        printf("<tr><th colspan='2'>Número da Linha: %08d</th></tr>\n", $linha->numero);
        foreach ($linha->dados as $nome_campo => $valor_campo){
            printf("<tr><td><b>%s</b></td><td>%s</td>\n ", $nome_campo, $valor_campo);
        }
        echo "</tr>\n";
    }
};

//--------------------------INÍCIO DA EXECUÇÃO DO CÓDIGO------------------------
$fileName = "retornos/retorno-cnab400-bb.ret";
/*
 * Assumindo-se que o arquivo de retorno é apenas no padrão CNAB400, 
 * instancia diretamente um objeto da class RetornoCNAB400
 * para ler o arquivo, no lugar de usar a class RetornoFactory
 * para descobrir qual o tipo de arquivo e instanciar
 * o objeto adequado para processar o mesmo.
 */
$cnab400 = new RetornoCNAB400($fileName);

/*
 Use uma das duas instrucões abaixo para usar uma das duas funções
 de callback definidas acima (comente uma e descomente a outra).
*/
$leitura = new LeituraArquivo($processarLinha1, $cnab400);
//$leitura = new LeituraArquivo($processarLinha2, $cnab400);

$leitura->lerArquivoRetorno();
