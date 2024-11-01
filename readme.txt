=== Virtuaria - Pagseguro Pix para Woocommerce ===
Contributors: tecnologiavirtuaria
Tags: payment, payment method, pagseguro, pix, woocommerce, gateway
Requires at least: 4.7
Tested up to: 6.0.1
Stable tag: 1.0.1
Requires PHP: 7.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adiciona o Pagseguro Pix como método de pagamento para o Woocommerce.

== Description ==

**Atenção:** Esse plugin não será mais atualizado porque a funcionalidade "Pagamento via Pix" foi incorporado a nosso outro plugin de integração com o PagSeguro que usa a API mais moderna de cobrança disponibilizada pelo PagSeguro e permite pagamentos via cartão de crédito, boleto bancário e Pix.
Para efetuar o download acesse [Virtuaria PagSeguro](https://wordpress.org/plugins/virtuaria-pagseguro/). Será necessário uma nova validação junto ao PagSeguro. A aba [instalação](https://wordpress.org/plugins/virtuaria-pagseguro/#installation) da página do plugin mostra como fazer. O processo é bem mais simples do que era antes, bastando clicar em conectar e conceder as permissões de acesso ao plugin.


Utilizando a API Pix disponibilizada pelo pagseguro, este plugin tem alta performance para processar seu pagamento e agilizar suas vendas.

* Fácil de instalar e configurar;
* Reembolso (total e parcial);
* Checkout Transparente, onde faz o pagamento sem sair do site;
* Relatório (log) para consulta a detalhes de transações, incluindo erros;
* Mudança automática dos status dos pedidos (aprovado, negado, cancelado, etc) via Webhook de retorno de dados dos status no Pagseguro;
* Detalhamento nas notas do pedido das operações ocorridas durante a comunicação com o PagSeguro (reembolsos, mudanças de status e valores recebidos/cobrados);
* Tempo limite para pagamento configurável;
* Nova cobrança Pix, muito útil para cobrança de valores extras ou nos casos onde o cliente perde o tempo limite de pagamento;
* Pagamento por QR code ou link de pagamento;
* Exibe os dados de pagamento no e-mail enviado e na tela de confirmação do pedido.

Com este plugin você poderá fazer reembolsos totais e parciais através da página de gerenciamento do pedido em sua loja.

O plugin conta com a funcionalidade “Cobrança Extra” que permite cobrar um valor extra em pedidos feitos com Pix. Esta função pode ser útil, por exemplo, para vendas de produtos no peso, pois neste caso o valor final quase sempre é diferente do inicialmente solicitado, algo muito comum em supermercados. Também é útil para os casos onde o cliente solicita a inclusão de novos itens no pedido ou nos casos onde o cliente perde o tempo limite de pagamento.

Observação: [PagSeguro Pix](https://pagseguro.uol.com.br/) é um método de pagamento brasileiro desenvolvido pela UOL. Este plugin foi desenvolvido, sem nenhum incentivo do PagSeguro ou da UOL, a partir da [documentação oficial do PagSeguro](https://dev.pagseguro.uol.com.br/reference/pix-intro) e utiliza a última versão ( 2.1.0 ) da API. Nenhum dos desenvolvedores deste plugin possui vínculos com o Pagseguro ou UOL.
 
Todas as compras são processadas utilizando o checkout transparente:
- **Transparente:** O cliente faz o pagamento direto no seu site sem precisar ir ao site do PagSeguro.

[youtube https://www.youtube.com/watch?v=mCLBTuoHa44]

**Observação:** Os prints foram feitos em um painel wordpress/woocommerce personalizado pela Virtuaria objetivando otimizar o uso em lojas virtuais, por isso o fundo verde.

**Para mais informações, acesse** [virtuaria.com.br - desenvolvimento de plugins, criação e hospedagem de lojas virtuais](https://virtuaria.com.br/) ou envie um email para tecnologia@virtuaria.com.br


= Compatibilidade =

Compatível com Woocommerce 5.8.0 ou superior

### Descrição em Inglês: ###

Using the Pix API provided by pagseguro, this plugin has high performance to process your payment and speed up your sales.

* Easy to install and configure;
* Refund (full and partial);
* Transparent Checkout, where you make the payment without leaving the site;
* Report (log) to query transaction details, including errors;
* Automatic change of order status (approved, denied, cancelled, etc.) via Webhook to return status data in Pagseguro;
* Details in the order notes of operations that occurred during communication with PagSeguro (refunds, status changes and amounts received/charged);
* Configurable payment timeout;
* New Pix charge, very useful for charging extra amounts or in cases where the customer misses the payment time limit;
* Payment by QR code or payment link;
* Displays payment details in the email sent and on the order confirmation screen.


== Installation ==

= Instalação do plugin: =

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.
* Navegue para Woocommerce -> Configurações -> Pagamentos, escolha o “Virtuaria Pagseguro Pix” e preencha as configurações.

Para utilizar nosso plugin em produção, é preciso solicitar homologação junto a equipe do pagseguro pelo link abaixo. A homologação consiste em enviar logs do plugin para equipe do PagSeguro, o que trás mais segurança quanto ao correto funcionamento do módulo.

[Formulário de solicitação de homologação](https://app.pipefy.com/public/form/2e56YZLK)

= Requerimentos: =

1- Conta no [PagSeguro](http://pagseguro.uol.com.br/);
2 - Ter instalado o [WooCommerce](http://wordpress.org/plugins/woocommerce/);
3 - Ter instalado o [Virtuaria PagSeguro](https://wordpress.org/plugins/virtuaria-pagseguro/).

= Configurações no PagSeguro: =

**Apenas com isso já é possível receber os pagamentos e fazer o retorno automático de dados.**

<blockquote>Atenção: Não é necessário configurar qualquer URL em "Página de redirecionamento" ou "Notificação de transação", pois o plugin é capaz de comunicar o PagSeguro pela API quais URLs devem ser utilizadas para cada situação.</blockquote>

= Configurações do Plugin: =

1 - Com o plugin instalado acesse o admin do WordPress e entre em "WooCommerce" > "Configurações" > "Pagamentos" > "PagSeguro Pix".

Pronto, sua loja já pode receber pagamentos pelo PagSeguro.

### Instalação e configuração em Inglês: ###

* Upload plugin files to your plugins folder, or install using WordPress built-in Add New Plugin installer;
* Activate the plugin;
* Navigate to WooCommerce -> Settings -> Payment Gateways, choose PagSeguro and fill in your options.


== Frequently Asked Questions ==

= Qual é a licença do plugin? =

Este plugin está licenciado como GPLv3.

= O que eu preciso para utilizar este plugin? =

* Ter instalado uma versão atual do plugin WooCommerce.
* Ter instalado uma versão atual do plugin Virtuaria PagSeguro.
* Possuir uma conta no PagSeguro.
* Gerar um token de segurança no PagSeguro.

= PagSeguro recebe pagamentos de quais países? =

No momento o PagSeguro recebe pagamentos apenas do Brasil e utilizando o real como moeda.

= Como que o plugin faz integração com PagSeguro? =

Fazemos a integração baseada na documentação oficial do PagSeguro que pode ser encontrada nos "[guias de integração](https://dev.pagseguro.uol.com.br/reference/pix-intro)" utilizando a última versão da API Pix para pagamentos.

= O pedido foi pago e ficou com o status de "processando" e não como "concluído", isto está certo? =

Sim, por padrão em compras pagas o status do pedido muda automaticamente para processando, significa que pode enviar sua encomenda. Porém, definir o status como "concluído" é atribuição do lojista ao final do processo de venda.

Para produtos baixáveis a configuração padrão do WooCommerce é permitir o acesso apenas quando o pedido tem o status "Concluído", entretanto nas configurações do WooCommerce na aba *Produtos* é possível ativar a opção **"Conceder acesso para download do produto após o pagamento"** e assim liberar o download quando o status do pedido está como "processando".

Note que caso você esteja utilizando a opção de **sandbox**, é necessário usar as informações do ambiente de testes que podem ser encontrados em "[PagSeguro Sandbox > Dados de Teste](https://sandbox.pagseguro.uol.com.br/vendedor/configuracoes.html)".

Se você tem certeza que o Token e E-mail estão corretos você deve acessar a página "WooCommerce > Status do Sistema" e verificar se **fsockopen** e **cURL** estão ativos. É necessário procurar ajuda do seu provedor de hospedagem caso você tenha o **fsockopen** e/ou o **cURL** desativados.

Por último é possível ativar a opção de **Log de depuração** nas configurações do plugin e tentar novamente fechar um pedido (você deve tentar fechar um pedido para que o log grave o erro). Com o log é possível saber exatamente o que está dando errado com a sua instalação.

Caso você não entenda o conteúdo do log não tem problema, você pode me abrir um "[tópico no fórum do plugin](https://wordpress.org/support/plugin/virtuaria-pagseguro#postform)" com o link do log (utilize o [pastebin.com](http://pastebin.com).

= O status do pedido não é alterado automaticamente? =

Sim, o status é alterado automaticamente usando a API de notificações de mudança de status do PagSeguro.

A seguir uma lista de ferramentas que podem estar bloqueando as notificações do PagSeguro:

* Site com CloudFlare, pois por padrão serão bloqueadas quaisquer comunicações de outros servidores com o seu. É possível resolver isso desbloqueando a lista de IPs do PagSeguro.
* Plugin de segurança como o "iThemes Security" com a opção para adicionar a lista do HackRepair.com no .htaccess do site. Acontece que o user-agent do PagSeguro está no meio da lista e vai bloquear qualquer comunicação. Você pode remover isso da lista, basta encontrar onde bloquea o user-agent "jakarta" e deletar ou criar uma regra para aceitar os IPs do PagSeguro).
* `mod_security` habilitado, neste caso vai acontecer igual com o CloudFlare bloqueando qualquer comunicação de outros servidores com o seu. Como solução você pode desativar ou permitir os IPs do PagSeguro.

= Funciona com o Sandbox do PagSeguro? =

Sim, funciona e basta você ativar isso nas opções do plugin, além de configurar o seu seu token gerado para o ambiente de testes.

= Quais URLs eu devo usar para configurar "Notificação de transação" e "Página de redirecionamento"? =

Não é necessário configurar qualquer URL para "Notificação de transação" ou para "Página de redirecionamento", o plugin já diz para o PagSeguro quais URLs serão utilizadas.

= Este plugin permite o reembolso total e parcial da venda? =

Sim, você pode reembolsar pedidos com status processando indo direto a página do pedido no woocommerce e clicar em Reembolso -> Reembolso via Virtuaria Pagseguro Pix e setar o valor seja ele total ou parcial.

= Erro 403 ao utilizar o plugin em produção =

Para utilizar o plugin em produção é preciso solicitar liberação feita pela equipe do PagSeguro. O processo é simples e pode ser feito via "[Solicitar Homologação](https://dev.pagseguro.uol.com.br/reference/request-approval)".


= Quais valores meus clientes podem pagar com este plugin?  =

Não há valores máximos para as vendas, porém existe o mínimo de R$1,00 a serem transacionado com o pagseguro.

### FAQ em Inglês: ###

= What is the plugin license? =

* This plugin is released under a GPLv3 license.

= What is needed to use this plugin? =

* WooCommerce version 4.5 or later installed and active.
* Only one account on [PagSeguro](http://pagseguro.uol.com.br/).

== Screenshots ==

1. Configurações do plugin;
2. Checkout transparente;
3. QR Code;
4. Reembolso bem sucedido;
5. Cobrança adicional;
6. E-mail de novo pedido.


== Upgrade Notice ==
Nenhuma atualização disponível

== Changelog ==
= 1.0.0 2022-10-21 =
* Versão inicial.
= 1.0.1 2023-01-09 =
* Bug fixes.
