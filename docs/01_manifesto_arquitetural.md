# Manifesto Arquitetural: Teleologia e Design do Cashew

## 1. Arquitetura da Intenção
A intenção primordial do Cashew é a democratização do controle financeiro através de uma interface intuitiva, porém robusta sob o capô. O gatilho do usuário nasce da necessidade de visibilidade sobre o fluxo de capital pessoal ou compartilhado. O modelo mental esperado é o de um "ledger" digital que se comporta como uma extensão da memória do usuário, permitindo que cada centavo seja rastreado sem atrito cognitivo.

## 2. Mecânica de Processamento
O processamento é regido pelo princípio **Local-First**. Todas as regras de negócio são executadas localmente usando o motor de banco de dados **Drift (SQLite)**.
- **Validação de Dados:** Antes de persistir qualquer entrada, o sistema valida limites de caracteres (NAME_LIMIT=250, NOTE_LIMIT=500), tipos de dados e consistência de chaves estrangeiras (Wallets, Categories).
- **Transformações:** Valores monetários são processados com alta precisão, convertendo entradas de string para double e aplicando arredondamentos definidos pelas configurações de precisão decimal (por padrão, 2 casas).

## 3. Micro-Transições de Estado
O estado evolui de "Transient" (em formulário) para "Persistent" (no SQLite) e, opcionalmente, para "Cloud-Synchronized" (no Google Drive ou Firestore).
- **Dependências Críticas:** A existência de pelo menos um 'Wallet' (Account) e uma 'Category' é pré-requisito para a criação de qualquer transação. O 'SelectedWalletPk' atua como o cursor global de estado para o contexto de exibição.

## 4. Tratamento de Entropia
A entropia é combatida através de:
- **DeleteLogs:** Mecanismo de rastreamento de deleções para garantir que operações em um dispositivo sejam replicadas corretamente em outros durante a sincronização.
- **Wandering Records Cleanup:** Funções automáticas (`deleteWanderingTransactions`, `deleteWanderingTitles`) que removem registros órfãos que perderam suas referências de categoria ou carteira.
- **Fallback de Moeda:** Caso uma carteira referenciada desapareça, o sistema recalcula totais usando a moeda primária definida ou o fallback para USD.

## 5. Filosofia do Design
O design atômico do Cashew reflete 20 anos de evolução em sistemas de gerenciamento. A decisão de manter nomes internos como `Wallet` e `Objective` (em vez de `Account` e `Goal`) preserva a integridade da 'memória institucional' e evita quebras em esquemas de banco de dados legados, enquanto a camada de UI traduz esses conceitos para o léxico moderno. A estrutura é sequencial e modular, permitindo que novas funcionalidades (como orçamentos compartilhados) sejam acopladas sem desestabilizar o núcleo transacional.
