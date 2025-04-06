# API Music - Sistema de Gerenciamento de Músicas

Este é um sistema de gerenciamento de músicas que permite aos usuários sugerir músicas do YouTube, e aos administradores aprovar ou rejeitar essas sugestões.

## Requisitos

- Apartir do PHP 8.2
- Composer
- MySQL
- Extensões PHP requeridas: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## Instalação

1. Clone o repositório
   ```
   git clone https://github.com/giovanneluna/api-music.git
   cd api-music
   ```

2. Instale as dependências
   ```
   composer install
   ```

3. Configure o ambiente
   - Copie o arquivo `.env.example` para `.env`
   - Configure as variáveis de banco de dados no arquivo `.env`

4. Execute migrações e seeders
   ```
   php artisan migrate && php artisan db:seed
   ```

5. Inicie o servidor de desenvolvimento
   ```
   php artisan serve
   ```

## Usuário Administrador

Um usuário administrador é automaticamente criado pelo seeder:

- **Email**: admin@sistema.com
- **Senha**: senha_segura

## Comandos Úteis

### Promover um usuário para administrador

```
php artisan user:promote usuario@exemplo.com
```

### Executar testes

Para rodar todos os testes:
```
php artisan test
```

Para rodar testes específicos:
```
php artisan test --filter=AuthControllerTest
php artisan test --filter=MusicControllerTest
php artisan test --filter=SuggestionControllerTest
php artisan test --filter=SuggestionStatusControllerTest
```

## Endpoints da API

### Autenticação

#### Registro de Usuário
- **URL**: `/api/auth/register`
- **Método**: `POST`
- **Autenticação**: Não
- **Body**:
  ```json
  {
    "name": "Nome do Usuário",
    "email": "usuario@exemplo.com",
    "password": "senha123",
    "password_confirmation": "senha123"
  }
  ```
- **Resposta (201)**:
  ```json
  {
    "status": "success",
    "message": "Usuario registrado com sucesso",
    "data": {
      "id": 1,
      "name": "Nome do Usuário",
      "email": "usuario@exemplo.com",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "is_admin": false
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz"
  }
  ```

#### Login
- **URL**: `/api/auth/login`
- **Método**: `POST`
- **Autenticação**: Não
- **Body**:
  ```json
  {
    "email": "usuario@exemplo.com",
    "password": "senha123"
  }
  ```
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "message": "Login realizado com sucesso",
    "data": {
      "id": 1,
      "name": "Nome do Usuário",
      "email": "usuario@exemplo.com",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "is_admin": false
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz"
  }
  ```

#### Obter Usuário Atual
- **URL**: `/api/auth/user`
- **Método**: `GET`
- **Autenticação**: Sim
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "data": {
      "id": 1,
      "name": "Nome do Usuário",
      "email": "usuario@exemplo.com",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "is_admin": false
    }
  }
  ```

#### Logout
- **URL**: `/api/auth/logout`
- **Método**: `POST`
- **Autenticação**: Sim
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "message": "Usuario deslogado com sucesso"
  }
  ```

### Músicas

#### Listar Músicas
- **URL**: `/api/musics`
- **Método**: `GET`
- **Autenticação**: Não
- **Parâmetros**:
  - `per_page` (opcional): Quantidade de itens por página
  - `page` (opcional): Número da página
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "data": [
      {
        "id": 1,
        "title": "Título da Música",
        "youtube_id": "abcdefghijk",
        "views": 5000,
        "thumbnail": "https://img.youtube.com/vi/abcdefghijk/hqdefault.jpg",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    },
    "links": {
      "first": "http://localhost:8000/api/musics?page=1",
      "last": "http://localhost:8000/api/musics?page=1",
      "next": null,
      "prev": null
    }
  }
  ```

#### Detalhes da Música
- **URL**: `/api/musics/{id}`
- **Método**: `GET`
- **Autenticação**: Não
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "data": {
      "id": 1,
      "title": "Título da Música",
      "youtube_id": "abcdefghijk",
      "views": 5000,
      "views_formatted": "5K",
      "thumbnail": "https://img.youtube.com/vi/abcdefghijk/hqdefault.jpg",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  }
  ```

#### Adicionar Música (Admin)
- **URL**: `/api/musics`
- **Método**: `POST`
- **Autenticação**: Sim (Admin)
- **Body**:
  ```json
  {
    "title": "Título da Música",
    "youtube_id": "abcdefghijk",
    "views": 5000,
    "thumbnail": "https://img.youtube.com/vi/abcdefghijk/hqdefault.jpg"
  }
  ```
- **Resposta (201)**:
  ```json
  {
    "status": "success",
    "message": "Música adicionada com sucesso",
    "data": {
      "id": 1,
      "title": "Título da Música",
      "youtube_id": "abcdefghijk",
      "views": 5000,
      "thumbnail": "https://img.youtube.com/vi/abcdefghijk/hqdefault.jpg",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  }
  ```

#### Atualizar Música (Admin)
- **URL**: `/api/musics/{id}`
- **Método**: `PATCH`
- **Autenticação**: Sim (Admin)
- **Body**:
  ```json
  {
    "title": "Novo Título da Música",
    "views": 6000
  }
  ```
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "message": "Música atualizada com sucesso",
    "data": {
      "id": 1,
      "title": "Novo Título da Música",
      "youtube_id": "abcdefghijk",
      "views": 6000,
      "views_formatted": "6K",
      "thumbnail": "https://img.youtube.com/vi/abcdefghijk/hqdefault.jpg",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  }
  ```

#### Excluir Música (Admin)
- **URL**: `/api/musics/{id}`
- **Método**: `DELETE`
- **Autenticação**: Sim (Admin)
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "message": "Música excluída com sucesso"
  }
  ```

#### Atualizar Dados do YouTube (Admin)
- **URL**: `/api/musics/{id}/refresh`
- **Método**: `POST`
- **Autenticação**: Sim (Admin)
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "message": "Dados do vídeo atualizados com sucesso",
    "data": {
      "id": 1,
      "title": "Título da Música",
      "youtube_id": "abcdefghijk",
      "views": 10000,
      "views_formatted": "10K",
      "likes": 1000,
      "likes_formatted": "1K",
      "thumbnail": "https://img.youtube.com/vi/abcdefghijk/hqdefault.jpg",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  }
  ```

### Informações de Vídeo do YouTube

#### Obter Informações de Vídeo (Para Sugestões)
- **URL**: `/api/youtube/info`
- **Método**: `POST`
- **Autenticação**: Sim
- **Body**:
  ```json
  {
    "youtube_url": "https://www.youtube.com/watch?v=abcdefghijk"
  }
  ```
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "data": {
      "titulo": "Título do Vídeo",
      "visualizacoes": 5000,
      "youtube_id": "abcdefghijk",
      "thumb": "https://img.youtube.com/vi/abcdefghijk/hqdefault.jpg"
    }
  }
  ```

### Sugestões

#### Listar Sugestões
- **URL**: `/api/suggestions`
- **Método**: `GET`
- **Autenticação**: Sim
- **Observação**: Usuários normais veem apenas suas próprias sugestões, admins veem todas
- **Parâmetros**:
  - `per_page` (opcional): Quantidade de itens por página
  - `status` (opcional): Filtrar por status ('pending', 'approved', 'rejected')
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "data": {
      "data": [
        {
          "id": 1,
          "url": "https://www.youtube.com/watch?v=abcdefghijk",
          "youtube_id": "abcdefghijk",
          "title": "Título da Sugestão",
          "status": "pending",
          "user_id": 1,
          "music_id": null,
          "reason": null,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z",
          "user": {
            "id": 1,
            "name": "Nome do Usuário",
            "email": "usuario@exemplo.com"
          }
        }
      ],
      "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 1
      },
      "links": {
        "first": "http://localhost:8000/api/suggestions?page=1",
        "last": "http://localhost:8000/api/suggestions?page=1",
        "next": null,
        "prev": null
      }
    },
    "is_admin": false
  }
  ```

#### Criar Sugestão
- **URL**: `/api/suggestions`
- **Método**: `POST`
- **Autenticação**: Sim
- **Body**:
  ```json
  {
    "url": "https://www.youtube.com/watch?v=abcdefghijk"
  }
  ```
- **Resposta (201)**:
  ```json
  {
    "status": "success",
    "message": "Sugestão enviada com sucesso",
    "data": {
      "id": 1,
      "url": "https://www.youtube.com/watch?v=abcdefghijk",
      "youtube_id": "abcdefghijk",
      "title": "Título do Vídeo do YouTube",
      "status": "pending",
      "user_id": 1,
      "music_id": null,
      "reason": null,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  }
  ```

#### Ver Detalhes da Sugestão
- **URL**: `/api/suggestions/{id}`
- **Método**: `GET`
- **Autenticação**: Sim
- **Observação**: Usuários podem ver apenas suas próprias sugestões, admins podem ver todas
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "data": {
      "id": 1,
      "url": "https://www.youtube.com/watch?v=abcdefghijk",
      "youtube_id": "abcdefghijk",
      "title": "Título da Sugestão",
      "status": "pending",
      "user_id": 1,
      "music_id": null,
      "reason": null,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "Nome do Usuário",
        "email": "usuario@exemplo.com"
      }
    }
  }
  ```

#### Excluir Sugestão
- **URL**: `/api/suggestions/{id}`
- **Método**: `DELETE`
- **Autenticação**: Sim
- **Observação**: Usuários podem excluir apenas suas próprias sugestões pendentes, admins podem excluir qualquer sugestão pendente
- **Resposta (200)**:
  ```json
  {
    "status": "success",
    "message": "Sugestão excluída com sucesso"
  }
  ```

#### Atualizar Status da Sugestão (Admin)
- **URL**: `/api/suggestions/{id}/status/{status}`
- **Método**: `POST`
- **Autenticação**: Sim (Admin)
- **Parâmetros**:
  - `status`: 'approved' ou 'rejected'
- **Body (para rejeição)**:
  ```json
  {
    "motivo": "Motivo da rejeição"
  }
  ```
- **Resposta (200) para aprovação**:
  ```json
  {
    "status": "success",
    "message": "Sugestão aprovada com sucesso",
    "data": {
      "id": 1,
      "url": "https://www.youtube.com/watch?v=abcdefghijk",
      "youtube_id": "abcdefghijk",
      "title": "Título da Sugestão",
      "status": "approved",
      "user_id": 1,
      "music_id": 1,
      "reason": "Motivo opcional da aprovação",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "music": {
        "id": 1,
        "title": "Título da Música",
        "youtube_id": "abcdefghijk",
        "views": 5000,
        "thumbnail": "https://img.youtube.com/vi/abcdefghijk/hqdefault.jpg"
      }
    }
  }
  ```
- **Resposta (200) para rejeição**:
  ```json
  {
    "status": "success",
    "message": "Sugestão rejeitada com sucesso",
    "data": {
      "id": 1,
      "url": "https://www.youtube.com/watch?v=abcdefghijk",
      "youtube_id": "abcdefghijk",
      "title": "Título da Sugestão",
      "status": "rejected",
      "user_id": 1,
      "music_id": null,
      "reason": "Motivo da rejeição",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  }
  ```

## Notas Adicionais

- Todas as rotas de API retornam respostas no formato JSON
- A autenticação é feita através de token Bearer usando Laravel Sanctum
- Os administradores têm acesso a todas as funcionalidades, enquanto usuários normais têm acesso limitado
- As sugestões de músicas passam por um processo de validação de URL do YouTube
