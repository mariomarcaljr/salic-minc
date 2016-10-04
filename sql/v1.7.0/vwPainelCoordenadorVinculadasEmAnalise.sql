-- =========================================================================================
-- Autor: Rômulo Menhô Barbosa
-- Data de Criação: 19/04/2016
-- Descrição: Painel do Coordenador de Vinculada com projeto em análise com o parecerista.
-- =========================================================================================

IF OBJECT_ID ('vwPainelCoordenadorVinculadasEmAnalise', 'V') IS NOT NULL
DROP VIEW vwPainelCoordenadorVinculadasEmAnalise ;
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE VIEW dbo.vwPainelCoordenadorVinculadasEmAnalise
AS
SELECT p.IdPRONAC, (p.AnoProjeto + p.Sequencial) AS NrProjeto, p.NomeProjeto,t.idProduto,r.Descricao AS Produto, 
       b.Area AS idArea, a.Descricao AS Area,b.Segmento as idSegmento,c.Descricao AS Segmento,      
       t.idDistribuirParecer, t.idOrgao,t.DtEnvio as DtEnvioMincVinculada,t.DtDistribuicao,
	   DATEDIFF(DAY,t.DtEnvio,t.DtDistribuicao) AS qtDiasParaDistribuir,t.DtDevolucao,
	   sac.dbo.fnQtdeDiasAnaliseParecerista(p.IdPRONAC,t.idProduto,0) AS TempoTotalAnalise,
	   sac.dbo.fnQtdeDiasAnaliseParecerista(p.IdPRONAC,t.idProduto,1) AS TempoParecerista,
	   sac.dbo.fnQtdeDiasAnaliseParecerista(p.IdPRONAC,t.idProduto,2) AS TempoDiligencia,
	   t.idAgenteParecerista,d.Descricao as Parecerista,
	   (SELECT COUNT(*) FROM sac.dbo.PlanoDistribuicaoProduto y WHERE y.idProjeto = p.idProjeto AND stPrincipal = 0) AS QtdeSecundarios,

	   (SELECT min(x1.dtSolicitacao) FROM tbDiligencia x1
	                                 WHERE x1.idTipoDiligencia = 124 AND x1.stEstado = 0 AND x1.stEnviado = 'S' 
									       AND x1.idProduto = b.idProduto AND x1.idPronac =  p.IdPRONAC) AS dtEnvioDiligencia, 
	   (SELECT max(x2.DtResposta) FROM tbDiligencia x2
	                               WHERE x2.idTipoDiligencia = 124 AND x2.stEstado = 0 AND x2.stEnviado = 'S' 
								         AND x2.idProduto = b.idProduto AND x2.idPronac =  p.IdPRONAC) AS dtRespostaDiligencia, 
	   (SELECT COUNT(*) FROM tbDiligencia x3
	                    WHERE x3.idTipoDiligencia = 124 AND x3.stEstado = 0 AND x3.stEnviado = 'S' AND x3.idProduto = b.idProduto 
						      AND x3.idPronac =  p.IdPRONAC) AS qtDiligenciaProduto, 
	   (SELECT SUM(x.Ocorrencia*x.Quantidade*x.ValorUnitario) 
	           FROM SAC.dbo.tbPlanilhaProjeto x 
	           WHERE p.IdPRONAC = x.idPRONAC AND x.FonteRecurso = 109 AND x.idProduto = t.idProduto) AS Valor,
	   CAST(t.Observacao AS TEXT) AS Observacao,t.stPrincipal,t.FecharAnalise 
FROM tbDistribuirParecer            AS t
INNER JOIN Projetos                 AS p ON t.idPRONAC            = p.IdPRONAC
INNER JOIN PlanoDistribuicaoProduto AS b ON p.idProjeto           = b.idProjeto and t.idProduto = b.idProduto
INNER JOIN Produto                  AS r ON b.idProduto           = r.Codigo
INNER JOIN Area                     AS a ON b.Area                = a.Codigo 
INNER JOIN Segmento                 AS c ON b.Segmento            = c.Codigo 
INNER JOIN Agentes.dbo.Nomes        AS d ON t.idAgenteParecerista = d.idAgente
WHERE t.stEstado = 0 
      AND t.FecharAnalise= 0
      AND t.tipoanalise IN (3, 1) 
	  AND p.Situacao IN ('B11', 'B14') 
	  AND t.idAgenteParecerista IS NOT NULL
	  AND t.DtDistribuicao      IS NOT NULL
	  AND t.DtDevolucao         IS NULL
GO 

GRANT  SELECT ON dbo.vwPainelCoordenadorVinculadasEmAnalise  TO usuarios_internet
GO
