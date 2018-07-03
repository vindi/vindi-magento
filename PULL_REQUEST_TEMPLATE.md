## Requisitos de um bom Pull Request para o vindi-magento

:clap::grin: Antes de mais nada, **muito obrigado** por seu **Pull Request (PR)**  :thumbsup:

- **Branches separadas** - Recomendamos que o PR não seja a partir da sua branch `master`.

- **Um PR por recurso** - Se você deseja ajudar em mais de uma coisa, envie múltiplos PRs.

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

- **Fragmentação** - Quando um PR for parte de uma tarefa e não entregar valor de forma isolada, será necessário explicitar na motivação quais são os objetivos da tarefa, e na solução proposta, os objetivos que foram concluídos no PR em questão e os que serão concluídos em PRs futuros.
