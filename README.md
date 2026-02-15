# 🏆 API Meu Campeonato (Simulador de Várzea)

Uma API RESTful desenvolvida como solução para o desafio técnico de simulação de campeonatos de futebol. A aplicação permite a criação de torneios de 8 times, inscrição controlada de participantes, chaveamento automático (Quartas, Semis, Terceiro Lugar e Final) e integração com um script externo em Python para simulação de resultados.

## 🚀 Tecnologias Utilizadas

* **PHP 8.x** com **Laravel 11** (Framework base)
* **PostgreSQL** (Banco de dados relacional)
* **Docker & Laravel Sail** (Orquestração de containers e ambiente isolado)
* **Python 3** (Script externo para mock de Inteligência Artificial/Geração de resultados)
* **Padrões de Projeto & Boas Práticas:** Clean Code, Service Pattern, Early Return com Spaceship Operator (<=>), Single Responsibility Principle (SRP).

## 🧠 Destaques da Arquitetura e Regras de Negócio

1. **Integração com Python (Mock de ML):** O sistema utiliza o componente Symfony\Component\Process para acionar nativamente um script Python (teste.py) dentro do container Docker. Esse script simula a engine de uma partida, gerando gols, cartões e cobranças de pênaltis.
2. **Cascata de Desempate Avançada:** Em caso de empate no tempo normal, a aplicação segue rigorosamente os critérios abaixo usando o Spaceship Operator para um código limpo e escalável:
    * Critério 1: Saldo de Gols / Pontuação acumulada ao longo do campeonato.
    * Critério 2: Fair Play (Time com menos cartões amarelos na partida avança).
    * Critério 3: Disputa de Pênaltis (gerada pelo script Python).
    * Critério 4: Ordem de Inscrição (O carimbo de tempo created_at na tabela pivô define quem se inscreveu primeiro).
3. **Controle de Estado:** O campeonato nasce como "pending", vai para "in_progress" e termina como "finished", impedindo mutações indevidas (como tentar simular um campeonato já finalizado ou com menos de 8 times).

---

## ⚙️ Como Executar o Projeto Localmente

O projeto foi totalmente containerizado com o Laravel Sail. Você NÃO PRECISA ter o PHP ou o Composer instalados na sua máquina host, apenas o Docker e o Git.

### Opção 1: Instalação Expressa (Recomendada)
Para a melhor experiência de DevEx, desenvolvi um script de automação que provisiona todo o ambiente com um único comando (baixa dependências, configura variáveis, sobe containers e roda migrations).

1. Pelo terminal/cmd clone o repositório e entre na pasta com os comandos a seguir:

   git clone https://github.com/wellingtonpaim/projeto_meu_campeonato.git
   cd projeto_meu_campeonato

2. Dê permissão de execução e rode o script de setup:

   chmod +x setup.sh
   ./setup.sh

### Opção 2: Instalação Passo a Passo (Manual)
Caso prefira rodar os comandos individualmente para entender o processo de provisionamento:

1. Clone o repositório e entre na pasta do projeto:

   git clone https://github.com/wellingtonpaim/projeto_meu_campeonato.git
   cd projeto_meu_campeonato

2. Baixe as dependências do Laravel via container temporário:

   docker run --rm \
   -u "$(id -u):$(id -g)" \
   -v "$(pwd):/var/www/html" \
   -w /var/www/html \
   laravelsail/php83-composer:latest \
   composer install --ignore-platform-reqs

3. Crie o arquivo de configuração e suba os containers em segundo plano:

   cp .env.example .env
   ./vendor/bin/sail up -d

4. Gere a chave da aplicação e crie as tabelas do banco de dados:

   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate

---

## 📡 Endpoints da API

A aplicação roda por padrão na porta 80 ou 8080 (dependendo da sua configuração do Docker, verifique no arquivo .env a variável APP_PORT).

### 1. Criar um Campeonato
Cria a estrutura inicial do torneio.
* POST /api/championships
* Body (JSON): {"name": "Copa do Bairro 2026"}

### 2. Inscrever Time
Inscreve um time por vez. O limite arquitetural é de 8 times. A 9ª tentativa retornará um erro 422.
* POST /api/championships/{id}/enroll
* Body (JSON): {"name": "Flamengo"}

### 3. Simular o Campeonato
Executa o chaveamento, aciona o script Python para os resultados, aplica as regras de desempate e coroa o campeão.
* POST /api/championships/{id}/simulate

### 4. Histórico de Campeonatos
Lista todos os campeonatos passados ordenados do mais recente para o mais antigo, com o respectivo time campeão.
* GET /api/championships

---

## 🧪 Testes Automatizados

O projeto conta com uma suíte de testes que cobre tanto a lógica de negócio quanto a integridade dos dados, utilizando **PHPUnit**.

### Cobertura de Testes:
- **Feature Tests:** Validação de regras de negócio (limite de 8 times, fluxo de inscrição e persistência).
- **Unit Tests:** Validação de lógica pura e contratos de dados (processamento de JSON e cálculos matemáticos).

Para rodar os testes, utilize o comando:

./vendor/bin/sail artisan test

