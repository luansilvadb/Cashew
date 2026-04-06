# Documentação Técnica e Operacional: Cashew (Budgeting & Finance)

Este documento fornece um mapeamento exaustivo da aplicação Cashew, servindo como guia de onboarding técnico e operacional para garantir a autonomia no uso, desenvolvimento e manutenção do ecossistema.

---

## 1. Visão Geral e Arquitetura de Valor

### 1.1 Propósito do Produto
O Cashew é uma solução de gestão financeira pessoal "offline-first", projetada para oferecer soberania de dados e flexibilidade extrema. O foco principal é permitir que o usuário rastreie transações, gerencie orçamentos e planeje objetivos de economia com uma interface intuitiva e altamente customizável.

### 1.2 Pilares de Valor
- **Privacidade e Soberania:** Os dados residem primariamente no dispositivo do usuário.
- **Flexibilidade:** Suporte multi-moeda, categorias personalizáveis e ícones dinâmicos.
- **Resiliência:** Sistema de backup robusto integrando armazenamento local e nuvem (Google Drive/Firebase).
- **Performance:** Interface reativa construída com Flutter, garantindo fluidez em dispositivos móveis e web.

### 1.3 Arquitetura Técnica Simplificada
- **Frontend:** Flutter (Dart) para entrega multiplataforma.
- **Persistência Local:** Banco de dados SQLite gerenciado via biblioteca `Drift`.
- **Backend/Sincronização:** Firebase Auth para identidade e Google Drive API para armazenamento de backups de banco de dados.
- **Localização:** Sistema de internacionalização (i18n) dinâmico com suporte a múltiplos idiomas e formatos de moeda.

---

## 2. Mapeamento Sequencial da Jornada do Usuário

### Fase 1: Primeiro Acesso e Configuração (Onboarding)
1.  **Seleção de Idioma:** O usuário escolhe a língua de interface (persistido no `userSettings`).
2.  **Configuração de Moeda:** Definição da moeda principal do sistema, com carregamento de taxas de câmbio dinâmicas.
3.  **Autenticação (Opcional):** Vinculação com Google para habilitar sincronização automática.
4.  **Restauração:** Verificação de backups existentes na nuvem para importação imediata de dados históricos.

### Fase 2: Estruturação Financeira
1.  **Criação de Carteiras (Wallets):** Registro de contas correntes, poupanças ou dinheiro físico.
2.  **Definição de Categorias:** Organização de gastos (Ex: Alimentação, Lazer, Aluguel).
3.  **Estabelecimento de Orçamentos (Budgets):** Definição de limites de gastos mensais ou semanais por categoria.

### Fase 3: Operação Diária (Fluxo de Sucesso)
1.  **Registro de Transação:** Entrada rápida de gastos/receitas (Nome, Valor, Categoria, Carteira).
2.  **Monitoramento:** Visualização imediata do impacto no saldo total e nos limites de orçamento através da Home.
3.  **Acompanhamento de Objetivos:** Alocação de economias para metas específicas (Ex: Viagem, Fundo de Emergência).

---

## 3. Dicionário de Módulos e Funcionalidades Core

### 3.1 Transações (`lib/pages/addTransactionPage.dart`)
- **Tipos:** Despesa (Saída), Receita (Entrada) e Transferência entre carteiras.
- **Atributos:** Tags, notas, anexos, recorrência e localização.

### 3.2 Carteiras e Contas (`lib/pages/accountsPage.dart`)
- **Gestão de Saldo:** Ajustes de balanço e acompanhamento de patrimônio líquido (Net Worth).
- **Multimoeda:** Suporte a conversão automática baseada em taxas de câmbio.

### 3.3 Orçamentos (`lib/pages/budgetsListPage.dart`)
- **Limites:** Monitoramento visual (progresso de gastos vs. limite).
- **Períodos:** Suporte a ciclos mensais, semanais ou customizados.

### 3.4 Sincronização e Backup (`lib/struct/syncClient.dart`)
- **Google Drive Sync:** Upload/Download silencioso de snapshots do banco de dados SQLite.
- **Integridade:** Versionamento de backups para evitar perda de dados por conflitos de escrita.

---

## 4. Fluxos de Exceção e Resolução de Problemas

### 4.1 Falhas de Sincronização
- **Sintoma:** Dados não aparecem em outro dispositivo.
- **Causa Comum:** Token de autenticação expirado ou falta de permissão no Google Drive ("App Data folder").
- **Resolução:** Re-autenticar via Configurações > Backup e Sincronização.

### 4.2 Transações Sem Categoria ("Wandering Transactions")
- **Detecção:** O sistema possui rotinas (`deleteWanderingTransactions`) para identificar e remover transações cujas categorias foram deletadas sem o devido remanejo.
- **Ação:** Recomenda-se sempre atribuir uma categoria "Outros" antes de deletar uma categoria principal.

### 4.3 Corrupção do Banco de Dados Local
- **Resolução:** A aplicação permite a exportação/importação manual do arquivo `.db` e de arquivos `CSV` como redundância ao sistema de nuvem.

---

## 5. Guia de Proficiência

### 5.1 Atalhos e Automação
- **Quick Actions:** Pressionar o ícone do app na home do celular para abrir diretamente a "Nova Transação".
- **Templates de Email:** Configuração de parsing de emails bancários para entrada automática de transações (funcionalidade avançada).
- **Recorrência:** Uso de "Scheduled Transactions" para contas fixas mensais.

### 5.2 Configurações Avançadas
- **UI Custom:** Ajuste da velocidade de animações, troca de fontes (Avenir, Inter, DMSans) e temas (Light, Dark, OLED Black).
- **Modo Debug:** Acesso a logs internos e flags de desenvolvimento via `debugPage.dart`.

### 5.3 Dicas de Eficiência
- **Busca Global:** Utilizar a barra de pesquisa para filtrar transações por notas ou tags específicas.
- **Edição em Massa:** Seleção múltipla na lista de transações para alteração rápida de categorias ou exclusão.

---
