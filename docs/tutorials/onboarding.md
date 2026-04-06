# Onboarding do Desenvolvedor

Bem-vindo ao projeto Cashew! Este guia ajudará você a configurar seu ambiente de desenvolvimento e executar o aplicativo pela primeira vez.

## Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- [Flutter SDK](https://docs.flutter.dev/get-started/install) (versão estável mais recente)
- [Dart SDK](https://dart.dev/get-started/sdk)
- Um IDE compatível (VS Code ou Android Studio com plugins Flutter/Dart)
- Android SDK (para desenvolvimento Android)
- Xcode (apenas para macOS, para desenvolvimento iOS)

## Configuração do Ambiente

1. **Clonar o Repositório**
   ```bash
   git clone https://github.com/jameskokoska/Cashew.git
   cd Cashew
   ```

2. **Instalar Dependências**
   Navegue até o diretório `budget` e instale os pacotes:
   ```bash
   cd budget
   flutter pub get
   ```

3. **Gerar Código (Drift/EasyLocalization)**
   O Cashew utiliza geração de código para o banco de dados e localização:
   ```bash
   dart run build_runner build --delete-conflicting-outputs
   ```

## Executando o Aplicativo

Para iniciar o aplicativo em um emulador ou dispositivo conectado:

```bash
flutter run
```

## Estrutura do Projeto

- `budget/lib/main.dart`: Ponto de entrada do aplicativo.
- `budget/lib/database/`: Definições de tabelas e lógica do Drift.
- `budget/lib/pages/`: Telas e interface do usuário.
- `budget/lib/widgets/`: Componentes reutilizáveis.

Para mais detalhes sobre a arquitetura, consulte o [Guia de Arquitetura](../explanation/architecture.md).
