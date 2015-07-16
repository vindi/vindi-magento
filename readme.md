# Vindi Magento Recorrente

**Nota: Este módulo encontra-se em estágio de desenvolvimento, não sendo recomendada sua utilização em ambiente de produção.**

## Descrição
A integração do módulo da Vindi permite criação e gestão de planos e assinaturas através do Magento de forma transparente.

## Recursos
- Pagamentos recorrentes por cartão de crédito.
- Pagamentos recorrentes por boleto bancário.
- Aceita cálculo de descontos vitalícios e frete.
- Mudança de status do pedido de "Pagamento Pendente" para "Processando" ao receber a confirmação de pagamento.
- Geração de novos pedidos para os próximos períodos da recorrência dos planos.

## Requerimentos
- PHP 5.5 ou superior.
- cURL habilitado para o PHP.
- Magento Community Edition 1.9 ou superior.
- Módulo [One Step Checkout Brasil 6 Pro](https://github.com/deivisonarthur/OSC-Magento-Brasil-6-Pro).

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
1. Em *System > Configuration > Vindi > Vindi Assinaturas*  informe a chave da API de sua conta Vindi e salve.
1. Caso a conexão ocorra com sucesso, você verá um link para configuração dos *Webhooks*, que deverá ser feito no **painel da Vindi**.
1. Em *System > Configuration > Sales > Payment Methods*, configure os métodos de pagamento **Vindi - Cartão de Crédito**  e **Vindi - Boleto Bancário**.
1. Em *Catalog > Manage Products*, adicione um produto e escolha o *Product Type* como **Assinatura Vindi**.
1. Configure o produto normalmente, lembrando-se de escolher o *Plano da Vindi* na aba *Vindi*.
1. Pronto! Agora é só efetuar a venda e a assinatura será registrada.

## Limitações
- O valor cobrado no checkout do Magento será o que efetivamente será recorrente para o cliente.
Por exemplo, se a soma produto + frete + desconto resultar em *X*, esse valor *X* será cobrado todos os meses do cliente.  
- No momento, o primeiro item/produto de um plano da Vindi deve ser permanente, pois o valor gerado no checkout do Magento será adicionado **integralmente** neste item, 
e os demais ficarão com valor R$ 0,00 (zero reais).
- Por conta da limitação acima, não é possível trabalhar de forma automática com items/produtos temporários, pois os mesmos terão valor zero.
- Na recorrência, os pedidos são gerados com **o mesmo endereço** do pedido do período anterior.   
- O módulo só aceita a venda de **Assinaturas Vindi**. Não é possível, no momento, efetuar venda de outros tipos de produtos / faturas avulsas.
**Nota:** no momento, é possível contornar essa limitação criando planos com duração de apenas 1 período (ou seja, sem recorrência).
- O módulo **não** gera pedidos do Magento para criação/recorrência de assinaturas que não possuam um pedido (no Magento) para o período anterior. 
Ou seja, um pedido captado fora do Magento não irá ser criado no mesmo.  
  
## Roadmap
Novos recursos que entrarão neste módulo, por ordem de prioridade:

- Recuperação de informações do cartão já cadastrado do cliente, permitindo checkout com 1 clique.
- Aceitar a venda de produtos simples/faturas avulsas. 
- Frontend: Botão para download do boleto bancário ao concluir a compra e nos pedidos, para clientes.
**Nota:** Independente destes botões, a plataforma da Vindi envia automaticamente o boleto para o cliente por e-mail.
- Backend: Botões para download e envio por e-mail do boleto bancário ao acessar os pedidos, para gestores.