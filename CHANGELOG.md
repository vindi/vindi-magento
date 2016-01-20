# Changelog
### 1.0.7 - 11/01/2016
- Adicionado suporte ao checkout nativo

### 1.0.6 - 24/11/2015
- Requests para a API terão como padrão o protocolo TSL 1.2 

### 1.0.5 - 21/10/2015
- Adicionado suporte a descontos nos produtos Assinatura Vindi
- Envio de frete e produtos separadamente
- Recorrências respeitam o valor recebido através dos webhooks
- Opção de mostrar o link para os boletos nos comentários dos pedidos (backend e frontend)
- Melhorias de performance nos webhooks

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
- Versão Inicial
