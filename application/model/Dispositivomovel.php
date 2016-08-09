<?php

/**
 * Abstra��o da tabela SAC.DBo.tbAplicativoSalic
 *
 * @author rafael.gloria@cultura.gov.br
 */
class Dispositivomovel extends GenericModel{

    protected $_name = 'tbDispositivoMovel';
    protected $_schema = 'dbo';
    protected $_banco = 'SAC';
    
    /**
     * Salva o dispositivo que est� conectado, salva o CPF do usu�rio e atualiza a �ltima data de acesso.
     * 
     * @param string $registrationId
     * @param string $cpf Optional
     * @return array $dispositivo Dados do dispositivo
     */
    public function salvar($registrationId, $cpf = NULL){
        $dispositivo = array();
        
        if(!empty($registrationId)){
            $dispositivoRow = $this->fetchRow("idRegistration = '{$registrationId}'");
            if(!$dispositivoRow){
                $dispositivoRow = $this->createRow(array(
                    'idRegistration' => $registrationId
                ));
            }
            
            if($cpf){
                $dispositivoRow->nrCPF = $cpf;
            }
            $dispositivoRow->dtAcesso = new Zend_Db_Expr('getdate()');
            $dispositivoRow->save();
            $dispositivo = $dispositivoRow->toArray();
        }
        
        return $dispositivo;
    }

    /**
     * Lista todos os dispositivos por idPronac.
     * 
     * @param integer $idPronac
     * @return array
     */
    public function listarPorIdPronac($idPronac){
        $consulta = $this->select();
        $consulta->setIntegrityCheck(false);
        $consulta
            ->from(array('projetos' => 'vwAgentesSeusProjetos'), array(), 'SAC.dbo')
            ->join(array('usuario' => 'SGCacesso'), 'projetos.IdUsuario = usuario.IdUsuario', array(
                'cpf' => 'Cpf'), 'ControleDeAcesso.dbo')
            ->join(array('dispositivo' => 'tbDispositivoMovel'), 'usuario.Cpf = dispositivo.nrCPF', array(
                'idRegistration'), 'SAC.dbo')
            ->group(array(
                'cpf',
                'idRegistration'))
        ;
        
        return $this->fetchAll($consulta);
    }
    
}
