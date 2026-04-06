# Como Gerenciar Migrações de Banco de Dados

Sempre que o esquema do banco de dados em `tables.dart` for alterado, você deve realizar uma migração para garantir que os dados dos usuários existentes sejam preservados.

## Passo a Passo da Migração

1. **Alterar o Esquema**
   Faça as mudanças necessárias em `budget/lib/database/tables.dart`.

2. **Incrementar a Versão**
   No arquivo `tables.dart`, incremente a variável global:
   ```dart
   int schemaVersionGlobal = 47; // Exemplo: de 46 para 47
   ```

3. **Gerar Código do Drift**
   Execute o build runner para atualizar os arquivos gerados (`tables.g.dart`):
   ```bash
   cd budget
   dart run build_runner build
   ```

4. **Gerar o Dump do Esquema**
   Crie um snapshot da nova versão:
   ```bash
   dart run drift_dev schema dump lib/database/tables.dart drift_schemas/drift_schema_v47.json
   ```

5. **Gerar Passos de Migração**
   Gere o código necessário para a transição:
   ```bash
   dart run drift_dev schema steps drift_schemas/ lib/database/schema_versions.dart
   ```

6. **Implementar a Estratégia de Migração**
   Edite a função `await stepByStep(...)` em `tables.dart` para incluir a lógica específica da nova versão.

## Dicas de Segurança
- Sempre teste a migração de uma versão antiga para a nova antes de fazer o commit.
- Use os logs do Drift para verificar se as queries de migração (`ALTER TABLE`, etc.) foram executadas corretamente.
