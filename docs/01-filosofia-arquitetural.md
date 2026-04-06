# Capítulo 1: Filosofia e Visão Geral Arquitetural Profunda

## 1. Contexto Histórico e Princípios Fundamentais

O Cashew não nasceu de um mero capricho de design, mas de uma necessidade premente por **autonomia de dados financeiros** em um ecossistema dominado por SaaS baseados em assinaturas e confinamento na nuvem (vendor lock-in). O princípio inegociável do sistema é ser **Local-First, Offline-Reliable**.  O usuário deve ter velocidade terminal de leitura/escrita, zero latência imposta por rede em suas interações core, e a garantia de que seus dados residem primariamente em seu dispositivo.

### Princípios de Design (The Cashew Axioms):
1. **Soberania do Estado Local:** O dispositivo é a fonte da verdade (Source of Truth). Qualquer provedor de nuvem atua estritamente como um vetor de transporte (para sync) ou repositório de backup frio, nunca como bloqueio ao uso em tempo real.
2. **Imersão Estética Adaptativa:** Utilização intensiva do **Material You**. A aplicação deve fundir-se à linguagem de design do sistema hospedeiro (especialmente em Android), extraindo paletas de cores dinamicamente, garantindo que o software sinta-se nativo, pessoal e organico.
3. **Resiliência a Longo Prazo:** Arquivos binários compilados pesados são preferidos em oposição a dependências de APIs web frágeis. O sistema financeiro de um usuário tem que funcionar exatamente igual daqui a 10 anos.

---

## 2. Rationale de Stack e Ferramentas

A base tecnológica foi curada meticulosamente para garantir a intersecção de máximo alcance de plataforma com performance quase-nativa.

### 2.1. O Motor de Renderização: Flutter & Dart
A escolha do Flutter permite uma compilação AOT (Ahead-of-Time) para ARM (iOS/Android) e JavaScript/WASM para Web. Isto consolidou a base de código (Single Codebase), que em aplicações financeiras elimina o perigoso cenário de disparidade de regras de negócio entre cliente iOS e Web. Todo calculo de saldo, agrupamento de budget e lógica de filtragem roda rigorosamente a mesma Virtual Machine do Dart em todos os alvos.

### 2.2. A Camada de Persistência Reativa: Drift (SQLite)
Em vez de depender de soluções NoSQL como o Hive ou SharedPreferences para dados core, a arquitetura exige a previsibilidade relacional do **SQLite**.
Utilizamos o pacote **Drift** (anteriormente Moor) como nossa camada ORM. Por quê Drift?
- **Segurança de Tipo (Type Safety) via Geração de Código:** Escrevemos esquemas em Dart puro ou arquivos `.drift` (SQL nativo), e o `build_runner` gera as classes de entidade. Erros de sintaxe SQL ou acessos a colunas não existentes são capturados em *compile-time*.
- **Streams Reativas Imediatas:** No Cashew, se uma transação muda no backend, a UI atualiza automaticamente. O Drift provê `Stream<T>` nativamente para qualquer query. Quando um dado na tabela `Transaction` sofre mutação, todos os widgets ouvindo aquela Stream (como gráficos de pizza de Budget) são repintados na próxima frame. Evita-se a complexidade de disparar eventos manuais.

### 2.3. Gestão de Estado Global: Provider
Apesar da existência de BLoC ou Riverpod, optamos por **Provider** associado ao `ChangeNotifier` para a orquestração do estado superficial da UI (como o Wallet/Account selecionado, filtros de transações ativos, ThemeMode).
- **Justificativa:** O estado profundo (o ledger de transações) já é reativo através do Drift. O estado da UI é simples o suficiente para que o overhead de *boilerplates* complexos como BLoC seja desnecessário e poluente. Utilizamos Providers pontuais (`ChangeNotifierProvider` em `AppStateNotifier`, `SettingsNotifier`, etc.) que transitam via árvore de contexto do Flutter.

---

## 3. Topologia do Sistema

O Cashew atua num modelo de **Thick Client** (Cliente Espesso).

```text
+-----------------------------------------------------------------+
|                         User Interface (Flutter)                |
|  [ Widgets (Material You), Páginas de Gráficos, Formulários ]   |
+-----------------------------------------------------------------+
|                      Gestão de Estado (Provider)                |
|  [ Selected Wallet, UI Theme, Filtros Temporais (WatchAll...) ] |
+-------------------------------+---------------------------------+
|         Business Logic        |        Sync & Backup Engine     |
| [ Conversão Moeda, Budgets,   | [ Auth Firebase, Google Drive   |
|   Objetivos/Empréstimos ]     |   Rest API, CSV Export/Import ] |
+-------------------------------+---------------------------------+
|                  Data Access Layer (Drift ORM)                  |
|  [ Tabelas Fortemente Tipadas, Migrações Versionadas (Steps) ]  |
+-----------------------------------------------------------------+
|                  Persistência Física (SQLite)                   |
|                   [ Armazenamento no Dispositivo ]              |
+-----------------------------------------------------------------+
```

### O Desacoplamento da Interface e Banco de Dados
Módulos de UI jamais realizam query SQL direta. Eles se inscrevem em funções reativas do `tables.dart` (ex: `database.watchAllTransactions()`), ou instanciam objetos fortemente tipados gerados pelo Drift. Toda regra de negócio que demanda agregação complexa de gastos e lucros está contida dentro de lógicas reusáveis de função pura em `functions.dart` ou métodos auxiliares do próprio modelo de banco de dados.
