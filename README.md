<p align="center"><img src ="https://vindi-blog.s3.amazonaws.com/wp-content/uploads/2017/10/logo-vindi-1.png" /></p>

##
# Vindi - Magento Recorrente

[![Licença do Software][badge-license]](LICENSE)
[![Última Versão no GitHub][badge-versionGitHub]][link-GitHub-release]
[![GitHub commits desde a última Versão][badge-versionGitHub-commits]][link-GitHub-release]

# Descrição
A integração do módulo da Vindi permite criação e gestão de planos e assinaturas através do Magento de forma transparente.

# Requisitos
- PHP **5.6.x** ou superior.
- cURL habilitado para o PHP.
- Magento Community Edition 1.7 a 1.9.2
- Certificado SSL.
- Conta ativa na [Vindi](https://www.vindi.com.br "Vindi").

# Instalação
Atualmente existem duas maneiras de instalar o Módulo Vindi Magento, a mais recomendada é através do [modgit](https://github.com/jreinke/modgit) pois instalando destá maneira é possível gerenciar facilmente o módulo e suas atualizações. A segunda maneira é através de um arquivo .zip onde é preciso inserir todos os arquivos do módulo nos diretórios da aplicação do Magento e todas as atualizações terão que ser realizadas manualmente.

#### - Via [modgit](https://github.com/jreinke/modgit)
1. Vá até o diretório base do Magento
1. Adicionar o módulo através do [modgit](https://github.com/jreinke/modgit)
```bash
modgit add vindi git@github.com:vindi/vindi-magento.git
```

# Atualização
#### - Via [modgit](https://github.com/jreinke/modgit)
1. Vá até o diretório base do Magento
1. Execute o comando abaixo
```bash
modgit update vindi
```

#### - Via .zip
1. Faça o download do [.zip](https://github.com/vindi/vindi-magento/archive/master.zip).
1. Extraia o conteúdo da pasta `src` em sua instalação do Magento.

# Remoção
#### - Via [modgit](https://github.com/jreinke/modgit)
1. Vá até o diretório base do Magento
1. Execute o comando abaixo
```bash
modgit remove vindi
```

# Configuração
1. Configurando sua conta Vindi
    - Em *Sistema -> Configuração -> Vindi > Vindi Assinaturas*  informe a chave da API de sua conta Vindi e salve.
    - Caso a conexão ocorra com sucesso, você verá um link para configuração dos *Webhooks*, que deve ser inserido no campo URL dentro do [painel da Vindi](https://app.vindi.com.br) em *Configurações -> Webhooks*.
1. Habilitando/Configurando os métodos de pagamento
    - Em *Sistema -> Configuração -> Sales > Formas de pagamento*, configure os métodos de pagamento **Vindi - Cartão de Crédito** ,  **Vindi - Boleto Bancário** e/ou **Vindi - Cartão de Débito**.
1. Criando produtos recorrentes
    - Em *Catálogo > Gerenciar Produtos*, adicione um produto e escolha o *Tipo de Produto* como **Assinatura Vindi**.
    - Na aba *Vindi* selecione o *Plano da Vindi* e associe a assinatura a um produto.

## Dúvidas
Caso necessite de informações sobre a plataforma ou API por favor siga através do canal [Atendimento Vindi](http://atendimento.vindi.com.br/hc/pt-br)

## Contribuindo
Por favor, leia o arquivo [CONTRIBUTING.md](CONTRIBUTING.md).

Caso tenha alguma sugestão ou bug para reportar por favor nos comunique através das [issues](https://github.com/vindi/vindi-magento/issues).

## Changelog
Tipos de mudanças
- **Adicionado** para novos recursos
- **Ajustado** para mudanças em recursos existentes
- **Depreciado** para recursos que serão removidos em breve
- **Removido** para recursos removidos
- **Corrigido** para correção de falhas
- **Segurança** em caso de vulnerabilidades

Todas as informações sobre cada release podem ser encontradas em [CHANGELOG.md](CHANGELOG.md).

## Créditos
- [Vindi](https://github.com/vindi)
- [Todos os Contribuidores](https://github.com/vindi/vindi-magento/contributors)

## Licença
GNU GPLv3. Por favor, veja o [Arquivo de Licença](LICENSE) para mais informações.

[badge-license]: https://img.shields.io/badge/license-GPLv3-blue.svg
[badge-versionGitHub]: https://img.shields.io/github/release/vindi/vindi-magento.svg
[badge-versionGitHub-commits]:  https://img.shields.io/github/commits-since/vindi/vindi-magento/latest.svg


[link-GitHub-release]: https://github.com/vindi/vindi-magento/releases
