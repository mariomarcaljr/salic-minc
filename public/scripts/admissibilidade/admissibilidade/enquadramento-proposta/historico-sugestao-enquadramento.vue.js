Vue.component('historico-sugestao-enquadramento-proposta', {
    template: `
        <div v-if="id_preprojeto">
            <h4>Hist&oacute;rico de Sugest&otilde;es de Enquadramento.</h4>
            <div class="row">
                <table class="tabela striped" id="historicoSugestoes" style="border: 0px solid gray">
                    <thead>
                    <tr class="destacar">
                        <td class="center" width="10%"><b>N&uacute;mero</b></td>
                        <td class="center" width="10%"><b>Data</b></td>
                        <td class="center" width="20%"><b>Avaliador</b></td>
                        <td class="center" width="20%"><b>Unidade</b></td>
                        <td class="center" width="10%"><b>&Aacute;rea</b></td>
                        <td class="center" width="10%"><b>Segmento</b></td>
                        <td class="center" width="10%"><b>Enquadramento</b></td>
                        <td class="center"><b>Parecer</b></td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->historicoEnquadramento as $indice => $enquadramentoProposta) :
                        ?>
                        <tr>
                            <td align="left"><?php echo ++$indice ?></td>
                            <td align="left"><?php echo Data::dataBrasileira($enquadramentoProposta['data_avaliacao']) ?></td>
                            <td align="left"><?php echo $enquadramentoProposta['usu_nome'] ?></td>
                            <td align="left"><?php echo "{$enquadramentoProposta['org_sigla']} - {$enquadramentoProposta['gru_nome']}" ?></td>
                            <?php
                            $artigoEnquadramento = " - ";
                            if($enquadramentoProposta['tp_enquadramento']) {
                                $artigoEnquadramento = ($enquadramentoProposta['tp_enquadramento'] == 1) ? 'Artigo 26' : 'Artigo 18';
                            }
                            $area = ($enquadramentoProposta['area']) ? $enquadramentoProposta['area'] : ' - ';
                            $segmento = ($enquadramentoProposta['segmento']) ? $enquadramentoProposta['segmento'] : ' - ';
                            ?>
                            <td align="center" class="center"><?php echo $area ?></td>
                            <td align="center" class="center"><?php echo $segmento ?></td>
                            <td align="center" class="center"><?php echo $artigoEnquadramento ?></td>
                            <td align="left" nowrap><?php echo $enquadramentoProposta['descricao_motivacao'] ?></td>
                        </tr>
                        <?php
                    endforeach;
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else class="center-align">
            <div class="padding10 green white-text">Opa! Proposta n�o informada...</div>
        </div>
    `,
    data: function () {
        return {
            sugestoes_enquadramento: {
                type: Object,
                default: function () {
                    return {}
                }
            }
        }
    },
    props: [
        'id_preprojeto'
    ],
    mounted: function () {
        this.buscar_dados()
    },
    methods: {
        buscar_dados: function () {
            let vue = this
            $3.ajax({
                url: '/admissibilidade/enquadramento-proposta/obter-historico-sugestao-enquadramento-ajax/id_preprojeto/'
                + vue.id_preprojeto
            }).done(function (response) {
                vue.sugestoes_enquadramento = response.sugestoes_enquadramento
            })
        }
    }
})