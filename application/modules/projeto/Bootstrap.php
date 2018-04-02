<?php
class Projeto_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initPath()
    {
        require_once APPLICATION_PATH . '/modules/assinatura/controllers/GenericController.php';
        require_once APPLICATION_PATH . '/modules/projeto/controllers/GenericController.php';
    }
}
