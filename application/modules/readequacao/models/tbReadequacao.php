<?php

/**
 * DAO tbReadequacao
 * @author jeffersonassilva@gmail.com - XTI
 * @since 19/11/2013
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright � 2011 - Minist�rio da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */
class Readequacao_Model_tbReadequacao extends MinC_Db_Table_Abstract
{
    protected $_schema = "sac";
    protected $_name = "tbReadequacao";
    protected $_primary = "idReadequacao";

    const TIPO_READEQUACAO_REMANEJAMENTO_PARCIAL = 1;
    const TIPO_READEQUACAO_PLANILHA_ORCAMENTARIA = 2;
    const TIPO_READEQUACAO_RAZAO_SOCIAL = 3;
    const TIPO_READEQUACAO_AGENCIA_BANCARIA = 4;
    const TIPO_READEQUACAO_SINOPSE_OBRA = 5;
    const TIPO_READEQUACAO_IMPACTO_AMBIENTAL = 6;
    const TIPO_READEQUACAO_ESPECIFICACAO_TECNICA = 7;
    const TIPO_READEQUACAO_ESTRATEGIA_EXECUCAO = 8;
    const TIPO_READEQUACAO_LOCAL_REALIZACAO = 9;
    const TIPO_READEQUACAO_ALTERACAO_PROPONENTE = 10;
    const TIPO_READEQUACAO_PLANO_DISTRIBUICAO = 11;
    const TIPO_READEQUACAO_NOME_PROJETO = 12;
    const TIPO_READEQUACAO_PERIODO_EXECUCAO = 13;
    const TIPO_READEQUACAO_PLANO_DIVULGACAO = 14;
    const TIPO_READEQUACAO_RESUMO_PROJETO = 15;
    const TIPO_READEQUACAO_OBJETIVOS = 16;
    const TIPO_READEQUACAO_JUSTIFICATIVA = 17;
    const TIPO_READEQUACAO_ACESSIBILIDADE = 18;
    const TIPO_READEQUACAO_DEMOCRATIZACAO_ACESSO = 19;
    const TIPO_READEQUACAO_ETAPAS_TRABALHO = 20;
    const TIPO_READEQUACAO_FICHA_TECNICA = 21;
    
    const PERCENTUAL_REMANEJAMENTO = 50;
    const ST_ESTADO_EM_ANDAMENTO = 0;
    const ST_ESTADO_FINALIZADO = 1;
    
    /**
     * @param array $dados
     * @param integer $where
     * @return integer (quantidade de registros alterados)
     */
    public function alterarDados($dados, $where)
    {
        $where = "idRecurso = " . $where;
        return $this->update($dados, $where);
    }

    public function readequacoesCadastradasProponente($where = array(), $order = array())
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("a.idReadequacao, a.idPronac, a.dtSolicitacao, CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao, CAST(a.dsJustificativa AS TEXT) AS dsJustificativa, b.dsReadequacao, c.idArquivo, d.nmArquivo, a.idTipoReadequacao"),
            )
        );

        $select->joinInner(
            array('b' => 'tbTipoReadequacao'),
            'a.idTipoReadequacao = b.idTipoReadequacao',
            array(''),
            'SAC.dbo'
        );
        $select->joinLeft(
            array('c' => 'tbDocumento'),
            'a.idDocumento = c.idDocumento',
            array(''),
            'BDCORPORATIVO.scCorp'
        );
        $select->joinLeft(
            array('d' => 'tbArquivo'),
            'c.idArquivo = d.idArquivo',
            array(''),
            'BDCORPORATIVO.scCorp'
        );

        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        $select->order($order);

        return $this->fetchAll($select);
    }

    /**
     * @param bool $where
     * @param bool $order
     * @param mixed $tamanho
     * @param mixed $inicio
     * @param bool $qtdeTotal
     * @param bool $filtro
     * @return void
     */
    public function painelReadequacoes($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false, $filtro = null)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = array();
        $result = array();
        $total = array();

        switch ($filtro) {
            case '':
                $select = $this->selectView('vwPainelCoordenadorReadequacaoAguardandoAnalise');
                break;
            case 'encaminhados':
                $select = $this->selectView('vwPainelCoordenadorReadequacaoEmAnalise');
                break;
            case 'analisados':
                $select = $this->selectView('vwPainelCoordenadorReadequacaoAnalisados');
                break;
        }

        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        $select->order($order);

        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }

        $stmt = $db->query($select);

        while ($o = $stmt->fetchObject()) {
            $result[] = $o;
        }

        return $result;
    }
    
    
    
    /**
     * painelReadequacoesCoordenadorAcompanhamento
     *
     * @param bool $where
     * @param bool $order
     * @param mixed $tamanho
     * @param mixed $inicio
     * @param bool $qtdeTotal
     * @param bool $filtro
     * @access public
     * @return void
     */
    public function painelReadequacoesCoordenadorAcompanhamento($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false, $filtro = null)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select= array();
        $result= array();
        $total= array();
        
        switch ($filtro) {
            case 'aguardando_distribuicao':
                $select = $this->selectView('vwPainelCoordenadorReadequacaoAguardandoAnalise');
                break;
            case 'em_analise':
                $select = $this->selectView('vwPainelCoordenadorReadequacaoEmAnalise');
                break;
            case 'analisados':
                $select = $this->selectView('vwPainelCoordenadorReadequacaoAnalisados');
                break;
            case 'aguardando_publicacao':
                $select = $this->selectView('vwPainelReadequacaoAguardandoPublicacao');
                break;
        }
        
        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);

        //paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }
        
        $stmt = $db->query($select);

        while ($o = $stmt->fetchObject()) {
            $result[] = $o;
        }
        
        return $result;
    }

    /**
     * @param string $vw
     * @return Zend_Db_Select
     */
    private function selectView($vw)
    {
        $db = Zend_Db_Table::getDefaultAdapter();

        $select = $db->select()->from(
            array('a' => $vw),
            array('*'),
            $this->_schema
        );

        return $select;
    }

    /**
     * count - retorna quantidade de linhas de uma tabela.
     *
     * @param string $table
     * @access public
     * @return int
     */
    public function count($table, $where)
    {
        $db = Zend_Db_Table::getDefaultAdapter();

        $select = $db->select()->from(
            array('a' => $table),
            array('*'),
            $this->_schema
        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        $result = $db->query($select)->fetchAll();
        return count($result);
    }

    /*
     * Busca os dados da readequacao com os campos de VARCHAR(MAX) convertidos e completos.
     */
    public function buscarReadequacao($idReadequacao)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array(
                'idReadequacao',
                'idPronac',
                'idTipoReadequacao',
                'dtSolicitacao',
                'idSolicitante',
                new Zend_Db_Expr('CAST(a.dsJustificativa AS TEXT) AS dsJustificativa'),
                new Zend_Db_Expr('CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao'),
                'idDocumento',
                'idAvaliador',
                'dtAvaliador',
                new Zend_Db_Expr('CAST(a.dsAvaliacao AS TEXT) AS dsAvaliacao'),
                'stAtendimento',
                'siEncaminhamento',
                'stAnalise',
                'idNrReuniao',
                'stEstado',
            )
        );

        $select->where('idReadequacao = ?', $idReadequacao);

        
        return $this->fetchAll($select);
    }


    /**
     * Método para buscar id da readequacao ativa
     * @access public
     * @param integer $idPronac
     * @param integer $idTipoReadequacao
     * @return integer
     */    
    public function buscarIdReadequacaoAtiva($idPronac, $idTipoReadequacao)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            'a.idReadequacao');
        
        $select->where('a.stEstado = ?', self::ST_ESTADO_EM_ANDAMENTO);
        $select->where('a.idPronac = ?' , $idPronac);
        $select->where('siEncaminhamento <> ?', Readequacao_Model_tbTipoEncaminhamento::SI_ENCAMINHAMENTO_FINALIZADA_SEM_PORTARIA);
        $select->where('a.idTipoReadequacao = ?', $idTipoReadequacao);
        
        $result = $this->fetchAll($select);
        
        if (count($result)) {
            return $result[0]['idReadequacao'];
        } else {
            return false;
        }
    }
    
    /*
     * Alterada em 06/03/14
     * @author: Jefferson Alessandro
     */
    public function buscarDadosReadequacoes($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.idReadequacao,
                a.idPronac,
                a.dtSolicitacao,
                CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao,
                CAST(a.dsJustificativa AS TEXT) AS dsJustificativa,
                a.idSolicitante,
                a.idAvaliador,
                a.dtAvaliador,
                CAST(a.dsAvaliacao AS TEXT) AS dsAvaliacao,
                a.idTipoReadequacao,
                CAST(c.dsReadequacao AS TEXT) AS dsReadequacao,
                a.stAtendimento,
                a.siEncaminhamento,
                CAST(b.dsEncaminhamento AS TEXT) AS dsEncaminhamento,
                a.stEstado,
                e.idArquivo,
                e.nmArquivo,
                a.dtEnvio
            ")
        );
        $select->joinInner(
            array('b' => 'tbTipoEncaminhamento'),
            'a.siEncaminhamento = b.idTipoEncaminhamento',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbTipoReadequacao'),
            'c.idTipoReadequacao = a.idTipoReadequacao',
            array(''),
            'SAC.dbo'
        );
        $select->joinLeft(
            array('d' => 'tbDocumento'),
            'd.idDocumento = a.idDocumento',
            array(''),
            'BDCORPORATIVO.scCorp'
        );
        $select->joinLeft(
            array('e' => 'tbArquivo'),
            'e.idArquivo = d.idArquivo',
            array(''),
            'BDCORPORATIVO.scCorp'
        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);

        
        return $this->fetchAll($select);
    }

    public function visualizarReadequacao($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.idReadequacao,
                a.idPronac,
                a.dtSolicitacao,
                CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao,
                CAST(a.dsJustificativa AS TEXT) AS dsJustificativa,
                a.idSolicitante,
                a.idAvaliador,
                a.dtAvaliador,
                CAST(a.dsAvaliacao AS TEXT) AS dsAvaliacao,
                a.idTipoReadequacao,
                CAST(c.dsReadequacao AS TEXT) AS dsReadequacao,
                a.stAtendimento,
                a.siEncaminhamento,
                CAST(b.dsEncaminhamento AS TEXT) AS dsEncaminhamento,
                a.stEstado,
                e.idArquivo,
                e.nmArquivo
            "),
            'SAC.dbo'
        );
        $select->joinInner(
            array('b' => 'tbTipoEncaminhamento'),
            'a.siEncaminhamento = b.idTipoEncaminhamento',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbTipoReadequacao'),
            'c.idTipoReadequacao = a.idTipoReadequacao',
            array(''),
            'SAC.dbo'
        );
        $select->joinLeft(
            array('d' => 'tbDocumento'),
            'd.idDocumento = a.idDocumento',
            array(''),
            'BDCORPORATIVO.scCorp'
        );
        $select->joinLeft(
            array('e' => 'tbArquivo'),
            'e.idArquivo = d.idArquivo',
            array(''),
            'BDCORPORATIVO.scCorp'
        );
//        $select->joinLeft(
//            array('f' => 'Aprovacao'), 'f.idReadequacao = a.idReadequacao',
//            array(''), 'SAC.dbo'
//        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);

        
        return $this->fetchAll($select);
    }

    /*
     * Criada em 16/03/2014
     * @author: Jefferson Alessandro
     * Fun��o usada para detalhar a readequa��o para an�lise do componente da comiss�o.
     */
    public function buscarDadosReadequacoesCnic($where = array(), $order = array())
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.idReadequacao,
                a.idPronac,
                a.dtSolicitacao,
                CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao,
                CAST(a.dsJustificativa AS TEXT) AS dsJustificativa,
                a.idSolicitante,
                a.idAvaliador,
                a.dtAvaliador,
                CAST(a.dsAvaliacao AS TEXT) AS dsAvaliacao,
                a.idTipoReadequacao,
                CAST(c.dsReadequacao AS TEXT) AS dsReadequacao,
                a.stAtendimento,
                a.siEncaminhamento,
                CAST(b.dsEncaminhamento AS TEXT) AS dsEncaminhamento,
                a.stEstado,
                e.idArquivo,
                e.nmArquivo,
                f.idDistribuirReadequacao
            ")
        );
        $select->joinInner(
            array('b' => 'tbTipoEncaminhamento'),
            'a.siEncaminhamento = b.idTipoEncaminhamento',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbTipoReadequacao'),
            'c.idTipoReadequacao = a.idTipoReadequacao',
            array(''),
            'SAC.dbo'
        );
        $select->joinLeft(
            array('d' => 'tbDocumento'),
            'd.idDocumento = a.idDocumento',
            array(''),
            'BDCORPORATIVO.scCorp'
        );
        $select->joinLeft(
            array('e' => 'tbArquivo'),
            'e.idArquivo = d.idArquivo',
            array(''),
            'BDCORPORATIVO.scCorp'
        );
        $select->joinInner(
            array('f' => 'tbDistribuirReadequacao'),
            'f.idReadequacao = a.idReadequacao',
            array(''),
            'SAC.dbo'
        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);

        
        return $this->fetchAll($select);
    }

    /*
     * Criada em 12/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     */
    public function painelReadequacoesAnalise($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false, $idPerfil = 0)
    {
        if ($idPerfil == Autenticacao_Model_Grupos::TECNICO_ACOMPANHAMENTO) {
            $nome = 'd.usu_nome AS Tecnico';
        } else {
            $nome = 'd.Descricao AS Tecnico';
        }

        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => 'tbDistribuirReadequacao'),
            array(
                new Zend_Db_Expr("a.idDistribuirReadequacao, c.IdPRONAC, c.AnoProjeto+c.Sequencial AS Pronac, c.NomeProjeto, a.DtEncaminhamento, $nome, a.idAvaliador AS idTecnico, CAST(b.dsSolicitacao as TEXT) AS dsSolicitacao, b.idReadequacao, e.dsReadequacao as tipoReadequacao")
            ),
            'SAC.dbo'
        );
        $select->joinInner(
            array('b' => 'tbReadequacao'),
            'a.idReadequacao = b.idReadequacao',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'Projetos'),
            'c.IdPRONAC = b.IdPRONAC',
            array('c.CgcCpf'),
            'SAC.dbo'
        );

        if ($idPerfil == Autenticacao_Model_Grupos::TECNICO_ACOMPANHAMENTO) {
            $select->joinLeft(
                array('d' => 'Usuarios'),
                'a.idAvaliador = d.usu_codigo',
                array(''),
                'TABELAS.dbo'
            );
        } else {
            $select->joinLeft(
                array('d' => 'Nomes'),
                'a.idAvaliador = d.idAgente',
                array(''),
                'AGENTES.dbo'
            );
        }

        $select->joinInner(
            array('e' => 'tbTipoReadequacao'),
            'e.idTipoReadequacao = b.idTipoReadequacao',
            array(''),
            'SAC.dbo'
        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        if ($qtdeTotal) {
            return $this->fetchAll($select)->count();
        }

        //adicionando linha order ao select
        $select->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }

        
        return $this->fetchAll($select);
    }

    /*
     * Criada em 14/03/2014
     * @author: Jefferson Alessandro
     * Fun��o acessada pelo componente da comiss�o.
     */
    public function painelReadequacoesComponente($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("b.idPronac, a.idReadequacao, b.AnoProjeto+b.Sequencial as PRONAC, b.NomeProjeto, a.dtSolicitacao, c.dsReadequacao, a.siEncaminhamento, d.idDistribuirReadequacao")
            )
        );
        $select->joinInner(
            array('b' => 'Projetos'),
            'a.idPronac = b.idPronac',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbTipoReadequacao'),
            'c.idTipoReadequacao = a.idTipoReadequacao',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('d' => 'tbDistribuirReadequacao'),
            'd.idReadequacao = a.idReadequacao',
            array(''),
            'SAC.dbo'
        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        if ($qtdeTotal) {
            return $this->fetchAll($select)->count();
        }

        //adicionando linha order ao select
        $select->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }

        
        return $this->fetchAll($select);
    }

    public function readequacoesNaoSubmetidas($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("b.idPronac, a.idReadequacao, b.AnoProjeto+b.Sequencial AS PRONAC, b.NomeProjeto, a.dtSolicitacao, a.idTipoReadequacao, f.dsReadequacao, c.usu_nome AS Componente, d.Descricao AS dsArea, e.Descricao AS dsSegmento"),
            )
        );

        $select->joinInner(
            array('b' => 'Projetos'),
            'a.idPronac = b.idPronac',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'Usuarios'),
            'a.idAvaliador = c.usu_codigo',
            array(''),
            'TABELAS.dbo'
        );
        $select->joinInner(
            array('d' => 'Area'),
            'b.Area = d.Codigo',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('e' => 'Segmento'),
            'b.Segmento = e.Codigo',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('f' => 'tbTipoReadequacao'),
            'f.idTipoReadequacao = a.idTipoReadequacao',
            array(''),
            'SAC.dbo'
        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        if ($qtdeTotal) {
            return $this->fetchAll($select)->count();
        }

        //adicionando linha order ao select
        $select->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }

        
        return $this->fetchAll($select);
    }

    public function buscarDadosParecerReadequacao($where = array(), $order = array())
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.idReadequacao, c.DtParecer, c.ResumoParecer, c.ParecerFavoravel, c.Logon as idAvaliador, d.usu_nome as nmAvaliador
            ")
        );
        $select->joinInner(
            array('b' => 'tbReadequacaoXParecer'),
            'b.idReadequacao = a.idReadequacao',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'Parecer'),
            'c.IdParecer = b.idParecer',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('d' => 'Usuarios'),
            'd.usu_codigo = c.Logon',
            array(''),
            'TABELAS.dbo'
        );

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);

        
        return $this->fetchAll($select);
    }

    public function buscarReadequacoesEnviadosPlenaria($idNrReuniao)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.stAnalise,
                (b.AnoProjeto+b.Sequencial) AS pronac,
                b.NomeProjeto,
                b.IdPRONAC,
                c.Descricao AS area,
                d.Descricao AS segmento,
                h.usu_nome AS nomeComponente,
                a.idReadequacao,
                i.dsReadequacao,
                a.idTipoReadequacao
            ")
        );
        $select->joinInner(
            array('b' => 'Projetos'),
            'b.IdPRONAC = a.idPronac',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'Area'),
            'b.Area = c.Codigo',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('d' => 'Segmento'),
            'b.Segmento = d.Codigo',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('g' => 'tbDistribuirReadequacao'),
            'g.idReadequacao = a.idReadequacao AND g.idUnidade = 400',
            array(''),
            'SAC.dbo'
        );
        $select->joinInner(
            array('h' => 'Usuarios'),
            'h.usu_codigo = g.idAvaliador',
            array(''),
            'TABELAS.dbo'
        );
        $select->joinInner(
            array('i' => 'tbTipoReadequacao'),
            'i.idTipoReadequacao = a.idTipoReadequacao',
            array(''),
            'SAC.dbo'
        );

        $select->where('a.stEstado = ? ', 0);
        $select->where('a.idNrReuniao = ? ', $idNrReuniao);
        $select->where('a.siEncaminhamento = ? ', 8);
        $select->where("NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbConsolidacaoVotacao AS cv WHERE a.idNrReuniao = cv.idNrReuniao AND a.idPronac = cv.IdPRONAC AND a.idTipoReadequacao = cv.tpTipoReadequacao)", '');
        $select->order(array(6, 1));

        
        return $this->fetchAll($select);
    }

    public function atualizarReadequacoesProximaPlenaria($idNrReuniao)
    {
        $sql = "UPDATE SAC.dbo.tbReadequacao
                     SET idNrReuniao = idNrReuniao + 1
                FROM  SAC.dbo.tbReadequacao a
                INNER JOIN SAC.dbo.Projetos c on (a.idPronac = c.IdPRONAC)
                WHERE siEncaminhamento = 8
                      AND NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbConsolidacaoVotacao b WHERE a.IdPRONAC = b.IdPRONAC AND a.idNrReuniao = $idNrReuniao )";
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha m�todo buscarPlanilhaDeCustos()

    public function atualizarStatusReadequacoesNaoSubmetidos($idNrReuniao)
    {
        $sql = "UPDATE SAC.dbo.tbReadequacao
                    SET stEstado = 1
               FROM  SAC.dbo.tbReadequacao a
               INNER JOIN SAC.dbo.Projetos c on (a.idPronac = c.IdPRONAC)
               WHERE a.stEstado = 0 and
                    (a.siEncaminhamento = 9 and a.idNrReuniao = $idNrReuniao ) or
                    (a.siEncaminhamento = 8 and a.stEstado = 0
                    AND EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbConsolidacaoVotacao b WHERE a.idPronac = b.IdPRONAC AND a.idNrReuniao = $idNrReuniao ))";
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha m�todo buscarPlanilhaDeCustos()

    /**
     *
     * @param bool $where
     * @param bool $order
     * @param mixed $tamanho
     * @param mixed $inicio
     * @param bool $qtdeTotal
     * @param bool $filtro
     * @return void
     */
    public function painelReadequacoesCoordenadorParecer($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false, $filtro = null)
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = array();
        $result = array();

        switch ($filtro) {
            case 'aguardando_distribuicao':
                $select = $this->selectView('vwPainelReadequacaoCoordenadorParecerAguardandoAnalise');
                break;
            case 'em_analise':
                $select = $this->selectView('vwPainelReadequacaoCoordenadorParecerEmAnalise');
                break;
            case 'analisados':
                $select = $this->selectView('vwPainelReadequacaoCoordenadorParecerAnalisados');
                break;
        }

        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        $select->order($order);

        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $select->limit($tamanho, $tmpInicio);
        }

        $stmt = $db->query($select);

        while ($o = $stmt->fetchObject()) {
            $result[] = $o;
        }

        return $result;
    }

    /**
     * @param bool $where
     * @param bool $order
     * @param mixed $tamanho
     * @param mixed $inicio
     * @param bool $qtdeTotal
     * @param bool $filtro
     * @return void
     */
    public function painelReadequacoesTecnicoAcompanhamento($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false)
    {
        try {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                array('a' => $this->_name),
                    new Zend_Db_Expr(
                        "
                        b.idPronac,
                        a.idReadequacao,
                        b.AnoProjeto+b.Sequencial as PRONAC,
                        b.NomeProjeto,
                        c.dsReadequacao as tpReadequacao,
                        d.dtEnvioAvaliador as dtDistribuicao,
                        DATEDIFF(DAY,
                        d.dtEnvioAvaliador,
                        GETDATE()) as qtDiasAvaliacao,
                        d.idAvaliador AS idTecnicoParecerista,
                        d.idUnidade as idOrgao"
                        )
                );

            $select->joinInner(
                array('d' => 'tbDistribuirReadequacao'),
                'a.idReadequacao = d.idReadequacao',
                array(''),
                $this->_schema
            );
            $select->joinInner(
                array('b' => 'Projetos'),
                'a.idPronac = b.idPronac',
                array(''),
                $this->_schema
            );
            $select->joinInner(
                array('c' => 'tbTipoReadequacao'),
                'c.idTipoReadequacao = a.idTipoReadequacao',
                array(''),
                $this->_schema
            );
            $select->joinInner(
                array('e' => 'tbTipoEncaminhamento'),
                'a.siEncaminhamento = e.idTipoEncaminhamento',
                array(''),
                $this->_schema
            );

            $select->where('a.stEstado = ? ', 0);
            $select->where('a.siEncaminhamento = ? ', 4);


            foreach ($where as $coluna => $valor) {
                $select->where($coluna, $valor);
            }

            $select->order($order);

            if ($tamanho > -1) {
                $tmpInicio = 0;
                if ($inicio > -1) {
                    $tmpInicio = $inicio;
                }
                $select->limit($tamanho, $tmpInicio);
            }

            $db = Zend_Db_Table::getDefaultAdapter();
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
            return $db->fetchAll($select);
        } catch (Exception $objException) {
            xd($objException->getMessage());
            throw new Exception($objException->getMessage(), 0, $objException);
        }
    }
    /**
     * painelReadequacoesCoordenadorAcompanhamentoCount
     *
     * @param bool $where
     * @param bool $filtro
     * @access public
     * @return int
     */
    public function painelReadequacoesCoordenadorAcompanhamentoCount($where=array(), $filtro = null)
    {
        $total = null;
        
        switch ($filtro) {
            case 'aguardando_distribuicao':
                $total = $this->count('vwPainelCoordenadorReadequacaoAguardandoAnalise', $where);
                break;
            case 'em_analise':
                $total = $this->count('vwPainelCoordenadorReadequacaoEmAnalise', $where);
                break;
            case 'analisados':
                $total = $this->count('vwPainelCoordenadorReadequacaoAnalisados', $where);
                break;
            case 'aguardando_publicacao':
                $total = $this->count('vwPainelReadequacaoAguardandoPublicacao', $where);
                break;
        }

        return $total;
    }

    public function buscarReadequacaoCoordenadorParecerEmAnalise($where = array(), $order = array(), $tamanho = -1, $inicio = -1)
    {
        try {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                array('a' => $this->_name),
                new Zend_Db_Expr(
                    "
                 b.idPronac,
                 a.idReadequacao,
                 b.AnoProjeto+b.Sequencial as PRONAC,
                 b.NomeProjeto,
                 b.Area,
                 b.Segmento,
                 c.dsReadequacao as tpReadequacao,
                 d.dtEnvioAvaliador as dtDistribuicao,
                 DATEDIFF(DAY,    
                 d.dtEnvioAvaliador,
                 GETDATE()) as qtDiasEmAnalise,
                 d.idAvaliador,
                 f.usu_nome as nmParecerista,
                 d.idUnidade as idOrgao"
                )
            );

            $select->joinInner(
                array('d' => 'tbDistribuirReadequacao'),
                'a.idReadequacao = d.idReadequacao',
                array(''),
                $this->_schema
            );
            $select->joinInner(
                array('b' => 'Projetos'),
                'a.idPronac = b.idPronac',
                array(''),
                $this->_schema
            );
            $select->joinInner(
                array('c' => 'tbTipoReadequacao'),
                'a.idReadequacao = d.idReadequacao',
                array(''),
                $this->_schema
            );
            $select->joinInner(
                array('e' => 'tbTipoEncaminhamento'),
                'a.siEncaminhamento = e.idTipoEncaminhamento',
                array(''),
                $this->_schema
            );
            $select->joinLeft(
                array('f' => 'Usuarios'),
                'd.idAvaliador = f.usu_codigo',
                array(''),
                $this->getSchema('tabelas')
            );

            $select->where('a.stEstado = ? ', 0);
            $select->where('a.siEncaminhamento = ? ', 4);

            foreach ($where as $coluna => $valor) {
                $select->where($coluna, $valor);
            }

            $select->order($order);

            if ($tamanho > -1) {
                $tmpInicio = 0;
                if ($inicio > -1) {
                    $tmpInicio = $inicio;
                }
                $select->limit($tamanho, $tmpInicio);
            }
            $db = Zend_Db_Table::getDefaultAdapter();
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
            return $db->fetchAll($select);
        } catch (Exception $objException) {
            throw new Exception($objException->getMessage(), 0, $objException);
        }
    }

    public function existeRemanejamento50EmAndamento($idPronac)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            'a.idReadequacao'
        );

        $select->where('a.idPronac = ?', $idPronac);
        $select->where('a.idTipoReadequacao=?', self::TIPO_READEQUACAO_REMANEJAMENTO_PARCIAL);
        $select->where('a.stAtendimento=?', 'D');
        $select->where('a.stEstado=?', self::ST_ESTADO_EM_ANDAMENTO);
        $select->where('a.siEncaminhamento=?', Readequacao_Model_tbTipoEncaminhamento::SI_ENCAMINHAMENTO_NAO_ENVIA_MINC);
        
        $remanejamentos = $this->fetchAll($select);

        if (count($remanejamentos) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Método para verificar se existe qualquer tipo de readequação em andamento
     * @access public
     * @param integer $idPronac
     * @param integer $idTipoReadequacao
     * @return boolean
     */    
    public function existeReadequacaoEmAndamento($idPronac, $idTipoReadequacao = null)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('r' => $this->_name),
            'r.idReadequacao'
        );
        $select->where('r.idPronac = ?', $idPronac);
        
        if ($idTipoReadequacao) {
            $tiposReadequacoes = array($idTipoReadequacao);
        } else {
            $tiposReadequacoes = array(
                self::TIPO_READEQUACAO_REMANEJAMENTO_PARCIAL,
                self::TIPO_READEQUACAO_PLANILHA_ORCAMENTARIA
            );
        }
        
        $select->where('r.idTipoReadequacao IN(?)', $tiposReadequacoes);
        $select->where('r.stEstado=?', self::ST_ESTADO_EM_ANDAMENTO);
        
        $result = $this->fetchAll($select);
        
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Método para verificar se existe readequacao de planilha em edição
     * @access public
     * @param integer $idPronac
     * @return boolean
     */    
    public function existeReadequacaoPlanilhaEmEdicao($idPronac)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('r' => $this->_name),
            'r.idReadequacao'
        );
        $select->where('r.idPronac = ?', $idPronac);
        $select->where('r.siEncaminhamento = ?', Readequacao_Model_tbTipoEncaminhamento::SI_ENCAMINHAMENTO_CADASTRADA_PROPONENTE);
        $select->where('r.idTipoReadequacao = ?', self::TIPO_READEQUACAO_PLANILHA_ORCAMENTARIA);
        $select->where('r.stEstado=?', self::ST_ESTADO_EM_ANDAMENTO);
        
        $result = $this->fetchAll($select);
        
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Método para verificar se existe readequacao parcial em edição
     * @access public
     * @param integer $idPronac
     * @return boolean
     */    
    public function existeReadequacaoParcialEmEdicao($idPronac)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('r' => $this->_name),
            'r.idReadequacao'
        );
        $select->where('r.idPronac = ?', $idPronac);
        $select->where('r.siEncaminhamento = ?', Readequacao_Model_tbTipoEncaminhamento::SI_ENCAMINHAMENTO_NAO_ENVIA_MINC);
        $select->where('r.idTipoReadequacao = ?', self::TIPO_READEQUACAO_REMANEJAMENTO_PARCIAL);
        $select->where('r.stEstado=?', self::ST_ESTADO_EM_ANDAMENTO);
        
        $result = $this->fetchAll($select);
        
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }    

    /**
     * Método para verificar se está o projeto está disponivel para edição da readequacao de planilha
     * @access public
     * @param integer $idPronac
     * @return boolean
     */
    public function disponivelParaEdicaoReadequacaoPlanilha($idPronac)
    {
        $liberacao = new Liberacao();
        $projeto = new Projetos();
        
        $existeReadequacaoEmAndamento = $this->existeReadequacaoEmAndamento($idPronac);
        $contaLiberada = $liberacao->contaLiberada($idPronac);
        $periodoExecucaoVigente = $projeto->verificarPeriodoExecucaoVigente($idPronac);
        
        if ($existeReadequacaoEmAndamento &&
            $contaLiberada &&
            $periodoExecucaoVigente) {

            return true;
        } else {
            return false;
        }        
    }

    /**
     * Método para verificar se está o projeto está disponivel para edição do remanejamento de planilha
     * @access public
     * @param integer $idPronac
     * @return boolean
     */
    public function disponivelParaEdicaoRemanejamentoPlanilha($idPronac)
    {
        $liberacao = new Liberacao();
        $projeto = new Projetos();
        $tbCumprimentoObjeto = new tbCumprimentoObjeto();
        
        $existeReadequacaoEmAndamento = $this->existeReadequacaoEmAndamento($idPronac);
        $contaLiberada = $liberacao->contaLiberada($idPronac);
        $periodoExecucaoVigente = $projeto->verificarPeriodoExecucaoVigente($idPronac);
        $possuiRelatorioDeCumprimento = $tbCumprimentoObjeto->possuiRelatorioDeCumprimento($idPronac);
        
        if (
            $existeReadequacaoEmAndamento &&
            $periodoExecucaoVigente &&
            (
                $contaLiberada ||
                (!$contaLiberada && !$possuiRelatorioDeCumprimento)
            )
        ) {
            return true;
        } else {
            return false;
        }        
    }
    
    /**
     * Método para verificar se está o projeto está disponivel para adição de itens da readequacao de planilha
     * @access public
     * @param integer $idPronac
     * @return boolean
     */
    public function disponivelParaAdicaoItensReadequacaoPlanilha($idPronac)
    {
        $liberacao = new Liberacao();
        $projeto = new Projetos();
        
        $existeReadequacaoEmAndamento = $this->existeReadequacaoEmAndamento(
            $idPronac,
            self::TIPO_READEQUACAO_PLANILHA_ORCAMENTARIA
        );
        $contaLiberada = $liberacao->contaLiberada($idPronac);
        $periodoExecucaoVigente = $projeto->verificarPeriodoExecucaoVigente($idPronac);
        
        if ($existeReadequacaoEmAndamento &&
            $contaLiberada &&
            $periodoExecucaoVigente) {

            return true;
        } else {
            return false;
        }        
    }

    /**
     * Método para retornar idPronac do projeto com readequação em andamento mais recentemente criada, com, prazo de execução vigente
     * @param integer $idTipoReadequacao
     * @return integer
     */
    public function buscarIdPronacReadequacaoEmAndamento($idTipoReadequacao)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('r' => $this->_name),
            'r.idPronac'
        );

        $select->joinInner(
            array('p' => 'Projetos'),
            'r.idPronac = p.idPronac',
            array(''),
            $this->_schema
        );
        
        $select->where('r.idTipoReadequacao = ?', $idTipoReadequacao);
        $select->where('r.stEstado=?', self::ST_ESTADO_EM_ANDAMENTO);
        $select->where(new Zend_Db_Expr('p.DtInicioExecucao < GETDATE()'));
        $select->where(new Zend_Db_Expr('p.DtFimExecucao > GETDATE()'));
        $select->order('r.dtSolicitacao DESC');
        $select->limit(1);
        
        $result = $this->fetchAll($select);
        
        if (count($result) > 0) {
            return $result->current()['idPronac'];
        } else {
            return false;
        }
    }
}
