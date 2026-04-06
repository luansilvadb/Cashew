# Capítulo 5: Proficiência Avançada e Masterização Completa

Guia de domínio profundo, projetado para administradores, integradores de sistema e contribuidores do Cashew.

## 1. Automações Pessoais (App Links e Intents)

Como a plataforma é fundamentalmente isolada (não consome SMS diretamente via API, dadas as políticas de lojas do Google Play / iOS de privacidade estrita), os *Power Users* integram o Cashew via **Deep Links**.

### O Protocolo de App Links
Um usuário pode automatizar através de Tasker (Android) ou Apple Shortcuts invocando URLs formatadas:
- A engine em `appLinks.dart` engole `cashew://transaction?amount=...`
- Extrai parâmetros URL na *Main Isolate*, transforma numa Intent Reativa que pausa a Home atual, e puxa forçadamente (push modal) a tela de preenchimento (`addBudgetPage` ou `addTransaction`) com todos os campos previamente autopreenchidos da requisição externa. O toque do usuário vira mera confirmação.

## 2. Ingestão e Saída de Dados Frios (Data Portability)

### Exportação e Importação de CSV / Google Sheets
- O motor de CSV interno não apenas mapeia colunas estritas. É um parser inteligente de inferência heurística.
- Ao tentar importar planilhas de bancos distintos (Chase, Nubank, etc.), o Cashew provê a tela onde se mapeia dinamicamente qual coluna do arquivo bruto representa Valor, Título e Data.
- O formato do Cashew tem escape customizado de texto e garante o respeito das enums exportadas.

## 3. O Fluxo de Desenvolvimento Estrito (Compilando o Banco de Dados)

Os passos críticos de quem toca na classe `tables.dart`. O aplicativo não roda "modificando na raça".
- Quando um dev altera `int schemaVersionGlobal = X + 1` ele precisa **necessariamente**:
  1. No Root: `dart run build_runner build` (Gera código sujo do banco ORM).
  2. Executar o **Schema Dump** de Snapshots.
  `dart run drift_dev schema dump lib/database/tables.dart drift_schemas/drift_schema_v[schemaVersion].json`
  3. Com o JSON salvo no repo, gera os passos transientes (`schema steps`) de ponte.
  4. Escrever o SQL exato no arquivo `schema_versions.dart` para quem está atualizando o app não crashar.
- O que garante que as instâncias locais legadas de testadores também migrem de forma idêntica as novas do ambiente.

## 4. O Futuro e Limitações Inerentes do App
- **Limitação Frontal do Design de SQLite Mobile:** Não há forma viável e barata para um sistema multiusuários colaborativos sincrônicos no modelo atual *sem* um servidor websocket persistente central. Compartilhamento de *Budgets* ou contas familiares requer *Workarounds* com arquivos em nuvem que dependem da disciplina de um Master-Admin e Read-Only Guests (não oficialmente exposto).
- O plano a longo prazo envolveu uma estrutura onde o Drift sincroniza não contra Drive, mas contra um servidor autohospedado por Power-Users (Self-Hosting via API Rest local).
