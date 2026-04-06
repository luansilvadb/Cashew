# Esquema do Banco de Dados

O Cashew utiliza o **Drift** (anteriormente Moor) como sua engine de persistência SQLite. Abaixo estão as principais tabelas e seus papéis no sistema.

## Tabelas Principais

### Wallets (Contas)
Armazena as contas financeiras do usuário (ex: Carteira, Banco, Cartão de Crédito).
- `walletPk`: Chave primária (String/UUID).
- `name`: Nome da conta.
- `currency`: Moeda associada.
- `amount`: Saldo atual (calculado ou armazenado).

### Transactions (Transações)
A tabela central que registra todas as entradas e saídas.
- `transactionPk`: Chave primária.
- `amount`: Valor da transação (positivo para receita, negativo para despesa).
- `categoryFk`: Chave estrangeira para a categoria.
- `walletFk`: Chave estrangeira para a conta (Wallet).
- `type`: Tipo especial (upcoming, subscription, repeating, etc).

### Categories (Categorias)
Define como as transações são agrupadas.
- `categoryPk`: Chave primária.
- `name`: Nome da categoria.
- `icon`: Referência ao ícone utilizado.

### Budgets (Orçamentos)
Define limites de gastos por período e categoria.
- `budgetPk`: Chave primária.
- `name`: Nome do orçamento.
- `amount`: Valor limite.
- `reoccurence`: Ciclo (daily, weekly, monthly, yearly, custom).

### Objectives (Objetivos/Metas)
Rastreia economias para metas específicas.
- `objectivePk`: Chave primária.
- `name`: Nome da meta.
- `amount`: Valor total desejado.

### DeleteLogs
Crucial para o sistema Local-First. Registra quais itens foram excluídos localmente para que possam ser removidos de outros dispositivos durante a próxima sincronização.

## Relacionamentos

- Uma **Transaction** pertence a uma **Category** e a uma **Wallet**.
- Uma **Transaction** pode estar associada a um **Objective** (Goal).
- Um **Budget** pode filtrar transações por múltiplas categorias.
