# Documentação Enciclopédica do Cashew: product.md

## 1. Filosofia e Visão Geral Arquitetural Profunda

### Princípios Fundamentais
O Cashew foi concebido como um gerenciador financeiro FOSS (Free and Open Source Software) centrado no usuário e fortemente calcado nas diretrizes do Material You (Material Design 3). Sua filosofia baseia-se na *apropriação total dos dados* pelo usuário e *flexibilidade sem atrito*. Em vez de forçar os usuários em moldes contábeis restritos, ele adota uma arquitetura reativa que permite configurar desde carteiras multimoedas (Wallets/Accounts) até objetivos financeiros complexos (Objectives/Goals) com facilidade. A adoção extensiva de customização (temas dinâmicos, sotaques de cor personalizados e categorias flexíveis com ícones configuráveis) sublinha a convicção de que o rastreamento de despesas não deve ser uma tarefa árdua, mas uma extensão orgânica do cotidiano do usuário.

### Contexto Histórico e Rationale de Design
O desenvolvimento começou como uma alternativa aos aplicativos comerciais de finanças inflados e invasivos em termos de privacidade. A arquitetura foi moldada por três restrições primárias:
1.  **Operação Offline-First:** O aplicativo deve ser plenamente funcional sem internet, armazenando todos os dados em um banco SQLite local via Drift.
2.  **Abstração de Plataforma:** Construído em Flutter, a lógica de negócios foi isolada da interface do usuário para compilar transparentemente para Web (PWA), Android, iOS e potencialmente desktop.
3.  **Privacidade e Sincronização Descentralizada:** Em vez de depender de servidores centrais e arriscados, o sistema aproveita o Google Drive do usuário (via `googleapis`) para realizar backups de ponta a ponta e sincronização peer-to-peer simulada, mantendo o controle sob a infraestrutura pessoal.

### Arquitetura de Valor
A proposta de valor principal está na *Automação e Consistência Intuitiva*. O banco de dados incorpora "Títulos Associados" (`AssociatedTitles`) que mapeiam strings transacionais automaticamente para categorias com base em histórico ou regras flexíveis. Isso resolve o atrito da entrada manual, ao mesmo tempo em que preserva a integridade taxonômica de relatórios gráficos precisos gerados via `fl_chart`.

---

## 2. Jornadas do Usuário Analíticas

### 2.1 Adição de Transação e Mapeamento Inteligente
- **Micro-interações:** O fluxo começa no botão FAB (Floating Action Button), abrindo uma folha modal inferior de entrada rápida. A interface exige valor monetário, categoria e conta (`WalletPk`).
- **Lógica de Negócio Subjacente:** Assim que uma transação recebe um título ("Starbucks"), o sistema consulta de forma assíncrona o cache de `AssociatedTitles`. Caso exista correspondência, autocompleta a categoria e potencialmente a conta padrão.
- **Fluxos de Dados:** A entrada é validada e instanciada como uma entidade de tabela `Transactions`. A conversão de moeda é avaliada on-the-fly, referenciando as taxas de câmbio pré-cacheadas em configurações globais, registrando tanto o montante original quanto o convertido se houver discrepância entre as moedas da transação e da carteira selecionada. O estado local (Provider ou ValueNotifiers) emite eventos para recarregar o widget da tela inicial.

### 2.2 Gerenciamento de Metas (Objectives/Goals) e Empréstimos (Long Term Loans)
- **Lógica de Negócio (O Desvio de Polariade):** No Cashew, um empréstimo concedido (Lent) de \$100 é rastreado como um "Objetivo". A inserção inicial não incrementa o montante "guardado" da meta de forma convencional. Em vez disso, o sistema registra uma despesa de \$100. Quando o mutuário paga, isso é inserido como um rendimento positivo e abatido da meta original.
- **Fluxos de Dados:** Uma query no banco agrega as transações pertencentes à chave primária de um `Objective` específico. A polaridade da transação original define se o progresso deve ser lido inversamente, garantindo que "Total do Empréstimo - Total Pago" sempre renderize o "Valor Restante" com precisão.

### 2.3 Orçamentos (Budgets) e Limites de Categoria
- **Micro-interações:** Os usuários criam orçamentos cíclicos (semanais, mensais, customizados). Podem anexar categorias específicas a este orçamento geral.
- **Lógica de Negócio:** A Engine de orçamento é proativa. Não se trata apenas de somar gastos, mas de projetar gastos ao longo de um ciclo. O aplicativo usa a tabela `CategoryBudgetLimits` para intersetar os gastos de uma categoria e as restrições globais de um orçamento particular, mudando as cores de estado das barras de progresso (`budget/packages/sa3_liquid` e `fl_chart`) quando os limites se aproximam.

---

## 3. Módulos Core e Mecanismos Internos Exaustivos

### 3.1 ORM e Persistência de Dados (Drift/SQLite)
O banco de dados é um sistema relacional modelado pelo `drift`. Diferente de NoSQL, a integridade é assegurada por foreign keys lógicas mantidas pelo ORM em `tables.dart`.
- **Tabelas Principais:**
  - `Transactions`: Coração do app. Chaves primárias usam UUIDv4 para evitar conflitos na sincronização entre dispositivos, em vez de auto-incrementos que colidiriam fatalmente.
  - `Wallets` (Accounts no UI): Armazena saldos e moedas preferidas.
  - `Categories`: Taxonomia dos gastos com referências visuais em `iconObjects.dart`.
  - `Objectives` (Goals no UI): Metas ou rastreamentos de empréstimos.
  - `Budgets`: Contêineres de limite de tempo/montante.
  - `DeleteLogs`: O mecanismo *Crucial* para o motor de Sincronização.

### 3.2 Sincronização Descentralizada (O Motor Google Drive em `syncClient.dart`)
Como o Cashew opera sem um servidor de estado global dedicado, o sistema de sync é engenhoso e robusto.
- **Mecanismo:** Usa-se um modelo de Arquitetura de Sincronização Reativa em Nuvem Pessoal.
  1. Cada dispositivo tem um `clientID` único.
  2. Cada alteração local (Insert, Update, Delete) registra um `SyncLog` local e atualiza a timestamp modificado. (Para Deleções, anotações vão para `DeleteLogs`).
  3. Durante a sincronização, o app baixa os backups de banco de dados (`sync-{clientID}.sqlite`) dos *outros* dispositivos usando `googleapis/drive`.
  4. O app lê esses bancos como instâncias Drift em memória ou anexadas e extrai registros novos com base na data de `lastSynced` local.
  5. Os registros mais recentes ganham. Conflitos (baseados nos UUIDs de transação) são resolvidos usando metadados de modificação (Data-Last-Modified).
  6. Um novo banco unificado é submetido ao Drive como `sync-{meuClientID}.sqlite`.

### 3.3 Motor de Agendamento, Automação e Fuso Horário
- **Componentes:** `flutter_timezone`, `timezone/data/latest_all.dart`.
- O processamento de transações repetitivas e lembretes é administrado usando `flutter_local_notifications` acoplado com callbacks nativos de reinício (`onAppResume.dart`) que varrem o banco de dados por transações futuras ou cíclicas que ultrapassaram a data de maturidade e processam as cópias necessárias.

---

## 4. Gerenciamento de Erros e Cenários Adversos Detalhados

### 4.1 Falha na Sincronização P2P (Google Drive Timeout/Rate Limits)
- **Cenário:** O usuário não tem rede, ou o token de autorização do Google SignIn expirou de forma silenciosa.
- **Resiliência:** O `syncClient.dart` captura falhas de token e solicita silenciosamente atualização ou rebaixa o sistema graciosamente para o "Modo Offline". Operações de sincronização têm `try/catch` massivos no stream do Google Drive, não corrompendo a thread da UI principal, e logando a falha em estado global via `loadingIndeterminateKey.currentState?.setVisibility(false)`. O banco local atua como a única fonte da verdade e acumula deltas (`DeleteLogs` intactos) até que a conexão seja restabelecida.

### 4.2 Colisões de Dados ou Corrupção (Drift Migrations)
- **Cenário:** Atualização do aplicativo envolvendo alterações de esquema (`tables.dart` bumps para `schemaVersionGlobal + 1`).
- **Resiliência:** Migrações são estritas. Arquivos JSON de dump (`drift_schema_v[X].json`) mapeiam o antes e depois. `schema_versions.dart` fornece upgrades step-by-step (`await stepByStep(...)`). Se a migração falhar no meio devido a limites do dispositivo, o processo Drift isola a operação numa transação atômica que fará rollback completo, exibindo ao usuário uma mensagem codificada de proteção e impedindo o acesso falho e leitura de estado inconsistente.

### 4.3 Arquivos Corrompidos de Backup (SQLite Binário)
- **Mitigação:** Como `kIsWeb` lida com codificações base64 via bin2str diferentemente dos File Systems móveis, a lógica extrai e checa a integridade do cabeçalho binário do SQLite antes da inserção de fallback. Exceções jogam avisos visuais explícitos `"syncing-failed"` traduzidos do `easy_localization`.

---

## 5. Proficiência Avançada e Masterização Completa

### Configurações Avançadas de Sistema
- **Tags de Inicialização para Debug:** Em `main.dart`, constantes como `allowDangerousDebugFlags = kDebugMode` ativam logs avançados e o `DevicePreview` para arquitetos UI.
- **App Links:** Para Power Users e integração com ferramentas como o Tasker (Android) ou Siri Shortcuts (iOS), o Cashew intercepta URIs personalizadas usando o pacote `app_links` (`widgets/util/appLinks.dart`). É possível injetar transações inteiras via URI preenchendo valor, categoria e data automaticamente.

### Otimização e Performance
- **Tuning de Banco de Dados:** Grandes conjuntos de dados (anos de transações) requerem renderização fluida em listas. O Cashew usa paginação implícita e chaves imutáveis em pacotes modificados como `implicitly_animated_reorderable_list` e construtores `ListView.builder` para renderização em janela (windowed rendering) garantindo 60fps constantes mesmo com >10,000 transações.
- **Limitações Conhecidas:** A sincronização via Google Drive sem um backend mestre falha se dois dispositivos offline fizerem modificações sobre o **mesmo** UUID simultaneamente (não operações de append, mas de mutação direta). O mecanismo resolverá com base na última marca de tempo recebida pelo host.

### Roadmap de Evolução da Arquitetura
1.  **Refatoração do Sync Engine:** Migrar do polling manual do Google Drive em resumos assíncronos para um modelo CRDT (Conflict-free Replicated Data Type) verdadeiro, possivelmente incorporando um provider de storage agnóstico em nuvem (S3 via API compatível).
2.  **Plugin Architecture:** Extensão do `ScannerTemplates` para permitir parsing avançado via Regex em emails, importando PDFs de faturas bancárias integralmente usando motores WASM (WebAssembly) nativos do Dart 3+.
3.  **Analytics Isolada:** Otimizar e expandir o módulo financeiro `fl_chart` para processamentos isolados em isolados em threads de background de Dart, reduzindo a carga do main isolate na geração dos relatórios anuais que requerem milhares de iterações em objetos `TransactionWithCategory`.
