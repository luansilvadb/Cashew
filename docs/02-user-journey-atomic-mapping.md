# Capítulo 2: Mapeamento Sequencial de Jornada em Nível Atômico

A fundação do nosso sistema baseia-se na captura meticulosa e processamento das ações do usuário. A teleologia de cada fluxo não é meramente completar uma ação, mas garantir integridade referencial, resiliência de estado e coerência distribuída.

## 2.1 Jornada de Lançamento Atômico: Criação de Transação (Transaction Dispatch)

### 2.1.1 Arquitetura da Intenção
* **O Gatilho:** O usuário aciona o modal de "Nova Transação" via UI padrão ou deep link externo (`cashew://transaction` via `appLinks.dart`).
* **O Modelo Mental:** O usuário espera que uma redução de saldo em sua conta (Wallet) aconteça em tempo real para refletir seu gasto ou ganho, classificado sob uma categoria ou orçamento (Budget).

### 2.1.2 Mecânica de Processamento
1. **Captura e Tipagem:** O sistema extrai polaridade (renda ou despesa) baseando-se na categoria.
2. **Conversão de Câmbio em Tempo Real:** Múltiplas moedas são tratadas. O valor inserido é armazenado e, se necessário, o equivalente na moeda base da conta (Account/Wallet) é gerado para a totalização.
3. **Escrita no Drift (SQLite):**
   * Transação é inserida na tabela referencial (cf. `budget/lib/database/tables.dart`).
   * A entidade armazena o `dateCreated` e `dateModified`. O `dateModified` atua como relógio lógico de Lamport para as unificações no `syncClient.dart`.
4. **Resolução de Metas (Long Term Loans/Objectives):**
   * Se a transação é vinculada a um empréstimo ou objetivo, o sistema recalcula o progresso: as despesas iniciais (negativas) constroem o teto da meta e as rendas (positivas) atuam como liquidação ("pagos de volta").
   * Este recálculo ocorre majoritariamente em `functions.dart`.

### 2.1.3 Micro-Transições de Estado
* `[Idle UI] -> [Form Active] -> [Validation Rules] -> [SQLite Mutate Event] -> [Provider Notification] -> [UI Rebuild (Dashboard/Graphs)]`
* **Dependência Crítica:** A inserção da transação não pode ocorrer se a Conta (Wallet) associada tiver sido deletada antes da validação da chave estrangeira falhar, ou se a Categoria for inválida.

### 2.1.4 Tratamento de Entropia (Falhas e Recuperação)
* **Falha de Escrita no DB Local:** Se o disco encher ou o SQLite retornar `database locked`, uma exceção é lançada e capturada na camada de controller, exibindo um SnackBar reativo sem corromper as estruturas in-memory (já que o Provider depende da veracidade do SQLite para rebuilds, e não de mutação otimista em memória).
* **Entrada Assíncrona de App Links:** Se um deep link tentar registrar uma transação incompleta (ex: falta conta padrão), o sistema roteia o usuário para o formulário pré-preenchido, interceptando o fluxo antes do commit do banco.

### 2.1.5 Filosofia do Design
Este fluxo existe desta forma pois as primeiras versões dependiam de persistência direta, mas o histórico exigiu a inclusão dos marcadores temporais (`dateModified`) e a vinculação rigorosa através de FKs para que a deleção de Contas (Wallets) pudesse refletir ou reatribuir o estado de todas as transações, sem orfaná-las.
