# Notas das versões

## [1.3.3 - 27/12/2018](https://github.com/vindi/vindi-magento/releases/tag/1.3.3)

### Corrigido
- Corrige validação no pagamento dos pedidos com AntiFraude habilitado


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
