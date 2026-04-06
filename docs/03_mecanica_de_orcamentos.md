# Mecânica de Orçamentos: Limites e Recorrência Atômica

## 1. Arquitetura da Intenção
A intenção do usuário ao criar um orçamento (`Budget`) é estabelecer uma disciplina de gastos. O modelo mental esperado é o de um "balde" que se enche ou esvazia conforme o capital flui, disparando alertas cognitivos quando os limites são atingidos.

## 2. Mecânica de Processamento
O motor de orçamentos do Cashew (`getBudgetDate`) calcula dinamicamente o período vigente:
- **Cálculo de Período:** Orçamentos são calculados via recursão ou loopings (`for (int i = 0; i < 10000; i++)`) para encontrar o `DateTimeRange` exato baseado em `startDate`, `periodLength` e `reoccurrence`.
- **Filtros de Inclusão/Exclusão:** Transações são filtradas via `onlyShowIfFollowsFilters` e `isInCategory`, suportando `categoryFks`, `categoryFksExclude` e `walletFks`.
- **Limites por Categoria:** O sistema cruza os limites definidos em `CategoryBudgetLimits` com as transações persistidas para prover o "gasto por categoria" em tempo real dentro do orçamento global.

## 3. Micro-Transições de Estado
Um orçamento transita entre:
- **Active:** Dentro do `DateTimeRange` calculado.
- **Past/History:** Visualização de períodos anteriores via `pastBudgetsPage`.
- **Archived:** Orçamentos que não aceitam mais entradas e saem da visão principal, mas cujos dados persistem para integridade histórica.
- **Pinned:** Estado que dita a prioridade de exibição no `HomePage`.

## 4. Tratamento de Entropia
Para lidar com mudanças estruturais:
- **Migração de Limites:** Ao alterar a moeda primária, o sistema recalcula todos os limites percentuais e absolutos (`toggleAbsolutePercentSpendingCategoryBudgetLimits`).
- **Orçamentos Órfãos:** Limites vinculados a categorias ou orçamentos deletados são removidos pelo `fixWanderingCategoryLimitsInBudget`.
- **Ajuste de Saldo:** O sistema permite "Balance Corrections" (categoria pk="0") para forçar a realidade financeira sobre o modelo digital sem perder o rastro do ajuste.

## 5. Filosofia do Design
A flexibilidade do motor de orçamentos, permitindo desde o "ciclo padrão mensal" até "períodos customizados para viagens" (`BudgetReoccurence.custom`), reflete o racional de que a vida financeira não é linear. O design prioriza o desempenho local, recalculando totais em tempo real em vez de armazenar saldos pré-calculados que poderiam dessincronizar.
