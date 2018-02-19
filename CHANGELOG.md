# Changelog

### 1.0.16 - 10/07/2017
- Alterações para parcelamento de pedidos pelo Painel Administrativo e em múltiplas lojas.

### 1.0.15 - 27/06/2017
- Correção na compra de assinaturas com parcelamento no boleto.

### 1.0.14 - 01/06/2017
- Melhorias no suporte para parcelamento de assinaturas.

### 1.0.13 - 09/12/2016
- Pequenos ajustes na criação do perfil de pagamento.

### 1.0.12 - 11/10/2016
- Ajustes no problema de comunicação com a API da Vindi.

### 1.0.11 - 31/08/2016
- Adicionado parcelamento por Store View.

### 1.0.10 - 04/04/2016
- Ajustes na exibição das informações sobre o cartão no pedido.

### 1.0.9 - 28/03/2016
- Ajustes no calculo parcelas mínimas.

### 1.0.8 - 01/02/2016
- Ajustes nos valores recebidos através dos webhooks.

### 1.0.7 - 11/01/2016
- Adicionado suporte ao checkout nativo.

### 1.0.6 - 24/11/2015
- Requests para a API terão como padrão o protocolo TSL 1.2.

### 1.0.5 - 21/10/2015
- Adicionado suporte a descontos nos produtos Assinatura Vindi.
- Envio de frete e produtos separadamente.
- Recorrências respeitam o valor recebido através dos webhooks.
- Opção de mostrar o link para os boletos nos comentários dos pedidos (backend e frontend).
- Melhorias de performance nos webhooks.

### 1.0.4 - 06/10/2015
- Adicionado método de envio padrão como fallback.
- Http Headers para retentativa dos weebhooks.

### 1.0.3 - 06/08/2015
- Adicionado suporte para criação de pedidos pelo painel de administração.

### 1.0.2 - 29/07/2015
- Atualizado para Versão estável.

### 0.0.3 - 28/07/2015
- Venda de produtos simples/faturas avulsas.
- Parcelamento de faturas avulsas.
- Cache de requisições de clientes para aumento de performance.
- Alterada a geração de códigos das assinaturas.
- Adicionada integração com nota fiscal da Bling.

### 0.0.2 - 17/07/2015
- Atualização de pedido com informações de cobranças rejeitadas. Caso o sistema esteja configurado para realizar
novas tentativas de cobranças, apenas adiciona um comentário no pedido, senão, muda o status para "Cancelado".
- Geração de novos pedidos na recorrência dos planos.

### 0.0.1 - 03/07/2015
- Versão Inicial.
