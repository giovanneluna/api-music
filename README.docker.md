# Clube do Tião API - Docker Setup

Este é o guia de configuração do Docker para o backend Laravel da aplicação Clube do Tião.

## Pré-requisitos

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)
- Configuração do arquivo hosts

## Configuração do arquivo hosts

Adicione a seguinte linha ao seu arquivo hosts (/etc/hosts no Linux/Mac ou C:\Windows\System32\drivers\etc\hosts no Windows):

```
127.0.0.1 api.musictest.localhost
```

## Estrutura do Docker

```
api-music/
├── docker/                  # Configurações Docker
│   ├── nginx/               # Configurações Nginx
│   │   └── conf.d/          # Configurações de domínio
│   └── php/                 # Configurações PHP
├── docker-compose.yml       # Configuração Docker Compose
└── .env.docker              # Variáveis de ambiente para Docker
```

## Instalação e Execução

1. **Configuração do ambiente Docker**

```bash
cp .env.docker .env
```

2. **Construa e inicie os containers**

```bash
docker-compose up -d
```

3. **Configure o Laravel dentro do container**

```bash
# Entre no container do Laravel
docker exec -it music-api bash

# Instale as dependências
composer install

# Gere a chave da aplicação
php artisan key:generate

# Execute as migrações
php artisan migrate --seed

# Saia do container
exit
```

4. **Acesse o projeto**

- Backend API: [http://api.musictest.localhost](http://api.musictest.localhost)
- PHPMyAdmin: [http://localhost:8080](http://localhost:8080)

## Comandos Úteis

- **Iniciar os containers**

```bash
docker-compose up -d
```

- **Verificar logs**

```bash
docker-compose logs -f
```

- **Parar os containers**

```bash
docker-compose down
```

- **Executar comandos no container**

```bash
docker exec -it music-api bash
```

## Acesso ao Banco de Dados

- **Via PHPMyAdmin**
  - URL: [http://localhost:8080](http://localhost:8080)
  - Servidor: db
  - Usuário: root
  - Senha: root_password

- **Via MySQL Client**
  - Host: localhost
  - Porta: 3306
  - Banco de Dados: musicas
  - Usuário: music_user
  - Senha: music_password 
