# Vindi Magento Recorrente v1.0.5

## Descrição
A integração do módulo da Vindi permite criação e gestão de planos e assinaturas através do Magento de forma transparente.

## Recursos
- Pagamentos recorrentes por cartão de crédito.
- Pagamentos recorrentes por boleto bancário.
- Pagamentos avulsos (produtos simples) por cartão de crédito.
- Pagamentos avulsos (produtos simples) por boleto bancário.
- Parcelamento de pagamentos avulsos.
- Integração com notas fiscais de produtos e serviços através de nossos parceiros.
- Aceita cálculo de descontos vitalícios e frete.
- Mudança de status do pedido de "Pagamento Pendente" para "Processando" ao receber a confirmação de pagamento.
- Atualização de pedido com informações de cobranças rejeitadas.
- Geração de novos pedidos para os próximos períodos da recorrência dos planos.
- Recuperação de informações do cartão já cadastrado do cliente, permitindo checkout com 1 clique.

## Requerimentos
- PHP 5.5 ou superior.
- cURL habilitado para o PHP.
- Magento Community Edition 1.7 ou superior.

## Recomendações
- Módulo [One Step Checkout Brasil 6 Pro](https://github.com/vindi/OSC-Magento-Brasil-6-Pro).

## Instalação
###1. Via [modman](https://github.com/colinmollenhour/modman): (recomendado)
```
modman clone https://github.com/vindi/vindi-magento.git
```

**Nota**: para o correto funcionamento do módulo para instalação via modman, ative os *symlinks* em:

*Sistema > Configuração > Avançado > Desenvolvedor > Configurações de Template > Permitir Symlinks* e marque como **Sim**.

###2. Via zip: (não recomendado)
1. [Faça o download do zip](https://github.com/vindi/vindi-magento/archive/master.zip).
2. extraia o conteúdo da pasta `src` em sua instalação do Magento.

## Atualização
###1. Via [modman](https://github.com/colinmollenhour/modman): (recomendado)
```
modman update Vindi_Subscription
```
###2. Via zip: (não recomendado)
1. [Faça o download do zip](https://github.com/vindi/vindi-magento/archive/master.zip).
2. extraia o conteúdo da pasta `src` em sua instalação do Magento.

## Configuração
1. Configure o [One Step Checkout Brasil 6 Pro](https://github.com/deivisonarthur/OSC-Magento-Brasil-6-Pro) conforme instruções contidas em seu próprio link.
Caso não deseje utilizá-lo, será necessário entrar em contato com a Vindi para alinhamento de como obter as informações de clientes como endereço e documentos (CPF/CNPJ, RG/IE).
1. Em *System > Configuration > Vindi > Vindi Assinaturas*  informe a chave da API de sua conta Vindi e salve.
1. Caso a conexão ocorra com sucesso, você verá um link para configuração dos *Webhooks*, que deverá ser feito no **painel da Vindi**.
1. Em *System > Configuration > Sales > Payment Methods*, configure os métodos de pagamento **Vindi - Cartão de Crédito**  e **Vindi - Boleto Bancário**.
1. Em *Catalog > Manage Products*, adicione um produto e escolha o *Product Type* como **Assinatura Vindi**.
1. Configure o produto normalmente, lembrando-se de escolher o *Plano da Vindi* na aba *Vindi*.
1. Pronto! Agora é só efetuar a venda e a assinatura será registrada.

## Limitações
- Na recorrência, os pedidos são gerados com **o mesmo endereço** do pedido do período anterior.
- O módulo **não** gera pedidos do Magento para criação/recorrência de assinaturas que não possuam um pedido (no Magento) para o período anterior.
Ou seja, um pedido captado fora do Magento não irá ser criado no mesmo.

## Roadmap
Novos recursos que entrarão neste módulo, por ordem de prioridade:

- Adicionar recorrência para todos os tipos de produtos
- Melhorias na validação (processo de criação de cliente -> perfil de pagamento -> assinatura) e mensagens de erro.
- Frontend: Botão para download do boleto bancário ao concluir a compra e nos pedidos, para clientes.
**Nota:** Independente destes botões, a plataforma da Vindi envia automaticamente o boleto para o cliente por e-mail.

## Changelog
### 1.0.5 - 21/10/2015
- Adicionado suporte a descontos nos produtos Assinatura Vindi.
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
