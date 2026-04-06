# Capítulo 2: Jornadas do Usuário Analíticas

Neste volume, dissecamos as artérias principais da aplicação através das quais o usuário e os dados transitam. Estas jornadas representam fluxos mapeados end-to-end, documentando a fricção mínima no design da interface e a mecânica pesada processada em background.

## 1. O Motor de Ingestão de Transações

O ato de adicionar uma transação no Cashew é sua função atômica fundamental e, por consequência, a mais altamente otimizada, modelada para requerer o menor número de toques (taps) em tela.

### 1.1. Fluxo de Entrada e Categorização
- **Micro-interação:** Quando o usuário invoca o modal (Sliding Sheet ou Tela cheia) de Adicionar Transação, o teclado (numérico ou alfanumérico baseado no input foco) sobe imediatamente (auto-focus).
- **Auto-Complete Preditivo:** Baseado em histórico armazenado (`Title` da transação). O sistema escaneia transações passadas em `tables.dart`. Se o usuário digita "Uber", o aplicativo não só sugere a string, como **vincula imediatamente à Categoria** usada anteriormente (ex: Transporte) e o tipo (Despesa). Isto corta três passos cognitivos.
- **Estruturas de Dados:** Uma transação não é apenas um número, mas um pacote de enumerações flexíveis.
  - **ExpenseIncome Enum:** Define a polaridade.
  - **TransactionSpecialType:** A magia real. Define se é comum, uma assinatura, um pagamento agendado (Upcoming) ou dívida/empréstimo.

### 1.2. Resolução de Empréstimos e Créditos (Long Term Loans)
Em empréstimos, a arquitetura inverte o fluxo tradicional de uma despesa.
- Quando se empresta \$100, registra-se uma *Expense* de valor absoluto negativo e vincula-se um **Goal/Objective** subjacente invisível para a maioria das lógicas, usado unicamente como âncora de agrupamento.
- **Resolução Matemática:** O total de um objetivo de empréstimo não é guardado como um campo estático de banco. É **derivado computacionalmente** somando as polaridades opostas de todas as transações daquele objetivo. (Ex: \$100 Expense + \$50 Income de pagamento parcial = Saldo Dinâmico do Empréstimo de -\$50).

---

## 2. A Gestão de Orçamento (Budgeting Engine)

Budgets (Orçamentos) no Cashew são incrivelmente complexos, permitindo ao usuário definir o espaço-tempo de restrição financeira.

### 2.1. O Ciclo de Vida do Budget
- **Criação Temporal Dinâmica:** Um budget pode ser Mensal, Semanal, ou `Custom` (uma data arbitrária de viagem, ex: de 15 a 22 de Novembro).
- **Inscrição de Categoria e Limits:** Um budget agrega N categorias. Cada categoria pode ter um sub-limite. O sistema usa *Providers* para armazenar temporariamente na memória a topologia desse budget em formação antes do *commit* atômico no banco.

### 2.2. Avaliação de Gasto em Tempo Real
- Como o sistema sabe o quanto você gastou? Quando a Home (Dashboard) é carregada, as lógicas em `database.watchAllTransactions()` cruzam todas as transações do mês com as *Foreign Keys* de categorias indexadas no *Budget*.
- Se a transação tem a Flag de pertencer àquele budget, ou pertence às categorias agregadas nele, a UI reage repintando a barra de progresso do budget no `homePageLineGraph.dart`. A lógica de porcentagem cuida de não ultrapassar 100% no visual, mas notifica em vermelho o *Overspending*.

---

## 3. Comutador Cambial Global (Multi-Currency e Accounts)

O usuário médio tem múltiplas contas, às vezes em moedas distintas (ex: Conta Corrente em USD e uma Carteira Física em EUR).

### 3.1. A Conversão Silenciosa (Currency Conversion API)
- **Estruturas Internas (`currencyFunctions.dart`):** O sistema busca taxas de câmbio atualizadas. Porém, os valores bases no banco (`tables.dart`) são armazenados em suas moedas originais onde a transação foi lançada.
- **O Switch da Dashboard:** Quando o usuário troca de "Conta A" (USD) para "Conta B" (EUR) no seletor do cabeçalho da Home, o sistema dispara um re-cálculo global instântaneo (O(N) rápido em Dart graças à JIT). O saldo visualizado não é o banco mutacionando; é a *ViewLayer* aplicando o fator de conversão cambial em tempo real em todas as *Streams* passantes.
- Assim, mantém-se a pureza do dado inserido originalmente.

---

## 4. Jornada de Sincronização Transparente (Cross-Device)

O calcanhar de Aquiles das aplicações offline. Como o Cashew trata o Sync?
1. O usuário autentica no Google Drive usando Firebase Auth apenas como vetor inicial.
2. O banco Drift/SQLite local emite os eventos de criação ou modificação em lotes.
3. Em background (`syncClient.dart`), se uma mudança estrutural foi cometida com sucesso, a engine gera um `.sqlite` físico temporário e usa chamadas RESTful diretas da Google Drive API ou mecanismos Delta para sobrescrever silenciosamente no arquivo na nuvem de propriedade apenas do usuário (não há servidor central coletando dados globais da base do Cashew).
4. No dispositivo secundário, um `watchdog` verifica a alteração do arquivo no Drive via webhooks ou polling intermitente e promove um *Merge* ou *Pull* forçado, disparando um *hot-reload* suave de dados da interface.
