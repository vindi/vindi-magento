# Contribuindo para o Vindi-Magento

:clap::grin: Antes de mais nada, muito obrigado por sua contribuição  :thumbsup:

Este projeto e todos os participantes estão sob o regimento do [**Código de Conduta Vindi**](CODE_OF_CONDUCT.md). Ao participar, espera-se que você mantenha este código.

[**Contribuições**](https://github.com/vindi/vindi-magento/projects) são **muito bem vindas** e serão totalmente [**creditadas**](https://github.com/vindi/vindi-magento/graphs/contributors).

Nós valorizamos muito as [**contribuições por Pull Requests (PR)**](https://github.com/vindi/vindi-magento/pulls) em [GitHub](https://github.com/vindi/vindi-magento), mas também adoramos [**sugestões de novas features**](https://github.com/vindi/vindi-magento/issues/new/choose). Por isso, fique à vontade para [**reportar um bug :rotating_light:**](https://github.com/vindi/vindi-magento/issues/new/choose) e também para [**parabenizar :tada: o projeto vindi-magento!**](https://github.com/vindi/vindi-magento/issues/new/choose)

## Requisitos de um bom Pull Request (PR) para vindi-magento

- **Branches separadas** - Recomendamos que o PR não seja a partir da sua branch `master`.

- **Um PR por feature** - Se você deseja ajudar em mais de uma feature, envie múltiplos PRs :grin:.

- **Clareza** - Além de uma boa descrição sobre a motivação e a solução proposta é possível incluir imagens ou animações que demonstrem quaisquer modificações visuais na interface. 

Exemplo de **Motivação** com uma **Solução Proposta**:
> Motivação

> Fazer com que o pedido seja cancelado, caso o pagamento seja reprovado na primeira tentativa (compras avulsas ou primeiro ciclo de uma assinatura), mas atualmente o cliente recebe a informação que o pedido foi registrado com sucesso, e posteriormente recebe a informação de falha no pagamento no Magento.

> Solução proposta

> Adicionar o cancelamento automático de faturas na Vindi após a recusa de uma transação no Magento, exceto: compras via Boleto ou pendente de revisões do Antifraude.

- **Foco** - Um PR deve possuir um único objetivo bem definido. Evite mais de um viés (bug-fix, feature, refactoring) no mesmo PR.

- **Formatação de código** - Não reformate código que não foi modificado. A reformatação de código deve ser feita exclusiva e obrigatoriamente nos trechos de código que foram afetados pelo contexto da sua alteração.
Obs.: Gostamos muito do [MEQP1](https://github.com/magento/marketplace-eqp):smile:

- **Fragmentação** - Quando um PR for parte de uma tarefa e não entregar valor de forma isolada, será necessário explicitar na motivação quais são os objetivos da tarefa, e na solução proposta, os objetivos que foram concluídos no PR em questão e os que serão concluídos em PRs futuros.

#### Se você nunca criou um Pull Request (PR) na vida, seja bem vindo :tada: :smile: [Aqui está um ótimo tutorial](https://egghead.io/series/how-to-contribute-to-an-open-source-project-on-github) de como enviar um.

1. Faça um [fork](http://help.github.com/fork-a-repo/) do projeto, clone seu repositório (fork):

   ```bash
   # Clone repositório (fork) na pasta corrente
   git clone https://github.com/<seu-username>/vindi-magento
   # Navegue ate a pasta recém clonada
   cd vindi-magento
   ```

2. Crie uma branch nova a partir da `master` que vai conter o "tipo/tópico" como nome da branch
- tipos: feature e fix

   ```bash
   git checkout -b feature/cria_metodo_pagamento
   ```

3. Faça um push da sua branch para seu repositório (fork) 

   ```bash
   git push -u origin feature/cria_metodo_pagamento
   ```

4. [Abra um Pull Request](https://help.github.com/articles/using-pull-requests/) com uma motivação e solução proposta bem claras.


# Qualidade do código
Para garantir a qualidade do código, a gente disponibiliza alguns testes funcionais e code style MEQP1 do magento em Vindi/Subscription/tests.
 
#### Se você nunca utilizou o composer na vida, seja bem vindo :tada: :smile: [Aqui está o link do composer](https://getcomposer.org/download/), depois instale as dependências do composer.json.

#### Se você nunca rodou testes funcionais com Selenium na vida, seja bem vindo :tada: :smile: [Aqui está um ótimo tutorial do framework de teste para o magento](https://magiumlib.com/) e você vai precisar do [java](https://www.java.com/pt_BR/download/) também.

## Configuração dos arquivos do framework de testes
1. Vindi/Subscription/tests/configuration/Magium/TestCaseConfiguration.php
```php
    <?php
    
    # Trocar o browser caso for utilizar outro
    $this->capabilities = \Magium\TestCaseConfiguration::CAPABILITIES_CHROME;
    
    # Trocar o dominio onde o selenium server estiver rodando
    $this->webDriverRemote = 'http://example:4444/wd/hub';
```

2. Vindi/Subscription/tests/configuration/Magium/Magento/Identities/Admin.php
```php
    <?php
    # Trocar o nome_usuario da adminstração do Magento
    $this->account = 'nome_usuario';
    
    # Trocar a senha da adminstração do Magento
    $this->password = 'senha';
```

3. Vindi/Subscription/tests/configuration/Magium/Magento/Identities/Customer.php
```php
    <?php
    # Trocar o e-mail do usuário
    $this->emailAddress = 'example@vindi.com.br';
    
    # Trocar a senha do usuário
    $this->password = 'senha';
```
 
4. Vindi/Subscription/tests/configuration/Magium/Magento/Themes/Admin/ThemeConfiguration.php
```php
    <?php
    # Trocar a URI da área adminstrativa do Magento
    $this->baseUrl = 'https://example/admin/';
```
 
5. Vindi/Subscription/tests/configuration/Magium/Magento/Themes/Magento19/ThemeConfiguration.php
```php
    <?php
    # Trocar a URI da área da loja
    $this->baseUrl = 'http://example/';
```
 
 
## Rodando os Testes

``` bash
composer test
```

## Verificando estilo do código

``` bash
composer style
```

## Corrigindo estilo do código

``` bash
composer fix
```

## Revisão da Comunidade

A revisão deve verificar se o PR atende aos requisitos abaixo, na ordem que são apresentados, e a decisão final ficaria com a 
equipe Vindi quanto à prioridade:

#### Correto

- O código realmente faz o que o autor está propondo?
- O tratamento de erros está adequado?

#### Seguro

- As modificações introduzem vulnerabilidades de segurança?
- Dados sensíveis estão sendo tratados da maneira correta?

#### Legível

- O código está legível?
- Métodos, classes e variáveis foram nomeadas apropriadamente?
- Os padrões definidos pelo projeto ou pela equipe estão sendo respeitados?

## 
**Feliz desenvolvimento!**
