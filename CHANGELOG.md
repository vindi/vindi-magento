# Notas das versões

## [1.8.0 - 03/06/2019](https://github.com/vindi/vindi-magento/releases/tag/1.8.0)

### Ajustado
- Ajusta tratativa de Webhooks (Fatura Criada e Fatura Paga)


## [1.7.1 - 03/05/2019](https://github.com/vindi/vindi-magento/releases/tag/1.7.1)

### Corrigido
- Persiste os dados do método de pagamento no pedido Magento


## [1.7.0 - 23/04/2019](https://github.com/vindi/vindi-magento/releases/tag/1.7.0)

### Adicionado
- Adiciona validação de pedidos finalizados para Webhook de Fatura Paga
- Adiciona fluxo de cancelamento de assinaturas após pagamento rejeitado
- Associa ID da fatura Vindi ao pedido no Magento 


## [1.6.2 - 17/04/2019](https://github.com/vindi/vindi-magento/releases/tag/1.6.2)

### Ajustado
- Ajusta alteração de valores do produto no Magento
- Ajusta status do retorno para Webhook de Teste


## [1.6.1 - 16/04/2019](https://github.com/vindi/vindi-magento/releases/tag/1.6.1)

### Corrigido
- Corrige geração de items duplicados na Fatura Vindi


## [1.6.0 - 15/04/2019](https://github.com/vindi/vindi-magento/releases/tag/1.6.0)

### Corrigido
- Corrige status de retorno para os eventos de Webhooks Vindi

### Ajustado
- Altera tipo de produto utilizado para geração de cobranças


## [1.5.1 - 04/04/2019](https://github.com/vindi/vindi-magento/releases/tag/1.5.1)

### Corrigido
- Corrige comportamento do Webhook 'charge_rejected' para cobranças via Cartão de Débito


## [1.5.0 - 01/03/2019](https://github.com/vindi/vindi-magento/releases/tag/1.5.0)

### Adicionado
- Insere informações de NSU nas renovações de pedidos via Webhooks


## [1.4.2 - 19/02/2019](https://github.com/vindi/vindi-magento/releases/tag/1.4.2)

### Corrigido
- Corrige comportamento da renovação de assinaturas para o Webhook de Fatura Criada


## [1.4.1 - 11/02/2019](https://github.com/vindi/vindi-magento/releases/tag/1.4.1)

### Corrigido
- Corrige mensagem no checkout para exibição do boleto bancário


## [1.4.0 - 29/01/2019](https://github.com/vindi/vindi-magento/releases/tag/1.4.0)

### Corrigido
- Corrige sincronização de produtos na assinatura Vindi
- Corrige preço do pedido que não refletia o preço da fatura


## [1.3.4 - 11/01/2019](https://github.com/vindi/vindi-magento/releases/tag/1.3.4)

### Ajustado
- Ajusta a função getDebitCardRedirectUrl para pegar o redirect_url em vez de authorization_url


## [1.3.3 - 27/12/2018](https://github.com/vindi/vindi-magento/releases/tag/1.3.3)

### Corrigido
- Corrige validação no pagamento dos pedidos com AntiFraude habilitado

### Ajustado
- Ajusta validação de produtos no carrinho


## [1.3.2 - 06/09/2018](https://github.com/vindi/vindi-magento/releases/tag/1.3.2)

### Corrigido
- Corrige validação do método de pagamento na geração dos pedidos


## [1.3.1 - 23/08/2018](https://github.com/vindi/vindi-magento/releases/tag/1.3.1)

### Ajustado
- Ajusta validação das taxas


## [1.3.0 - 20/08/2018](https://github.com/vindi/vindi-magento/releases/tag/1.3.0)

### Adicionado
- Adiciona compatibilidade com as taxas do Magento

### Ajustado
- Ajusta retorno do método de criação de assinaturas


## [1.2.0 - 10/08/2018](https://github.com/vindi/vindi-magento/releases/tag/1.2.0)

### Adicionado
- Adiciona exibição de pedidos com status pendente no painel do cliente
- Adiciona compatibilidade com quantidade de assinaturas

### Ajustado
- Ajusta parcelas para compras variadas (produtos recorrentes + produtos avulsos)

### Removido
- Remove verificação da data de expiração do cartão nas renovações via Webhooks
- Remove consulta adicional na fatura da Vindi após finalização da compra


## [1.1.0 - 20/06/2018](https://github.com/vindi/vindi-magento/releases/tag/1.1.0)

### Adicionado
- Adiciona transação de verificação para cartões de crédito


## [1.0.17 - 11/04/2018](https://github.com/vindi/vindi-magento/releases/tag/1.0.17)

### Adicionado
- Adiciona NSU e número de parcelas de compras de cartões de crédito no administrador
- Adiciona ambiente de sandbox para testes
- Adiciona cartão de débito
- Adiciona captura do campo 'telefone'
- Adiciona parcelamento por Store View

### Corrigido
- Corrige compra de assinaturas com parcelamento no boleto

### Ajustado
- Ajusta URL do boleto na confirmação do checkout
- Ajusta criação do perfil de pagamento
- Ajusta no problema de comunicação com a API da Vindi


## [1.0.10 - 04/04/2016](https://github.com/vindi/vindi-magento/releases/tag/1.0.10)

### Ajustado
- Ajusta exibição das informações sobre o cartão no pedido


## [1.0.9 - 28/03/2016](https://github.com/vindi/vindi-magento/releases/tag/1.0.9)

### Corrigido
- Corrige cálculo de parcela mínima


## [1.0.8 - 01/02/2016](https://github.com/vindi/vindi-magento/releases/tag/1.0.8)

### Ajustado
- Ajusta valores recebidos através dos webhooks


## [1.0.7 - 11/01/2016](https://github.com/vindi/vindi-magento/releases/tag/1.0.7)

### Adicionado
- Adiciona suporte ao checkout nativo
- Adiciona padrão de protocolo TLS 1.2


## [1.0.5 - 21/10/2015](https://github.com/vindi/vindi-magento/releases/tag/1.0.5)

### Adicionado
- Adiciona suporte a descontos nos produtos da assinatura Vindi
- Adiciona envio de frete e produtos separadamente
- Adiciona opção de mostrar o link para os boletos nos comentários dos pedidos
- Adiciona faturas avulsas
- Adiciona parcelamento de faturas avulsas
- Adiciona cache de requisições de clientes para aumento de performance
- Adiciona integração com nota fiscal da Bling

### Ajustado
- Altera a geração de códigos das assinaturas


## [1.0.0 - 06/10/2015](https://github.com/vindi/vindi-magento/releases/tag/1.0.0)
- Versão Inicial
