# ApiToolkit - A Laravel API Collection Generator

**ApiToolkit** is a Laravel package designed to generate Postman-compatible API collections from your defined routes. It will automatically generate collections with API route details, query parameters, request bodies, headers, and authentication, making API testing and documentation easy.

## Features

- Automatically generates a Postman collection from Laravel API routes.
- Supports routes with `GET`, `POST`, `PUT`, `PATCH`, and `DELETE` methods.
- Generates query parameters, request bodies, and headers based on route documentation.
- Supports authentication headers (e.g., `Bearer {{token}}`).
- Outputs a JSON file that can be directly imported into Postman.

## Installation

To install **ApiToolkit**, follow these steps:

### 1. Install via Composer

In your Laravel project, run:

```bash
composer require judeufuoma/api-toolkit
```
### 2. Generate the Postman Collection

Once installed, you can generate the Postman collection by running the following command:

```bash
php artisan api-toolkit:generate
```
This command will:
- Scan your Laravel routes.
- Generate a Postman collection JSON file.
- Include all routes prefixed with api/

### Usage
The ApiToolkit will generate a postman_collection.json file in your project’s root directory. You can then import this file into Postman to get a detailed view of your API, including routes, query parameters, request bodies, and headers.

### Testing

If you’re contributing to the development of this package or need to run tests, use PHPUnit:

```bash
vendor/bin/phpunit
```

Alternatively, if you need to run tests within the Laravel app that uses the package, run:

```bash
php artisan test
```

### Contributing
We welcome contributions! If you have suggestions or improvements, please fork the repository and submit a pull request.
If you find any bugs or want to add a feature, please create an issue on GitHub.


