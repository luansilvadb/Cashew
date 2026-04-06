# Capítulo 4: Gerenciamento de Erros e Cenários Adversos Detalhados

Um sistema financeiro resiliente não é aquele que nunca falha, mas aquele que isola a falha e provê remediação clara. Este volume detalha os pontos de pressão sistêmica e como a arquitetura reage.

## 1. Falhas em Nuvem e Resiliência de Sincronização

A API do Google Drive (o *backend* primário de backup) não oferece SLAs inquebráveis ou garantias de ausência de limite de cota (Rate Limits).

### 1.1. O Cenário "Conflito de Merge" Oculto
- **O Problema:** O usuário adiciona \$50 no celular offline, abre o Web App e adiciona \$20 lá. Ambos entram online simultaneamente. Qual arquivo SQLite vence?
- **O Fallback Arquitetural:** Em um banco local complexo, resolver o conflito row-by-row de tabelas SQLite criptografadas em dois lados exigiria um servidor centralizador pesado (violando a premissa #1 do app). O Cashew mitiga isso confiando na preempção do *Timestamp* dos metadados de modificação da Google Drive e alertando o usuário ou efetuando Overwrite agressivo em um dos lados sob a premissa da intenção mais recente do usuário. Para usuários que temem isso, oferecemos o backup frio (CSV).

### 1.2. Interrupções Temporárias de Rede
O sistema falha na coleta de taxas atualizadas (Currency Conversion).
- O `currencyFunctions.dart` carrega um fallback com o último *cache* bem-sucedido. A UI alerta com um indicador visual sutil (como "Taxas desatualizadas: 2 dias atrás") em vez de disparar uma Exception paralisante.

---

## 2. Abortamentos no SQLite e Migrações (Database Breakage)

O maior perigo para o projeto: Quebrar a tabela `Transaction` do usuário.

### 2.1. O *Panic Recovery* nas Migrações
Durante a injeção do `stepByStep()` em `tables.dart`, se o comando ALTER falhar (por ex: restrições de Foregin Key do SQLite antigo), a query aborta inteiramente.
- Em *debug*, lançamos logs vermelhos agressivos. Em produção, paralisamos modificações e a base continua na versão antiga até o *kill/restart* do app.
- **Isolamento de Falha Visual:** A injeção de dependências em Flutter via Provider aguarda que o Drift assinale que está íntegro. Se a integridade não é provada, um Loader de Emergência substitui a árvore visual, protegendo o usuário de tela branca.

---

## 3. Sensores Biométricos com Fallback

### O Problema do `LocalAuth`
Quando o Lock Screen Biométrico está habilitado, a infraestrutura Android/iOS de Biometria pode atuar de forma errática.
- Retorno `null` contínuo por falhas do sensor hardware.
- Senhas do dispositivo mudadas.
- **Como Lidar:** O Cashew (`initializeBiometrics.dart`) engloba chamadas do SDK nativo em `try/catch` que intercedem as falhas de API não documentadas dos fabricantes e sempre forçam um timeout caso o frame de reconhecimento trave. Se travar definitivamente, a persistência permite que o PIN manual do app sempre sobreponha a falha do sensor óptico/ultrassônico.

---

## 4. Estouro Matemático e Formatação Dinâmica

### Representação do Valor em Dart
- Evitamos o estopim trágico dos Floating Points de Javascript onde `0.1 + 0.2 = 0.30000004` (que também afetaria o Dart transpilado para JS para o Cashew na Web) garantindo cálculos por casas decimais estritas no encapsulamento em Classes e não variáveis soltas e dependendo de funções de truncagem monetária padrão no `currencyFunctions.dart` quando salvos.
