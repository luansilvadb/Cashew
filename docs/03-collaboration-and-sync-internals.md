# Capítulo 3: Modos de Colaboração e Sincronização em Massa

A resiliência dos dados não é apenas individual; o sistema cresceu para abraçar a colaboração familiar e distribuída.

## 3.1 O Racional de Compartilhamento via Firebase (`shareBudget.dart`)

### 3.1.1 Arquitetura da Intenção
* **O Gatilho:** Usuários decidem que múltiplos indivíduos devem acessar e manipular o mesmo "Budget".
* **O Modelo Mental:** Edições na carteira de orçamentos por A são instantaneamente (ou quase) percebidos por B, com um único dono contábil (master).

### 3.1.2 Mecânica de Processamento
A colaboração não transita via Google Drive (que é exclusivo para backup pessoal e cross-device do próprio usuário). A infraestrutura de compartilhamento transaciona pelo Firebase (`budget/lib/struct/shareBudget.dart`).
1. **Ativação:** Apenas usuários logados autenticados no Firebase Auth são elegíveis a assinar ou publicar orçamentos em tempo real.
2. **Propagação Local -> Nuvem:** Modificações num "Budget" compartilhado são empacotadas de sua estrutura SQLite para JSON/Map e impulsionadas ao Firebase Firestore.
3. **Consumo Nuvem -> Local:** Clientes escutam (via snapshots do Firebase) as mudanças. Quando o documento na nuvem muta, a instância `shareBudget` força uma conversão descendo da nuvem para o Drift DB do usuário convidado.

### 3.1.3 Micro-Transições de Estado
* `[A altera valor local] -> [Update Drift DB] -> [A Provider Notifica Firebase Sync] -> [Firestore Mutate] -> [B Firestore Stream Active] -> [B Parses to Drift Model] -> [B Atualiza Drift DB (Upsert)] -> [B Provider UI Rebuild]`

### 3.1.4 Tratamento de Entropia (Falhas e Recuperação)
* **Desconexão durante Sync:** Se A perder a rede após editar localmente, o Firebase SDK empilha a gravação offline (`offline persistence`) e despacha assim que o pulso de rede retornar.
* **Resolução de Conflitos Firebase:** Adotamos regras de timestamp (`serverTimestamp`) do Firebase e concorrência last-write-wins sob a responsabilidade do Owner do Budget. Não usamos chaves otimistas incrementais porque orçamentos são vetores temporais mais fluidos.

## 3.2 O Motor de Sincronização Pessoal (`syncClient.dart`)

### 3.2.1 Arquitetura da Intenção
* **Gatilho:** Backup diário, inicialização de novo aparelho ou recuperação de dados perdidos.

### 3.2.2 Mecânica de Processamento
* O motor cria e envia um dump JSON e o esquema SQLite para uma pasta isolada no Google Drive do próprio usuário via API do Google.
* **O "Tombstone Pattern" (DeleteLogs):** Para replicar uma exclusão, não deletamos silenciosamente, pois dispositivos não sincronizados não perceberiam a ausência (ressuscitando a linha). Registramos o ID e a tabela numa estrutura de `DeleteLogs`. Durante o pull (recebimento), o `syncClient` aplica as `DeleteLogs` no banco receptor *antes* de executar upserts (inserções/atualizações) das outras tabelas.
* A arbitragem baseia-se num simples teste: se o registro local `dateModified` for anterior ao remoto correspondente, aplicamos o remoto (Upsert). Se for o inverso, ignoramos a cópia da nuvem, pois nossa versão local é mais pura.

### 3.2.3 Filosofia do Design
O design de `DeleteLogs` (Tombstone) foi inserido num refatoramento histórico, substituindo cópias completas do banco e prevenindo duplicação fantasma ("Ghosting" de transações deletadas), marcando o amadurecimento do sistema como uma verdadeira ferramenta multi-dispositivo off-grid.
