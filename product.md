# Cashew: Enciclopédia e Wiki Completa do Sistema

## 1. Filosofia e Visão Geral Arquitetural Profunda

### 1.1 Princípios Fundamentais
O Cashew foi concebido sob a premissa de que a gestão financeira pessoal deve ser uma extensão da soberania do indivíduo sobre seus próprios dados. Diferente de soluções baseadas inteiramente na nuvem (SaaS convencionais), o Cashew adota uma abordagem **Local-First**. Isso significa que a experiência primária, a integridade dos dados e a performance não dependem de uma conexão estável com a internet.

**Os pilares da filosofia Cashew são:**
*   **Privacidade por Design:** Os dados financeiros são sensíveis. O sistema prioriza o armazenamento local e utiliza backups criptografados no Google Drive do próprio usuário, eliminando intermediários no processamento de informações privadas.
*   **Flexibilidade Granular:** O sistema não impõe um método rígido (como o "envelope system" puro). Em vez disso, fornece camadas de abstração (Contas, Categorias, Orçamentos, Metas) que podem ser combinadas para suportar desde orçamentos domésticos simples até gestões complexas multi-moeda e multi-conta.
*   **Resiliência e Longevidade:** Ao utilizar SQLite como motor de persistência, o Cashew garante que os dados sejam portáveis e acessíveis mesmo após décadas, seguindo padrões abertos de indústria.

### 1.2 Contexto Histórico e Evolução
O desenvolvimento iniciou em setembro de 2021, evoluindo de um simples rastreador de despesas para um ecossistema completo de automação financeira. A transição de "Wallets" para "Accounts" e de "Objectives" para "Goals" na interface reflete uma maturidade no entendimento da jornada do usuário, simplificando a nomenclatura técnica para conceitos familiares sem perder a robustez interna.

### 1.3 Rationale de Design e Arquitetura de Valor
A arquitetura do Cashew é construída sobre o framework **Flutter**, permitindo uma interface adaptativa e de alta performance (60/120fps) em Android, iOS e Web.

#### Camada de Persistência (Drift/SQLite)
O uso do pacote **Drift (antigo Moor)** é a espinha dorsal do sistema. Ele fornece:
*   **Tipagem Estática para SQL:** Reduzindo erros em tempo de execução ao lidar com queries complexas.
*   **Streams Reativas:** A interface do usuário reage instantaneamente a mudanças no banco de dados, eliminando a necessidade de "pull-to-refresh" para ver atualizações de saldo.
*   **Migrações Estruturadas:** Com mais de 46 versões de esquema documentadas em `tables.dart`, o sistema demonstra uma capacidade robusta de evolução sem perda de dados para o usuário final.

#### Camada de Sincronização e Nuvem
O Cashew utiliza uma arquitetura híbrida para sincronização:
1.  **Google Drive (SyncClient):** Utilizado para backups completos do banco de dados SQLite. É a solução de recuperação de desastres e sincronização entre dispositivos pessoais do mesmo usuário.
2.  **Firebase Firestore (ShareBudget):** Uma camada de "Realtime Data" usada especificamente para **Orçamentos Compartilhados**. Aqui, o sistema opera de forma diferente: apenas as transações vinculadas ao orçamento compartilhado são enviadas para a nuvem, mantendo o restante do banco de dados estritamente local.

### 1.4 Princípios Operacionais (Arquitetura de Valor)
O valor do sistema é gerado através da **transformação de dados brutos em insights**. A lógica de "Polaridade de Transação" (Expense/Income) integrada com "Conversão de Moeda em Tempo Real" permite que um usuário veja seu patrimônio líquido consolidado em uma moeda base, independentemente de onde o dinheiro esteja fisicamente armazenado.

## 2. Jornadas do Usuário Analíticas

### 2.1 O Ciclo de Vida da Transação
A jornada básica de entrada de dados é otimizada para ser realizada em poucos segundos, mas esconde uma lógica complexa de processamento.

#### Etapa 1: Inserção e Validação
Ao inserir uma transação, o sistema verifica:
*   **Polaridade:** Determina se o valor é positivo (Income) ou negativo (Expense) baseado no tipo de categoria ou seleção manual.
*   **Associação Inteligente (Associated Titles):** O sistema busca por padrões de nomes (`AssociatedTitles`) no banco de dados para sugerir automaticamente a categoria correta, reduzindo a fricção cognitiva do usuário.

#### Etapa 2: Atribuição e Contexto
Uma transação no Cashew não é apenas um valor. Ela pode ser vinculada a:
*   **Contas (Wallets):** Onde o saldo físico reside.
*   **Orçamentos (Budgets):** Onde o limite de gastos é monitorado. Uma transação pode pertencer a vários orçamentos se eles tiverem períodos ou categorias sobrepostas.
*   **Metas (Goals/Objectives):** Contribui para o progresso de uma reserva financeira ou quitação de dívida.

#### Etapa 3: Micro-Interações de Manutenção
*   **Swipe-to-Edit/Delete:** Gestos nativos permitem a gestão em massa de transações.
*   **Marcar como Pago (Mark as Paid):** Essencial para transações futuras ou assinaturas. Quando o usuário marca uma assinatura como paga, o sistema gera automaticamente a próxima ocorrência baseada no `periodLength` e `reoccurrence`, garantindo continuidade.

### 2.2 Gestão de Orçamentos (Budgets)
A jornada de planejamento financeiro é dividida entre orçamentos estáticos e dinâmicos.

#### Orçamentos Estáticos (Mensais/Periódicos)
O usuário define um teto de gastos. O sistema calcula o "Budget Status" em tempo real:
*   **Cálculo de Projeção:** Baseado na velocidade de gastos e no tempo restante do período.
*   **Limites por Categoria:** O sistema permite granularidade, onde um orçamento maior pode ter sub-limites para categorias específicas (ex: "Alimentação" dentro do orçamento "Mensal").

#### Orçamentos Dinâmicos (Custom)
Úteis para viagens ou eventos específicos. A jornada foca no acompanhamento de gastos acumulados sem a rigidez de um calendário fixo.

### 2.3 Gestão de Dívidas e Empréstimos (Credit/Debt)
Diferente de transações comuns, a jornada de empréstimos (Lent/Borrowed) é bidirecional:
1.  **Criação:** Registro do valor inicial e da contraparte.
2.  **Monitoramento:** O sistema mantém o saldo "aberto".
3.  **Liquidação:** O processo de "marcar como coletado" ou "liquidado" gera uma transação de compensação que zera o saldo no consolidado, mantendo o histórico de fluxo de caixa.

### 2.4 Automação e Importação
Para usuários avançados, a jornada de entrada de dados é automatizada via:
*   **Importação de CSV e Google Sheets:** Processamento em lote de extratos bancários.
*   **App Links:** Integração via URL (deep linking) que permite que apps externos (como automações de atalhos) pré-preencham transações no Cashew.

## 3. Módulos Core e Mecanismos Internos Exaustivos

### 3.1 O Modelo de Dados (Schema Drift)
O Cashew utiliza uma estrutura relacional normalizada para garantir integridade. Os principais componentes são:

#### Wallets (Contas)
*   **Identificador:** `walletPk` (UUID).
*   **Atributos:** Nome, Cor, Ícone, Moeda e Formatação.
*   **Lógica Interna:** O sistema suporta múltiplas casas decimais (coluna `decimals`), permitindo o suporte a criptomoedas ou moedas de baixo valor unitário.

#### Transactions (Transações)
*   **Tipos Especiais (`TransactionSpecialType`):** `upcoming`, `subscription`, `repetitive`, `credit`, `debt`.
*   **Mecanismo de Recorrência:** Utiliza `periodLength` e `reoccurrence` (Daily, Weekly, Monthly, Yearly).
*   **Lógica de "Paid":** Transações recorrentes criam uma nova entrada "unpaid" assim que a atual é marcada como "paid", usando uma chave previsível (`updatePredictableKey`) para evitar duplicatas durante a sincronização entre dispositivos.

#### Budgets (Orçamentos)
*   **Flexibilidade de Filtros:** Um orçamento pode filtrar transações por categorias (`categoryFks`), exclusão de categorias (`categoryFksExclude`) ou contas específicas (`walletFks`).
*   **SharedBudgets:** Quando um orçamento tem um `sharedKey`, ele é espelhado no Firebase Firestore.

### 3.2 O Motor de Conversão de Moeda
Localizado em `currencyFunctions.dart`, este módulo é vital para o consolidado financeiro.
*   **Provedor de Taxas:** Utiliza a API `@fawazahmed0/currency-api` com fallback para caches locais.
*   **Cálculo Transversal:** Sempre que um saldo é exibido, o sistema percorre todas as contas, converte seus saldos para a moeda da conta selecionada (`selectedWalletPk`) e soma os valores. Isso é feito de forma eficiente através de streams.

### 3.3 Mecanismo de Sincronização (SyncClient)
O `SyncClient` gerencia a exportação e importação do banco de dados SQLite para o Google Drive.
*   **ClientID:** Cada dispositivo gera um ID único para evitar que um backup sobrescreva outro de um dispositivo diferente.
*   **SyncLogs:** O sistema rastreia mudanças via `DeleteLogs` e `dateTimeModified` para realizar "merges" inteligentes de dados novos vindos de outros dispositivos sem deletar dados locais.

### 3.4 Sistema de Notificações e Automação
*   **Background Tasks:** O sistema agenda notificações para transações futuras (`upcoming`) e vencimentos de assinaturas.
*   **Processamento de Importação:** O módulo de importação de CSV utiliza mapeamento dinâmico de colunas, permitindo que o usuário ensine ao Cashew como ler extratos de diferentes bancos.

## 4. Gerenciamento de Erros e Cenários Adversos Detalhados

### 4.1 Resiliência na Sincronização de Dados (Cloud/Sync)
Cenários como "Google Drive Inacessível" ou "Token Expirado" são tratados por:
*   **Timeout e Retry:** O `SyncClient` implementa um timer de timeout de 5 segundos para evitar bloqueios na interface.
*   **Cópia de Segurança Local (syncdb.sqlite):** Antes de realizar um merge de dados novos vindo da nuvem, o sistema cria uma cópia temporária do banco de dados recebido para validar se ele é um banco de dados SQLite válido antes de processar os `SyncLogs`.

### 4.2 Integridade do Esquema (Database Corruption)
*   **Detecção de Corrupção:** O sistema utiliza `DriftRemoteException` para capturar falhas no motor SQLite.
*   **Modo de Recuperação:** Se o banco de dados for detectado como corrompido, o Cashew entra em um estado onde permite ao usuário restaurar um backup anterior do Google Drive ou realizar um "clear data" para recomeçar, protegendo a experiência contra crashes infinitos.

### 4.3 Gestão de Erros em Transações Compartilhadas (Firestore)
*   **Fila de Pendências (`sendTransactionsToServerQueue`):** Se o usuário estiver offline ao editar um orçamento compartilhado, as mudanças são enfileiradas localmente nas configurações (`appStateSettings`).
*   **Sincronização de Fila:** Assim que a conexão é detectada ou o app é reiniciado, o sistema percorre a fila pendente (`syncPendingQueueOnServer`) para garantir que os outros membros do orçamento recebam a atualização.

### 4.4 Migrações de Esquema Falhas
Com 46 versões de esquema, o Cashew implementa estratégias de migração granulares (`onUpgrade`).
*   **Fallback Seguro:** Se uma coluna nova falhar ao ser criada (ex: se já existir por um backup de versão superior), o sistema captura o erro no bloco `try-catch` da migração, garantindo que o app continue funcionando.
*   **Fix Order Lógica:** Periodicamente, o sistema executa funções como `fixOrderBudgets`, `fixOrderCategories`, etc., para re-indexar ordens de exibição que possam ter se corrompido durante merges de sincronização.

## 5. Proficiência Avançada e Masterização Completa

### 5.1 Guia para Uso Expert

#### Filtros de Orçamento Avançados (`BudgetTransactionFilters`)
Dominar o sistema significa entender como combinar filtros em um orçamento:
*   **Excluir Categorias:** Útil para ver gastos "discrionários" (Excluir: Aluguel, Contas Fixas).
*   **Incluir Transferências:** Permite ver fluxos entre contas no contexto de um orçamento.
*   **Filtros de Membro:** Em orçamentos compartilhados, filtre gastos por quem os realizou.

#### Automação via App Links (Deep Linking)
O Cashew expõe uma API de entrada via URL: `https://cashewapp.web.app/add-transaction?amount=10&name=Cafe&category=Food`.
*   **Masterização:** Use o Apple Shortcuts ou Tasker no Android para automatizar entradas ao pagar com NFC ou receber SMS bancário.

#### Formatação Customizada de Números e Moedas
O usuário master pode configurar:
*   **Separadores:** Escolha entre ponto ou vírgula para decimais e milhares.
*   **Símbolo de Moeda:** Posicione o símbolo antes ou depois do valor, dependendo do padrão local.

### 5.2 Otimizações e Configurações de Performance
*   **Battery Saver Mode:** Desativa animações pesadas e sombras (`disableShadows`).
*   **High Refresh Rate:** O sistema tenta forçar a maior taxa de atualização disponível no hardware (`setHighRefreshRate`).

### 5.3 Limitações Conhecidas e Roadmap de Evolução
*   **Suporte a Imagens/Anexos:** Atualmente em desenvolvimento (`uploadAttachment.dart`), focado em recibos.
*   **Sincronização de Esquema em Tempo Real:** O Firestore é usado apenas para transações de orçamentos compartilhados, não para o esquema completo do app (que usa Google Drive), o que pode causar "delay de visão" entre dispositivos se o backup não for feito.

### 5.4 Filosofia de Longo Prazo
O Cashew continuará a ser uma ferramenta de **empoderamento pessoal**. O roadmap foca em maior automação via IA local (processamento de linguagem natural para entrada de dados) e integração profunda com ecossistemas de Open Banking, mantendo sempre a premissa de que **o usuário é o dono soberano de sua história financeira**.
