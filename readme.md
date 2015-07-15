# Vindi Magento Recorrente

**Nota: Esta é uma versão em estágio inicial desenvolvimento, não devendo ser utilizada em ambiente de produção.**

## Descrição
A integração do módulo da Vindi permite criação e gestão de planos e assinaturas através do Magento de forma transparente.

## Requerimentos
- PHP 5.5 ou superior.
- Magento Community Edition 1.6 ou superior.
- Módulo [One Step Checkout Brasil 6 Pro](https://github.com/deivisonarthur/OSC-Magento-Brasil-6-Pro).

## Instalação
###1. Via [modman](https://github.com/colinmollenhour/modman): (recomendado) 
```
modman clone https://github.com/vindi/vindi-magento.git
```
###2. Via zip: (não recomendado)
1. [Faça o download do zip](https://github.com/vindi/vindi-magento/archive/master.zip).
2. extraia o conteúdo da pasta `src` em sua instalação do Magento. 

## Configuração
1. Configure o [One Step Checkout Brasil 6 Pro](https://github.com/deivisonarthur/OSC-Magento-Brasil-6-Pro) conforme instruções contidas em seu próprio link.
1. Em *System > Configuration > Vindi > Vindi Assinaturas*  informe a chave da API de sua conta Vindi.
1. Em *System > Configuration > Sales > Payment Methods*, configure os métodos de pagamento **Vindi - Cartão de Crédito**  e **Vindi - Boleto Bancário**.
1. Em *Catalog > Manage Products*, adicione um produto e escolha o *Product Type* como **Assinatura Vindi**.
1. Configure o produto normalmente, lembrando-se de escolher o *Plano da Vindi* na aba *Vindi*.
1. Pronto! Agora é só efetuar a venda e a assinatura será registrada.
