# Cashew - Enciclopédia do Sistema e Wiki Arquitetural

Bem-vindo à Wiki Oficial do **Cashew**, um ecossistema completo de gestão financeira pessoal multiplataforma. Este repositório de conhecimento não é apenas um manual de usuário, mas um **documento arquitetural vivo**, projetado com o rigor de duas décadas de engenharia de software contínua.

## Propósito e Escopo

A finalidade deste guia é fornecer uma **visão onisciente** sobre o sistema. Ele disseca a base de código Flutter/Dart, o modelo de persistência de dados local reativo (Drift/SQLite), os fluxos de sincronização em nuvem e os intrincados mecanismos de interface baseados no Material You. Se você é um engenheiro sênior realizando o *onboarding*, um contribuidor examinando as fundações arquiteturais ou um entusiasta curioso sobre os meandros de um produto de alto rendimento no mundo real, você está no lugar certo.

Priorizamos aqui a **completude didática**. Cada escolha de design – desde por que não usamos Firebase como *backend primary* até a forma como tratamos micro-interações de transações financeiras – é justificada com base em princípios de confiabilidade offline-first e alta performance.

## Estrutura da Wiki

O conhecimento foi rigorosamente segmentado em cinco volumes principais para facilitar o consumo progressivo, da visão sistêmica de alto nível às minúcias operacionais:

1. **[Filosofia e Visão Geral Arquitetural Profunda](01-filosofia-arquitetural.md)**
   A fundação do Cashew. Análise da topologia offline-first, por que utilizamos Drift (SQLite), a escolha do Flutter como motor de renderização universal, gestão de estado global com Provider e os axiomas de design que guiam o desenvolvimento contínuo.

2. **[Jornadas do Usuário Analíticas](02-jornadas-do-usuario.md)**
   Um mapeamento científico da interação homem-máquina no Cashew. Exploração dos fluxos críticos: ingestão de transações de múltipla complexidade (despesas, receitas, empréstimos), o ciclo de vida do planejamento orçamentário e as transições de UI sob o capô.

3. **[Módulos Core e Mecanismos Internos Exaustivos](03-modulos-core.md)**
   A sala de máquinas. Dissecação da modelagem de dados (`database/tables.dart`), do motor de conversão cambial em tempo real, do mecanismo de backup assíncrono via Google Drive API e dos utilitários críticos de tempo/data (`functions.dart`).

4. **[Gerenciamento de Erros e Cenários Adversos Detalhados](04-gerenciamento-erros.md)**
   Como o sistema sobrevive ao caos. Análise de falhas de sincronização na nuvem, conflitos transacionais, isolamento de problemas em migrações de esquema do banco de dados local e o fallback para serviços de conversão fora do ar.

5. **[Proficiência Avançada e Masterização Completa](05-proficiencia-avancada.md)**
   O guia do *Power User* e Administrador do Sistema. Como estender o Cashew através de App Links / Automações nativas, pipelines para extração/ingestão via CSV, compilação de código estático (build_runner/drift_dev) e o roadmap evolutivo previsto.

---
*"A complexidade é mitigada não pela sua eliminação, mas por sua estruturação meticulosa."* — Arquitetura de Software Cashew.
