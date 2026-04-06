# Sincronização e Entropia: O Motor Local-First

## 1. Arquitetura da Intenção
A intenção da sincronização é prover a onipresença dos dados financeiros. O usuário espera que o registro de um café no celular apareça instantaneamente no tablet, mantendo uma "Single Source of Truth" distribuída.

## 2. Mecânica de Processamento
O motor de sincronização (`syncClient.dart`) opera sobre o Google Drive (Pasta `appDataFolder`):
- **Geração de Backup Sync:** Cada dispositivo cria um arquivo `sync-[clientID].sqlite` contendo o estado atual do banco Drift.
- **Processamento de Logs de Mudança:** Ao sincronizar, o sistema baixa os arquivos de outros dispositivos, abre-os como `databaseSync` temporários e extrai apenas novos registros posteriores ao `lastSynced` (`getAllNewWallets`, `getAllNewTransactions`, etc).
- **Consumo de DeleteLogs:** O sistema processa a tabela `DeleteLogs` de outros dispositivos para remover localmente registros que foram deletados remotamente.

## 3. Micro-Transições de Estado
O fluxo de sincronização passa por:
- **Idle/Watching:** Escutando mudanças locais (`watchAllForAutoSync`).
- **Debouncing:** Aguardando 5 segundos sem novas mudanças para evitar escritas excessivas na nuvem (`backupDebounce`).
- **Uploading/Merging:** Transição de estado para o `appDataFolder` do Drive.
- **Synced:** Quando o `lastSynced` é atualizado para o `modifiedTime` do arquivo na nuvem.

## 4. Tratamento de Entropia
O combate ao caos ocorre via:
- **Resolução de Conflitos (Last Write Wins):** Baseado no `dateTimeModified` de cada registro. Registros com datas mais recentes sobrepõem os antigos.
- **Timeout de Sincronização:** Timer de 5 segundos (`syncTimeoutTimer`) para evitar múltiplas execuções simultâneas que poderiam corromper a integridade dos dados.
- **Wandering Records Cleanup:** Limpeza de orfãos pós-sync (`deleteWanderingTransactions`, `deleteWanderingTitles`).

## 5. Filosofia do Design
A decisão de usar arquivos SQLite individuais por dispositivo em vez de um banco de dados centralizado em nuvem reflete o compromisso com a privacidade e o funcionamento offline. O sistema é autoritativo localmente, e a sincronização é uma "comunicação entre pares" mediada pela nuvem, garantindo resiliência total mesmo sem conectividade constante.
