# Cashew: Manifesto Arquitetural e Guia Definitivo de Onboarding

**Autor:** Arquiteto Fundador
**Escopo:** Diretrizes Imutáveis, Topologia do Sistema e Domínio de Negócio

Este documento consolida o conhecimento arquitetural adquirido ao longo do desenvolvimento do Cashew. Ele foi projetado para eliminar a curva de aprendizado de novos engenheiros seniores, transformando conhecimento legado em diretrizes acionáveis e imutáveis. Leia este manifesto não como um simples manual, mas como o DNA do nosso ecossistema.

---

## 1. Racional Estratégico e Gênese das Regras de Negócio

O Cashew nasceu de uma premissa clara: **Gestão financeira pessoal deve ser offline-first, resiliente e altamente customizável**. A dependência de conexões de rede constantes é um antipadrão para aplicativos de controle financeiro. Portanto, adotamos o Flutter para a interface adaptativa (Material You) e o **Drift (SQLite)** como nossa fonte de verdade primária (Single Source of Truth).

A lógica de negócios foi desenhada para flexibilidade extrema:
- **Separação de Preocupações (SoC):** A interface do usuário (Accounts/Goals) é deliberadamente dissociada da nomenclatura do banco de dados subjacente (Wallets/Objectives). Isso permite que a experiência do usuário evolua sem exigir migrações estruturais custosas no esquema do banco.
- **Sincronização Passiva vs. Ativa:** Operamos sob a premissa de que os dados locais são sempre os mais precisos. A sincronização em nuvem (via Google Drive/Firebase) atua como um mecanismo de *backup e restore*, não como um pipeline de bloqueio em tempo real.
- **Tipagem Estrita de Transações:** Para suportar a complexidade do mundo real (empréstimos, dívidas, assinaturas recorrentes), o motor financeiro classifica transações além do binário "Receita/Despesa". Transações são vetores polares que afetam não apenas o saldo de uma carteira (`Wallet`), mas também o progresso de um objetivo (`Objective`).

---

## 2. Mapeamento de Processos de Ponta a Ponta

O ciclo de vida dos dados, da ingestão ao output, segue um pipeline determinístico:

### A. Ingestão de Dados (Ingress)
1. **Entrada Manual / App Links / CSV / Google Sheets:** O usuário ou sistema injeta dados brutos.
2. **Parsing e Categorização Automática:** Através da tabela `AssociatedTitles`, transações com nomes reconhecidos são mapeadas automaticamente para `Categories` específicas (redução de atrito cognitivo).
3. **Validação e Polarity Assignment:** O sistema define o sinal (positivo/negativo) baseado na categoria (`income` boolean) e no tipo especial (`TransactionSpecialType`).

### B. Persistência (Storage Layer)
1. **Geração de UUID:** Toda entidade (Transaction, Wallet, Budget, Category) recebe um PK (Primary Key) do tipo UUID v4 client-side, garantindo unicidade global em cenários de sincronização descentralizada (evitando conflitos de chaves incrementais).
2. **Commit via Drift:** Os dados são persistidos no SQLite (`FinanceDatabase`). O esquema é rigidamente versionado (`schemaVersionGlobal`) com dumps exportados em JSON.

### C. Processamento (Engine)
1. **Cálculo de Orçamentos (Budgets):** O motor cruza transações com o período de tempo definido pelo orçamento (`startDate`, `endDate` ou recorrência) e filtra pelas `Wallets` e `Categories` associadas.
2. **Watchers e Streams:** O Flutter consome Streams do Drift. Quando uma transação é inserida, a UI reage instantaneamente em múltiplos pontos (Home, Gráficos, Orçamentos) sem necessidade de polling manual.

### D. Output e Sincronização (Egress)
1. **Renderização de Interface:** Componentes escutam as mudanças e desenham gráficos de fluxo, barras de progresso de orçamento e saldos de contas.
2. **Backup/Exportação:** O estado local é serializado e exportado em background, respeitando regras de privacidade (biometria via `initializeBiometrics.dart`).

---

## 3. Anatomia Detalhada de Módulos

O sistema é composto por módulos interdependentes. Qualquer alteração nestes pilares deve ser feita com extrema cautela.

### 3.1. Core Engine (Drift Database - `tables.dart`)
- **Transactions:** O coração do sistema. Contém UUID (`transactionPk`), UUID pareado (para transferências), valor, data e referências estrangeiras (FKs) para Category, SubCategory e Wallet.
- **Wallets (Accounts na UI):** Reservatórios de valor. Possuem moeda específica (Currency), saldo implícito (calculado pelas transações) e cor/ícone.
- **Categories:** Estrutura hierárquica. Se `mainCategoryPk` for nulo, é uma categoria mestre. Se preenchido, é subcategoria. A flag `income` inverte a polaridade financeira.
- **Budgets:** Limites de gastos temporais. Podem ser filtrados por múltiplas `Wallets` (`walletFks` armazenado via TypeConverter JSON) e possuem regras de recorrência complexas (`BudgetReoccurence`).
- **Objectives (Goals na UI):** Metas de economia ou pagamento. Integrados de forma assíncrona ao motor de transações.

### 3.2. Automation & Templates (Scanner)
- **ScannerTemplates:** Permite a varredura automática de textos (ex: emails bancários). O parser usa regex e delimitadores (`titleTransactionBefore`, `amountTransactionAfter`) para inferir valores, ignorar transações irrelevantes e associar automaticamente a `Wallets` e `Categories`.

### 3.3. Sync & Cloud (`syncClient.dart` / Firebase)
- Mecanismo de sincronização entre dispositivos cruzando dados locais (SQLite) com a nuvem. Evita perda de dados, porém prioriza a latência zero da interface offline.

---

## 4. Matriz de Casos Críticos e Exceções Históricas (Corner Cases)

A evolução do produto revelou cenários que desafiam a lógica ingênua. Respeite estas resoluções estabelecidas:

| Cenário (Corner Case) | Comportamento Padrão Ingênuo | Resolução Arquitetural (Implementada) |
| :--- | :--- | :--- |
| **Empréstimos de Longo Prazo (Long Term Loans)** | Criar uma entidade separada para acompanhar quanto foi pago. | Empréstimos criam um **Objetivo (Objective)**. O total do objetivo *não* é armazenado no registro do objetivo. Em vez disso, é calculado somando a polaridade das transações do tipo oposto. Ex: Empréstimo de $100 (Expense, negativo). Pagamento de $20 (Income, positivo). O saldo é a soma ($-80). A UI cuida de exibir "Faltam $80". |
| **Transferências entre Contas** | Duas transações separadas (uma receita, uma despesa). | Uso do campo `pairedTransactionFk`. Uma transação de despesa em Wallet A tem o FK apontando para a receita em Wallet B. A deleção/edição atualiza ambas em cascata. |
| **Mudança de Plataforma (Web vs Mobile)** | `Platform.isAndroid` lança exceção na Web. | Sempre utilize a função encapsulada `getPlatform()` definida em `functions.dart`. O roteamento usa `pushRoute` para gerenciar as diferenças entre mobile `PageRouteBuilder` e rotas Web. |
| **Migrações de Schema de Banco de Dados** | Modificar a tabela e esperar que o SQLite adapte. | Processo estrito: 1. Aumentar `schemaVersionGlobal`. 2. Rodar `build_runner`. 3. Fazer dump do schema via `drift_dev`. 4. Gerar steps de migração. 5. Escrever a lógica em `stepByStep()` no `tables.dart`. Falhar neste fluxo corrompe a base instalada. |

---

## 5. Dicionário de Domínio e Proficiência Operacional

A linguagem cria a realidade no nosso código. Use estas definições rigorosamente em discussões técnicas e revisões de código.

*   **Wallet (Account):** O termo técnico no backend/database é *Wallet*. Na UI (Interface de Usuário), é chamado de *Account*. Nunca tente renomear a tabela do banco; traduza apenas na camada de apresentação.
*   **Objective (Goal):** O termo técnico no backend/database é *Objective*. Na UI, é *Goal*. Mesma regra das Wallets.
*   **Polarity (Polaridade):** O comportamento matemático de uma transação. A polaridade (se o valor diminui ou aumenta o saldo líquido) não é definida estritamente por um sinal de "menos" ou "mais", mas pela combinação de `Categories.income` e `TransactionSpecialType`.
*   **TransactionSpecialType:** Enumerador vital que rege o ciclo de vida.
    *   `upcoming`: Transação projetada no futuro. Não afeta saldos atuais.
    *   `subscription`/`repetitive`: Gera novas transações automaticamente baseada em cron jobs internos / inicialização.
    *   `credit`/`debt`: Define se o dinheiro saiu mas nos devem (credit) ou entrou mas devemos (debt).
*   **Client-side UUID:** Mecanismo de chaves primárias. Nunca use inteiros incrementais para IDs que trafegam ou podem ser sincronizados.

---

### Reflexão Final

A arquitetura do Cashew não é um acidente, mas o resultado de um refinamento iterativo impiedoso contra atritos de UX e limitações técnicas. Ao contribuir para este repositório, você herda a responsabilidade de manter a resiliência offline, a clareza da separação de estado e o respeito absoluto aos dados financeiros do usuário.

Bem-vindo a bordo. Modifique o que for necessário, construa novas fundações, mas respeite as leis da física que já estabelecemos.
