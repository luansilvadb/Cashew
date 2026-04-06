# Colaboração e Partilha: O Ecossistema Compartilhado

## 1. Arquitetura da Intenção
A intenção do compartilhamento de orçamentos (`Shared Budgets`) é a visibilidade coletiva sobre fundos comuns (casais, famílias ou grupos). O modelo mental é de uma "conta conjunta digital" sem a necessidade de uma conta bancária real compartilhada.

## 2. Mecânica de Processamento
A arquitetura de partilha (`shareBudget.dart`) utiliza o **Firebase Firestore**:
- **Publicação do Orçamento:** Um `Budget` local é espelhado no Firestore na coleção `/budgets`.
- **Sincronização de Transações:** Cada transação vinculada a um orçamento compartilhado é publicada na sub-coleção `/budgets/[sharedKey]/transactions`.
- **Fila de Upload (Offline-First):** Se o usuário estiver offline, as transações são enfileiradas no `sendTransactionsToServerQueue` em `AppSettings` e sincronizadas posteriormente (`syncPendingQueueOnServer`).

## 3. Micro-Transições de Estado
Um orçamento compartilhado transita por:
- **Local-Only:** Orçamento convencional sem `sharedKey`.
- **Shared (Owner):** Criado localmente e publicado no Firestore, onde o usuário detém a propriedade.
- **Shared (Member):** Importado via convite, onde o usuário é apenas um leitor/escritor de transações.
- **Disconnected:** Quando o acesso é revogado remotamente, mas os dados locais persistem (`compareSharedToCurrentBudgets`).

## 4. Tratamento de Entropia
Para mitigar inconsistências na nuvem:
- **Log de Deleção Remota:** Quando uma transação compartilhada é deletada localmente, o sistema publica um "logType: delete" no Firestore em vez de apenas apagar o registro, garantindo que outros membros também a removam.
- **Limpeza de Orfãos Compartilhados:** Transações órfãs cujo orçamento compartilhado foi deletado são movidas para uma carteira local neutra.
- **Sincronização de Categorias:** Se uma transação compartilhada usa uma categoria não existente no membro, o sistema a recria automaticamente (`downloadTransactionsFromBudgets`).

## 5. Filosofia do Design
O design de partilha é granular. Cada transação é um documento isolado no Firestore, o que evita conflitos de escrita em documentos grandes. O racional é manter a experiência do usuário "rápida e local", enquanto a nuvem atua como um barramento de eventos assíncronos que mantém o grupo em sincronia.
