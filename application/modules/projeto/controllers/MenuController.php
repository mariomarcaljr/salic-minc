<?php
class Projeto_MenuController extends Projeto_GenericController
{
    public function init()
    {
        $auth = Zend_Auth::getInstance(); // pega a autenticacao
        $idPreProjeto = $this->getRequest()->getParam('idPreProjeto');

        $arrIdentity = array_change_key_case((array) Zend_Auth::getInstance()->getIdentity());
        $GrupoAtivo   = new Zend_Session_Namespace('GrupoAtivo');

        $cpf = isset($arrIdentity['usu_codigo']) ? $arrIdentity['usu_identificacao'] : $arrIdentity['cpf'];

        // Busca na SGCAcesso
        $modelSgcAcesso 	 = new Autenticacao_Model_Sgcacesso();
        $arrAcesso = $modelSgcAcesso->findBy(array('cpf' => $cpf));

        // Busca na Usuarios
        //Excluir ProposteExcluir Proposto
        $usuarioDAO   = new Autenticacao_Model_Usuario();
        $arrUsuario = $usuarioDAO->findBy(array('usu_identificacao' => $cpf));

        // Busca na Agentes
        $tableAgentes  = new Agente_Model_DbTable_Agentes();
        $arrAgente = $tableAgentes->findBy(array('cnpjcpf' => trim($cpf)));

        if ($arrAcesso) {
            $this->idResponsavel = $arrAcesso['idusuario'];
        }
        if ($arrAgente) {
            $this->idAgente 	  = $arrAgente['idagente'];
        }
        if ($arrUsuario) {
            $this->idUsuario     = $arrUsuario['usu_codigo'];
        }
        if ($this->idAgente != 0) {
            $this->usuarioProponente = "S";
        }
        $this->cpfLogado = $cpf;
        parent::init();
    }

    public function indexAction()
    {
        $menu = [
            ['label' => 'Inicio', 'title' => 'Inicio', 'uri' => '/', 'ico' => false, 'sub' => false, 'ajax' => false],
            ['label' => 'Proponente', 'title' => 'Inicio', 'uri' => '/consultardadosprojeto/dados-proponente', 'ico' => false, 'sub' => false, 'ajax' => true],
            ['label' => '', 'title' => '', 'uri' => false, 'ico' => false,
                'sub' => [
                    [
                        'label' => 'Outras Informações',
                        'title' => '',
                        'uri' => false,
                        'ico' => false,
                        'ajax' => false,
                        'header' =>true 
                    ],
                    [
                        'label' => 'Dados Complementares',
                        'title' => 'Dados Complementares',
                        'uri' => $this->view->url(
                            [
                                'module' => 'default',
                                'controller' => 'consultardadosprojeto',
                                'action' => 'dados-complementares']
                            ),
                        'ico' => false,
                        'ajax' => true
                    ]
                ]
            ]
        ];

        $this->_helper->json($menu);
    }
}
