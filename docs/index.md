# Cashew App: Arquitetura e Memória Institucional

Bem-vindo à documentação oficial da arquitetura do Cashew App. Este repositório de conhecimento compila o design de sistema, o mapeamento atômico das jornadas do usuário e a filosofia arquitetural que impulsiona o ecossistema.

## Índice Enciclopédico

1. [Filosofia Arquitetural e Design System](01-architectural-philosophy.md)
   - O Paradigma Local-First
   - Ontologia e Semântica Core (Wallets e Objectives)
2. [Mapeamento Sequencial de Jornada em Nível Atômico](02-user-journey-atomic-mapping.md)
   - Fluxo de Lançamento de Transação
   - Micro-transições de estado e tratamento de Entropia
3. [Modos de Colaboração e Sincronização em Massa](03-collaboration-and-sync-internals.md)
   - Compartilhamento no Firebase
   - Racional Tombstone e DeleteLogs do Google Drive Sync
4. [A Ontologia do Dinheiro: Metas, Câmbios e Erros](04-core-states-and-errors.md)
   - Processamento Assíncrono com Isolates
   - Filosofia do Isolamento e Atomicidade no Banco Local

Estes documentos foram projetados para proporcionar um grau de masterização total aos mantenedores do projeto, descrevendo o comportamento "sob o capô" com densidade e rigor técnico.
