# Capítulo 1: Filosofia Arquitetural e Design System

## 1.1 O Paradigma Local-First e Sync Distribuído
O ecossistema opera sob uma filosofia de **"Local-First"**. O banco de dados local SQLite (gerenciado via pacote Drift) é a fonte absoluta da verdade. O aplicativo nunca depende de conectividade imediata para executar suas lógicas de negócio.

A sincronização de dados ocorre de forma otimista e descentralizada, principalmente através do Google Drive API (estruturado no `syncClient.dart`). Esta escolha arquitetural, tomada na concepção, assegura:
1. **Resiliência a Partições de Rede:** O estado do usuário é preservado de forma atômica independentemente do acesso à nuvem.
2. **Propriedade dos Dados:** O backup no Drive garante que os dados permaneçam sob controle e escopo do usuário.
3. **Resolução de Conflitos Distribuídos:** Utiliza a mecânica de `DeleteLogs` e carimbos de tempo (timestamps) de modificação para unificação cruzada entre dispositivos (`merge` direcional de logs mais recentes).

## 1.2 Semântica e Ontologia do Sistema
Durante a evolução de 20 anos, o vocabulário do sistema abstraiu alguns termos legados para manter compatibilidade no código-fonte, divergindo do front-end:
* **`Wallets`:** Historicamente mapeia e compõe a estrutura atual de **"Accounts" (Contas)**.
* **`Objectives`:** Historicamente mapeia e compõe a estrutura de **"Goals" (Metas/Objetivos)** de economia.
* **`Polarity`:** O fluxo de valor das transações (positivo para receita, negativo para despesa). Essa estrutura polar dita as regras de acumulação nos `Objectives`.

## 1.3 Arquitetura de Estado Orientada a Eventos
O gerenciamento de estado da interface em Flutter baseia-se pesadamente no pacote `provider`. Alterações no banco Drift emitem eventos que invalidam a árvore de renderização onde necessário. A maior parte das mutações de estado e processamento de negócio de transações e contas encontra-se concentrada no módulo core `budget/lib/functions.dart`.
