<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Carrinho API

Este projeto é uma API RESTful desenvolvida em Laravel 12, utilizando o [Laravel Sail](https://laravel.com/docs/12.x/sail) para facilitar o ambiente de desenvolvimento com Docker.

# Endpoints da API

Abaixo estão listadas as principais rotas RESTful disponíveis nesta API, exemplos de uso e payloads esperados.

## Cart Items

| Método | Rota                | Descrição                        |
|--------|---------------------|----------------------------------|
| GET    | /api/cart-items     | Lista todos os itens do carrinho |
| POST   | /api/cart-items     | Cria um novo item no carrinho    |
| GET    | /api/cart-items/{id}| Detalha um item do carrinho      |
| PUT    | /api/cart-items/{id}| Atualiza um item do carrinho     |
| DELETE | /api/cart-items/{id}| Remove um item do carrinho       |

### Exemplo: Criar item no carrinho

**POST /api/cart-items**
```json
{
  "name": "Produto X",
  "price": 99.90,
  "quantity": 2
}
```

**Resposta 201**
```json
{
  "data": {
    "id": 1,
    "name": "Produto X",
    "price": 99.9,
    "quantity": 2,
    "created_at": "2025-06-18 12:00:00",
    "updated_at": "2025-06-18 12:00:00"
  }
}
```

### Exemplo: Listar itens do carrinho

**GET /api/cart-items**

**Resposta 200**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Produto X",
      "price": 99.9,
      "quantity": 2,
      "created_at": "2025-06-18 12:00:00",
      "updated_at": "2025-06-18 12:00:00"
    }
  ]
}
```

### Exemplo: Atualizar item do carrinho

**PUT /api/cart-items/1**
```json
{
  "quantity": 3
}
```

### Exemplo: Remover item do carrinho

**DELETE /api/cart-items/1**

**Resposta:** 204 No Content

---

## Pagamento

| Método | Rota         | Descrição                |
|--------|--------------|--------------------------|
| POST   | /api/payment | Processa um pagamento    |

### Exemplo: Pagamento com PIX

**POST /api/payment**
```json
{
  "payment_method": "pix",
  "qtd_installments": 1
}
```

**Resposta 200**
```json
{
  "message": "Pix payment processed successfully",
  "data": {
    "payment_method": "pix",
    "qtd_installments": 1,
    "amount": 89.91
  }
}
```

### Exemplo: Pagamento com Cartão de Crédito

**POST /api/payment**
```json
{
  "payment_method": "credit_card",
  "qtd_installments": 2,
  "card_information": {
    "card_holder_name": "Test User",
    "card_number": "4111111111111111",
    "expiration_date": "12/25",
    "cvv": "123"
  }
}
```

**Resposta 200**
```json
{
  "message": "Credit card payment processed successfully",
  "data": {
    "payment_method": "credit_card",
    "qtd_installments": 2,
    "amount": 199.80
  }
}
```

> Os campos de cartão são obrigatórios apenas quando `payment_method` for `credit_card`.

## Pré-requisitos
- Docker instalado e rodando
- Docker Compose instalado

## Passos para subir o projeto

1. **Copie o arquivo de variáveis de ambiente:**
   ```sh
   cp .env.example .env
   ```

2. **Instale as dependências do Composer:**
   ```sh
   ./vendor/bin/sail composer install
   ```
   > Se ainda não tiver o Sail instalado, rode:
   > ```sh
   > composer require laravel/sail --dev
   > php artisan sail:install
   > ```

3. **Suba os containers:**
   ```sh
   ./vendor/bin/sail up -d
   ```

4. **Gere a key do Laravel:**
   ```sh
   ./vendor/bin/sail artisan key:generate
   ```

5. **Rode as migrations:**
   ```sh
   ./vendor/bin/sail artisan migrate
   ```

6. **Acesse a API:**
   - Acesse [http://localhost](http://localhost) no navegador ou utilize ferramentas como Postman/Insomnia para consumir os endpoints.

## Comandos úteis
- Parar os containers:
  ```sh
  ./vendor/bin/sail down
  ```
- Acessar o container:
  ```sh
  ./vendor/bin/sail shell
  ```
- Rodar testes:
  ```sh
  ./vendor/bin/sail artisan test
  ```

---

Para mais informações, consulte a [documentação oficial do Laravel Sail](https://laravel.com/docs/12.x/sail).
