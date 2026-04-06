# Capítulo 3: Módulos Core e Mecanismos Internos Exaustivos

Esta seção dissecada adentra o nível mais profundo do código-fonte. Aqui destrinchamos as engrenagens mestres que fazem o app funcionar e garantem sua evolução retro-compatível.

## 1. O Modelo de Domínio Relacional (`database/tables.dart`)

O coração do Cashew é seu banco de dados local modelado em Drift. Não se trata apenas de tabelas, mas de ecossistemas reativos.

### 1.1. Schema e Constantes de Banco
O arquivo inicia com os Enum-Types fundamentais que guiam a lógica do negócio, antes mapeados como `INTs` ou `VARCHARs` literais em SQL, são convertidos transparentemente via Drift para Enums nativos de Dart:
- `TransactionSpecialType`: Controla se o fluxo monetário é Padrão, Upcoming (Futuro e não descontado no balanço), Subscription, ou Dívida/Crédito.
- `BudgetReoccurence`: Controla a periodicidade matemática dos orçamentos (Diário até Costumizado).

### 1.2. O Motor de Migração Estrita
Em `schemaVersionGlobal` (ex: `46`), reside o coração da manutenção do banco de dados a longo prazo. No Cashew, o banco nunca quebra de versão.
- Como garantimos que o usuário que baixar o app após 1 ano sem atualizar continue com seus dados?
- O Cashew utiliza **Step-by-step Migrations** em `schema_versions.dart`. Para cada alteração estrutural nas tabelas (adicionar coluna `isShared`, criar tabela de Objetivos, etc.), o comando `dart run drift_dev schema steps` cria arquivos versionados representativos daquele salto histórico. O `await stepByStep(...)` garante que o aplicativo faça as *ALTER TABLES* de forma sequencial, até bater a versão atual.

---

## 2. Utilitários Fundamentais e Injeção Lógica (`functions.dart`)

A `functions.dart` atua como uma **Standard Library interna**. Evitamos poluir a classe de Widgets e centralizamos funções estáticas globais e matemáticas.

### 2.1. O Wrapper de Plataforma Universal
O Dart `Platform` falha miseravelmente ao compilar para Web. Por isso a criação do helper universal `getPlatform()`. Todo o aplicativo, em todo lugar, consome esta abstração para não crashar ao decidir se deve usar ícones de Cupertino (iOS), Material (Android), ou layouts extendidos no Web/PWA.

### 2.2. Date Utils e Time Manipulation
A manipulação financeira é primariamente uma manipulação temporal.
- Usamos extensões em Dart (Ex: `extension DateUtils on DateTime`) para injeção de métodos auxiliares na própria estrutura de base do Flutter `DateTime`. Funções que fazem `.copyWith(month: x)`, calculam interseções de períodos para os limites visuais dos gráficos, ou determinam que 'Hoje' mudou para recarregar transações.

---

## 3. Gestores de Estado Isolados (`struct/*.dart` e Providers)

Se Drift lida com a camada permanente, a pasta `struct` abriga os cérebros passageiros do Cashew.

### 3.1. Settings e Preferências do Usuário
A configuração de temas, cores customizadas, fuso horário preferencial de notificações locais está em classes Singleton gerenciadas pelo `shared_preferences`. Isso permite leitura síncrona bloqueante na inicialização (`main.dart`), para que o App não pisque no tema errado antes de renderizar a primeira página (evitando o "White Screen Flash").

### 3.2. A Central de Notificações (`initializeNotifications.dart`)
Trata de abstrair o `flutter_local_notifications`. O Cashew precisa rastrear quando uma transação `Upcoming` se tornou `Today` sem ligar para a internet. O agendamento é resolvido pelo fuso horário (tz) internalizado e disparos cronometrados do SO do telefone.

---

## 4. Ecossistema UI e Animação

Por que a interface é tão fluida?
1. O uso de **ScrollBehaviorOverride** garante físicas de deslizamento coesas com a intenção do usuário entre Plataformas (O bounce elástico em iOS x overscroll em Android).
2. **CustomDelayedCurve** modela as transições espaciais de páginas. Em vez de usar `push()` cru, o Cashew aderece a animações que mascaram micro-latências de fetch do SQLite, providenciando o `pushRoute(context, page)` encapsulado em `functions.dart`.
3. Integração total ao `DynamicColorBuilder`. Ao invés de *hardcoding* de cores base, geramos Swatches de tintas e sombras da API Nativa do Android > 12.
