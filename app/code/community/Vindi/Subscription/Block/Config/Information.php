<?php

class Vindi_Subscription_Block_Config_Information extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * @param \Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $helper = Mage::helper('vindi_subscription');

        if(! $helper->getKey()) {
            return '';
        }

        $html = '<tr><td colspan="4" class="label"><h3>Informações sobre a conta Vindi</h3></td></tr>';

        /** @var Vindi_Subscription_Helper_API $api */
        $api = Mage::helper('vindi_subscription/api');

        $merchant = $api->getMerchant();

        $html .= '<tr><td class="label">Conexão</td>';

        if (! $merchant) {
            $html .= '<td class=" value error-msg">Falha na Conexão!<br />Verifique sua conta e tente novamente!</td></tr>';
            return $html;
        }
        $html .= '<td class="value success-msg">Conectado com Sucesso!</td></tr>';

        $html .= '<tr><td class="label">Conta</td><td class="value">' . $merchant['name'] . '</td></tr>';

        $status = $api->isMerchantStatusTrial() ? 'Trial' : 'Ativo';
        $html .= '<tr><td class="label">Status</td><td class="value">'. $status .'</td></tr>';

        $html .= '<tr><td colspan="4" class="label"><h3>Configuração dos Eventos da Vindi</h3></td></tr>';

        $html .= '<tr>
                    <td class="label">URL dos Webhooks</td>
                    <td class="value">
                        <input type="text" value="' . $helper->getWebhookURL() .'" style="width:100%" readonly onclick="this.select();" />
                        <p class="note" style="width:100%"><span>Copie esse link e utilize-o para configurar os eventos nos Webhooks da Vindi.</span></p>
                    </td>
                  </tr>';

        return $html;
    }
}