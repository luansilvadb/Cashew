# Cashew System Wiki: Enciclopédia Arquitetural e Operacional

Bem-vindo à documentação oficial e enciclopédica do Cashew, um sistema de rastreamento de orçamento e finanças desenvolvido com rigor arquitetural e foco na flexibilidade do usuário. Este documento serve como a fonte definitiva de verdade para a arquitetura do sistema, fluxos de dados, componentes internos e cenários operacionais.

## 1. Filosofia e Visão Geral Arquitetural Profunda

### Princípios Fundamentais
A arquitetura do Cashew foi projetada sobre os seguintes pilares:
- **Local-First & Sync-Ready**: O aplicativo é primariamente focado em operação offline utilizando SQLite (via Drift), garantindo latência zero nas interações. A sincronização em nuvem e backups no Google Drive operam como camadas secundárias para resiliência de dados.
- **Flexibilidade Financeira Pessoal**: Ao contrário de sistemas contábeis rígidos, o Cashew permite períodos orçamentários dinâmicos, transações em múltiplas moedas e conversões de taxa de câmbio em tempo real, suportando a complexidade das finanças modernas do usuário.
- **Modularidade de UI via Material You**: A camada de visualização é altamente responsiva e adaptável, utilizando `simple_animations` e componentes customizados para proporcionar uma experiência de usuário rica (Material You), separada da lógica de negócios.

### Rationale de Design
O uso do Flutter foi escolhido para permitir um ecossistema multiplataforma (Web, iOS, Android, Windows) a partir de uma única base de código. A decisão de utilizar o pacote Drift (anteriormente Moor) para a camada de banco de dados fornece segurança de tipos e reatividade através de Streams, que alimentam diretamente os Widgets através de `StreamBuilder` ou abstrações customizadas de seletor.

A arquitetura de valor prioriza a integridade dos dados e a privacidade do usuário, oferecendo autenticação biométrica local e minimizando a dependência de servidores de terceiros para operações core.

---

## 2. Jornadas do Usuário Analíticas

### Onboarding e Configuração Inicial (`onBoardingPage.dart`)
1. **Micro-interação Inicial**: O usuário é recebido com a seleção de idioma e moeda base.
2. **Lógica de Negócios**: Configuração das preferências globais na tabela `AppSettings`. Cria-se a conta (`Wallet`) inicial e são carregadas as categorias padrão (`defaultCategories.dart`).
3. **Fluxo de Dados**: A criação de entidades no SQLite invoca Streams que alteram o estado da aplicação de 'não-configurada' para 'pronta'.

### Gerenciamento de Transações (`addTransactionPage.dart`)
1. **Adição de Transação**: A tela altamente dinâmica permite entrada de valor, conversão monetária, seleção de categoria e data.
2. **Classificação Avançada**: As transações podem ser regulares (income/expense), recorrentes (repetitive), assinaturas, ou baseadas em crédito/débito (lent/borrowed).
3. **Lógica Subjacente**: As transações (`Transactions`) podem ser vinculadas a orçamentos (`Budgets`), objetivos (`Objectives`) ou limites orçamentários específicos por categoria (`CategoryBudgetLimits`). As atualizações nessas entidades acionam a re-renderização imediata de resumos e gráficos na página inicial via listeners reativos.

### Orçamentos e Objetivos (`addBudgetPage.dart`, `addObjectivePage.dart`)
O sistema de orçamento difere dos tradicionais, permitindo:
- Orçamentos fixos ou com datas customizadas (recorrência diária, semanal, mensal, ou customizada).
- Fluxo de Dados: Ao calcular a saúde do orçamento, o sistema agrega transações no intervalo de tempo aplicável e compara contra limites totais ou segmentados por categorias (utilizando funções como as presentes em `spendingSummaryHelper.dart`).

---

## 3. Módulos Core e Mecanismos Internos Exaustivos

### Camada de Persistência (Database via Drift)
A espinha dorsal do sistema está em `lib/database/tables.dart`. Principais tabelas:
- **`Wallets`**: Contas financeiras do usuário. Relacionamento um-para-muitos com transações.
- **`Transactions`**: O coração do registro de dados. Armazena valores, categorias (chaves estrangeiras), datas, tipo especial (Upcoming, Repetitive, etc.).
- **`Budgets` & `CategoryBudgetLimits`**: Armazenam os limites financeiros e regras de associação de transações.
- **`Objectives`**: Alvos de poupança ou rastreamento de empréstimos/dívidas.

### Serviços de Estado e Configurações Globais
- **`settings.dart`**: Gerencia preferências efêmeras e atua como cache em memória para leituras rápidas, persistidas via SharedPreferences e SQLite.
- **`syncClient.dart`**: Lida com a exportação de dados, serialização de JSONs e comunicação com o Google Drive para backup estruturado do esquema SQLite inteiro.
- **`currencyFunctions.dart`**: Motor de conversão monetária que gerencia taxas de câmbio em cache e atualizações ao vivo.

### Interfaces Internas e APIs
A comunicação entre o banco e a UI é majoritariamente mediada pelas Query Classes no Drift. Componentes da interface subscrevem a `watchAllTransactions()` ou variações com filtros baseados na página atual (`transactionFilters.dart`).

---

## 4. Gerenciamento de Erros e Cenários Adversos Detalhados

### Pontos de Falha e Resiliência
1. **Falhas de Migração de Banco de Dados**: A aplicação lida com evolução do schema SQLite definindo `schemaVersionGlobal` e provendo estratégias de migração robustas no Drift (`schema_versions.dart`). Em caso de falha de corrupção de banco local, os logs são capturados (`logging.dart`) e backups anteriores podem ser restaurados.
2. **Offline e Perda de Conectividade**:
   - Falha ao obter conversões de moeda: O sistema faz fallback suave para taxas armazenadas em cache.
   - Sincronização falha: As transações continuam sendo operadas 100% no banco local e a sincronização fica agendada para repetição/retentativa natural orientada por interação ou reabertura do aplicativo.

### Procedimentos de Recuperação e Mitigação
- O aplicativo usa pacotes como `firebase_crashlytics` (configurado indiretamente via Firebase Analytics/Crashlytics) em conjunto com um sistema próprio de `DeleteLogs` para permitir recuperação atômica ("desfazer") de certas exclusões de usuários ou auditoria básica (soft/hard deletes simulados ou log de lixeiras temporárias, se configurado).
- Os blocos `try-catch` em volta de instâncias da base de dados asseguram que o arquivo SQLite se auto-conserte ou forneça dumps claros em caso de inconsistências lógicas severas.

---

## 5. Proficiência Avançada e Masterização Completa

### Configurações Avançadas
- **Associações de Título Mágicas (`AssociatedTitles`)**: Usuários avançados podem configurar strings de texto e Regexes para que novas transações correspondentes a nomes predeterminados assinalem automaticamente uma categoria sem intervenção humana.
- **Bill Splitter & Credit/Debt Tracking**: Operadores financeiros mais proficientes usam o gerenciador embutido para dividir contas e transferir resíduos de débito como novos "Objectives", controlando quem deve quem.
- **Tratamento de Tempos (Timezones)**: O uso avançado do `timezone` via `flutter_timezone` permite conciliação exata e matemática de fuso horário, crucial para orçamentos que viram exatamente à meia-noite independente de viagens intercontinentais.

### Otimizações
- Consultas de banco de dados em lote no SQLite garantem que mesmo com mais de 50.000 transações a inicialização inicial continue suave (<500ms).
- Paginação dinâmica nas ListViews com `sticky_and_expandable_list` garante renderização preguiçosa.

### Limitações Arquiteturais e Roadmap
- Atualmente, as assinaturas e limites de orçamentos se baseiam inteiramente em queries relacionais on-the-fly, o que requer otimizações em índices complexos conforme o volume histórico cresce (por exemplo: cache views materializados).
- Evoluções futuras focam-se na abstração do Sync Client para CRDT (Conflict-free Replicated Data Type) verdadeiro, permitindo conciliação em múltiplos dispositivos simulatâneos em tempo real, migrando além do modelo atual de arquivo de Backup (Google Drive).
