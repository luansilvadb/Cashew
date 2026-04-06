# Capítulo 4: A Ontologia do Dinheiro: Metas e Câmbios

## 4.1 O Paradoxo das Metas (Goals/Objectives) vs Orçamentos
O sistema impõe uma rigorosa divisão de águas entre rastreamento passivo e ativo.

### 4.1.1 Arquitetura da Intenção
* O usuário quer salvar dinheiro para uma compra específica (Goal) e controlar gastos diários (Budget).

### 4.1.2 Mecânica de Processamento
* **Budgets** possuem teto de gastos. Transações limitam seu escopo via restrições de tempo cíclicas (Mensal/Semanal ou Customizado).
* **Objectives (Goals/Long Term Loans)** são potes independentes de tempo. A totalidade é definida pelo vetor oposto:
  * Uma meta "Empréstimo" tem transações matrizes negativas. Os pagamentos das parcelas são transações positivas correlacionadas (associadas por ID).
  * Isso reduz a complexidade do banco, pois não precisamos armazenar um "Estado Total do Empréstimo", e sim apenas projetar os somatórios (views lógicas). `functions.dart` materializa as somas on-the-fly.

### 4.1.3 Tratamento de Entropia
* Se uma transação do "pote" da meta (Goal) perde sua categoria, a associação referencial a meta persiste. O ID de objetivo da transação tem precedência processual sobre a categoria isolada da transação na totalização.

## 4.2 Lógica Assíncrona e Estado Extracelular
Qualquer manipulação massiva (ex: importar CSVs ou transições multi-moedas) lança `Isolates` (através de micro-processos assíncronos no Dart) para não travar a "UI Thread", mantendo os frames de animação fluidos a 60-120hz. O `provider` aguarda os resultados e repassa os pacotes de transações pro repositório Drift de uma vez só, encapsulados num bloco `transaction` (SQL Transaction, não transação financeira) para garantir rollback total em caso de inserção malformada.

### Filosofia de Design da Camada de Erros
A falha em um script importador ou mutação massiva não corrompe o estado anterior do app porque todas as inserções múltiplas em `tables.dart` usam as transações nativas do DB: é _Tudo-ou-Nada_ (Atomicidade garantida no escopo transacional do próprio SQLite).

---
**Fim do Documento Enciclopédico.**
*Esses manuscritos encapsulam o código genético do Cashew App e sua teleologia pragmática, estruturados rigorosamente para habilitar total capacidade operacional autônoma ao engenheiro leitor.*
