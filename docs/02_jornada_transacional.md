# Jornada Atômica: O Ciclo de Vida da Transação

## 1. Arquitetura da Intenção
A intenção do usuário ao criar uma transação é materializar um evento financeiro no ledger. O gatilho é quase sempre um gasto real ou um recebimento, onde o modelo mental exige rapidez (para evitar esquecimento) e precisão (para garantir orçamentos corretos).

## 2. Mecânica de Processamento
Cada transação passa por um fluxo rigoroso de validação e transformação (`createOrUpdateTransaction`):
- **Normalização de Sinais:** A polaridade do `amount` é ajustada (`abs() * (income ? 1 : -1)`) para garantir que despesas e receitas sejam somadas corretamente em queries de agregação.
- **Associação Automática de Títulos:** O sistema consulta a tabela `AssociatedTitles` para sugerir ou aplicar categorias baseadas no nome fornecido, utilizando correspondência exata ou parcial.
- **Tratamento de Anexos:** Se houver arquivos vinculados, o `uploadAttachment` é disparado, movendo o dado para o armazenamento local persistente ou para a nuvem.

## 3. Micro-Transições de Estado
A transação evolui através de estados lógicos:
- **Pending/Upcoming:** Transações futuras com flags de notificação ativa (`upcomingTransactionNotification`).
- **Paid/Cleared:** Transações efetivadas que alteram o saldo real da `Wallet`.
- **Subscription/Repetitive:** Estados que geram clones automáticos baseados em `periodLength` e `BudgetReoccurence` (Daily, Weekly, Monthly, Yearly).
- **Credit/Debt:** Transações vinculadas a `Objectives` (Loans), onde o estado "Paid" inverte a lógica de exibição para representar a amortização da dívida.

## 4. Tratamento de Entropia
O sistema gerencia incertezas via:
- **Transações Pareadas (Transferências):** Em transferências entre `Wallets`, o sistema cria duas transações vinculadas via `pairedTransactionFk`. Se uma for editada, o sistema tenta sincronizar a outra (`updateCloselyRelatedBalanceTransfer`).
- **Recuperação de Deleção:** Deleções não são apenas remoções; elas geram um `DeleteLog` e alimentam o `recentlyDeletedTransactions` para permitir o "undo".
- **Limpeza de Registros Órfãos:** O sistema verifica periodicamente transações vinculadas a categorias que foram deletadas (`deleteWanderingTransactions`).

## 5. Filosofia do Design
A transação é o átomo do Cashew. O racional histórico por trás da estrutura é a versatilidade: o mesmo registro de `Transaction` pode ser um simples café, uma assinatura de streaming recorrente ou um empréstimo complexo para um amigo. Essa unificação simplifica o motor de queries enquanto a UI provê as abstrações necessárias para cada caso de uso.
