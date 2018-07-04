# Contribuindo para o Vindi-Magento

:clap::grin: Antes de mais nada, muito obrigado por sua contribuição  :thumbsup:

[**Contribuições**](https://github.com/vindi/vindi-magento/projects) são **bem vindas** e serão totalmente [**creditadas**](https://github.com/vindi/vindi-magento/graphs/contributors).

Nós valorizamos muito as [**contribuições por Pull Requests (PR)**](https://github.com/vindi/vindi-magento/pulls) em [GitHub](https://github.com/vindi/vindi-magento), mas também adoramos [**sugestões de novas features**](https://github.com/vindi/vindi-magento/issues/new/choose) ou caso seja verificado a existência de um bug :beetle:, e queira [**reportar o bug :rotating_light:**](https://github.com/vindi/vindi-magento/issues/new/choose), ou até queira [**parabenizar :tada: o projeto vindi-magento**](https://github.com/vindi/vindi-magento/issues/new/choose)

## Requisitos de um bom Pull Request (PR) para vindi-magento

- **Branches separadas** - Recomendamos que o PR não seja a partir da sua branch `master`.

- **Um PR por feature** - Se você deseja ajudar em mais de uma feature, envie múltiplos PRs :grin:.

- **Clareza** - Além de uma boa descrição sobre a motivação e a solução proposta é possível incluir imagens ou animações que demonstrem quaisquer modificações visuais na interface. 

Exemplo de **Motivação** com uma **Solução Proposta**:
> Motivação

> - O modelo de negócio atual da Vindi, requer que caso o pagamento não seja aprovado na primeira tentativa (compras avulsas ou primeiro ciclo de uma assinatura), para que a fatura e o pedido sejam cancelados.
> - Porém, no Magento o cliente recebe a informação que o pedido foi registrado com sucesso, e posteriormente recebe a informação de falha no pagamento.

> Solução proposta

> - Adicionar o cancelamento automático de faturas na Vindi após a recusa de uma transação no Magento.
> - As compras via Boleto ou pendente de revisões do Antifraude não são canceladas automaticamente.

- **Foco** - Um PR deve possuir um único objetivo bem definido. Evite mais de um viés (bug-fix, feature, refactoring) no mesmo PR.

- **Formatação de código** - Não reformate código que não foi modificado. A reformatação de código deve ser feita exclusiva e obrigatoriamente nos trechos de código que foram afetados pelo contexto da sua alteração.
Obs.: Gostamos muito do [PSR-1](https://www.php-fig.org/psr/psr-1/) e [PSR-2](https://www.php-fig.org/psr/psr-2/) :smile:

- **Fragmentação** - Quando um PR for parte de uma tarefa e não entregar valor de forma isolada, será necessário explicitar na motivação quais são os objetivos da tarefa, e na solução proposta, os objetivos que foram concluídos no PR em questão e os que serão concluídos em PRs futuros.


## Revisão da Comunidade

A revisão deve verificar se o PR atende aos requisitos abaixo, na ordem que são apresentados e a decisão final ficaria com a 
Equipe Vindi quanto a prioridade.

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
