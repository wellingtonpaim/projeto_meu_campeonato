#!/bin/bash

echo "🚀 Iniciando a configuração do ambiente Meu Campeonato..."

echo "📦 1/5: Baixando dependências do Composer via Docker (isso pode levar alguns minutos na primeira vez)..."
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

echo "⚙️ 2/5: Configurando arquivo .env..."
cp .env.example .env

echo "🐳 3/5: Subindo os containers do Laravel Sail..."
./vendor/bin/sail up -d

echo "⏳ Aguardando o banco de dados iniciar..."
sleep 5

echo "🔑 4/5: Gerando chave da aplicação..."
./vendor/bin/sail artisan key:generate

echo "🗄️ 5/5: Rodando as migrations do banco de dados..."
./vendor/bin/sail artisan migrate

echo "✅ Ambiente configurado com sucesso! A API está pronta para uso."
