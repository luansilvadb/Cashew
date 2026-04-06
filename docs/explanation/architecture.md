# Arquitetura do Sistema

O Cashew é construído com Flutter e segue uma filosofia **Local-First**, priorizando a performance e a privacidade do usuário, com sincronização em nuvem opcional.

## Filosofia "Local-First"

A fonte da verdade reside no banco de dados local do dispositivo.
- **Drift (SQLite)**: Gerencia o armazenamento persistente.
- **Sincronização**: Realizada via Google Drive API, permitindo que o usuário mantenha seus dados em múltiplos dispositivos sem um servidor centralizado de banco de dados.
- **Integridade**: O sistema utiliza `DeleteLogs` para rastrear exclusões e garantir que as sincronizações não causem inconsistências.

## Componentes Principais

### 1. Banco de Dados (Persistence Layer)
Localizado em `budget/lib/database/tables.dart`. Define o esquema do SQLite usando a biblioteca Drift.

### 2. Gerenciamento de Estado
O projeto utiliza o pacote `provider` para gerenciar o estado global, como configurações do usuário, contas selecionadas e temas.

### 3. Sincronização (Entropy & Sync)
A lógica de sincronização está concentrada em `budget/lib/struct/syncClient.dart`. Ela gerencia uploads/downloads para o Google Drive e resolve conflitos de versão.

### 4. Localização (i18n)
Utiliza `easy_localization`. Os arquivos de tradução são gerados a partir de uma planilha mestre e localizados em `budget/assets/translations/`.

## Mapeamento de Terminologia

Para manter a consistência, observe as seguintes equivalências entre o código interno e a interface do usuário:

| Interno (Código) | Interface (UI) |
| :--- | :--- |
| Wallets | Accounts (Contas) |
| Objectives | Goals (Objetivos) |

## Tech Stack

- **Framework**: Flutter
- **Linguagem**: Dart
- **DB Local**: Drift (SQLite)
- **Auth**: Firebase Auth (Google Login)
- **Cloud Storage**: Google Drive API
- **Localização**: Easy Localization
