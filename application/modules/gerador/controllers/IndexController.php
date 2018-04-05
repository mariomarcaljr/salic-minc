<?php

include_once APPLICATION_PATH . '/modules/gerador/models/Gerador.php';

class Gerador_IndexController extends MinC_Controller_Action_Abstract
{
   public function init()
    {
//        xd(123);
//        $auth = Zend_Auth::getInstance(); // instancia da autenticacao
//        $this->grupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
       parent::init();
    }

    public function indexAction()
    {
        $this->view->modulo = $modulo = $this->_getParam('modulo', 'default');

        $mGerador = new Gerador_Model_Gerador();
        $aTabelas = $mGerador->gerar();

        foreach ($aTabelas as $schema => $tabelas) {
            foreach ($tabelas as $nomeTabela => $tabela) {
                $this->view->tabela = $tabela;
                $this->view->schema = $schema;
                $this->view->nomeTabela = $nomeTabela;

                $caminhoModulo = $this->_criarPastas($modulo, $tabela);
                $this->_gerarModel($caminhoModulo);
                $this->_gerarController($caminhoModulo);
                $this->_gerarIndex($caminhoModulo);

                exec("chmod -R 777 $caminhoModulo");
            }
        }

        xd("Fim");
    }

    private function _criarPastas($modulo, $tabela)
    {

        $nomeController = strtolower($this->view->nomeTabela);

        $caminhoModulo = 'application/modules/gerador/arquivos-gerados/' . $modulo;
        if(!is_dir($caminhoModulo)){
            mkdir($caminhoModulo);
        }
        if(!is_dir($caminhoModulo . '/controllers')){
            mkdir($caminhoModulo . '/controllers');
        }
        if(!is_dir($caminhoModulo . '/models')){
            mkdir($caminhoModulo . '/models');
        }
        if(!is_dir($caminhoModulo . '/views')){
            mkdir($caminhoModulo . '/views');
        }
        if(!is_dir($caminhoModulo . '/views/scripts')){
            mkdir($caminhoModulo . '/views/scripts');
        }
        if(!is_dir($caminhoModulo . '/views/scripts/' . $nomeController)){
            mkdir($caminhoModulo . '/views/scripts/' . $nomeController);
        }

        return $caminhoModulo;
    }

    private function _gerarModel($caminhoModulo)
    {
        $model = $this->view->render('index/model.phtml');

        $nomeArquivo = ucfirst($this->view->nomeTabela);
        $caminhoArquivo = $caminhoModulo . '/models/' . $nomeArquivo . ".php";

        @unlink($caminhoArquivo);
        $file = fopen($caminhoArquivo, 'a+');
        $escreve = fwrite($file, $model);
        fclose($file);
    }

    private function _gerarController($caminhoModulo)
    {
        $model = $this->view->render('index/controller.phtml');

        $nomeArquivo = ucfirst($this->view->nomeTabela);
        $caminhoArquivo = $caminhoModulo . '/controllers/' . $nomeArquivo . "Controller.php";

        @unlink($caminhoArquivo);
        $file = fopen($caminhoArquivo, 'a+');
        $escreve = fwrite($file, $model);
        fclose($file);
    }

    private function _gerarIndex($caminhoModulo)
    {
        $model = $this->view->render('index/index.phtml');

        $nomeController = strtolower($this->view->nomeTabela);
        $caminhoArquivo = $caminhoModulo . '/views/scripts/' . $nomeController . '/index.phtml';
        
        @unlink($caminhoArquivo);
        $file = fopen($caminhoArquivo, 'a+');
        $escreve = fwrite($file, $model);
        fclose($file);
    }
}
