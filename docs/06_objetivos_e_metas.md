# Objetivos e Metas: A Gestão de Empréstimos e Economias

## 1. Arquitetura da Intenção
A intenção do usuário ao criar um `Objective` (Meta) ou um `Goal` (Objetivo de Empréstimo) é o planejamento de longo prazo. O modelo mental varia:
- **Savings Jars:** Uma "caixa de dinheiro" que se enche gradualmente.
- **Long-Term Loans:** Um montante inicial que diminui com amortizações sucessivas.

## 2. Mecânica de Processamento
O motor de objetivos (`ObjectiveType`) processa as transações de forma distinta:
- **Savings Goals:** Transações vinculadas via `objectiveFk` são somadas ao montante atual.
- **Long-Term Loans (Lent/Borrowed):** A lógica é inversa (`cleanseTransactionForLongTermLoan`). O montante inicial é um `expense` (lent) ou `income` (borrowed), e as amortizações subsequentes têm polaridade oposta. O sistema calcula a diferença para exibir o "valor restante".
- **Conversão de Moeda:** Totais são recalculados em tempo real usando a moeda primária definida para a Meta (`watchTotalTowardsObjective`).

## 3. Micro-Transições de Estado
Um objetivo transita entre:
- **Active (In Progress):** Abaixo do valor alvo (ou acima do valor inicial para empréstimos).
- **Completed:** Quando o `totalTowardsObjective` atinge ou supera o `amount`.
- **Archived:** Metas concluídas que são removidas da visualização ativa para foco no presente.
- **Pinned:** Estado de prioridade máxima no `ObjectivesListPage`.

## 4. Tratamento de Entropia
Gerenciamento de inconsistências:
- **Metas de Diferença Única (Difference-Only Loans):** Lógica especial para empréstimos onde apenas a diferença final importa, sem rastreamento de amortizações individuais.
- **Wandering Objectives Cleanup:** Metas vinculadas a carteiras deletadas são movidas para a carteira padrão do sistema (`fixWanderingCategoryLimitsInBudget`).
- **Limpeza de Transações:** Se um Objetivo é deletado, o sistema limpa as referências `objectiveFk` e `objectiveLoanFk` de todas as transações vinculadas.

## 5. Filosofia do Design
A decisão de unificar economias e empréstimos em uma única estrutura de `Objective` no banco de dados, mas separá-los na UI (Goals vs Loans), simplifica o motor de queries e garante consistência estrutural. O Cashew utiliza o racional de que "dinheiro guardado" e "dinheiro a receber/pagar" são faces da mesma moeda de planejamento futuro.
