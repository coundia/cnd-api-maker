````md
# cnd-api-maker/symfony

Symfony integration for **Cnd API Maker**.

This package builds on top of `cnd-api-maker/core` and provides:
- Symfony Bundle (service registration + config)
- Console commands
- Symfony-specific generators (API Platform resources, entities, fixtures/tests… depending on enabled modules)

## Requirements
- PHP 8.2+
- Symfony 6.4 / 7.x
- `cnd-api-maker/core`
- (optional) `api-platform/core` if you generate API Platform resources

## Install

### Public install (Packagist)
```bash
composer require cnd-api-maker/symfony
````

### Local dev install (monorepo path)

In your Symfony app `composer.json`:

```json
{
  "repositories": [
    { "type": "path", "url": "../cnd-api-maker/packages/cnd-api-maker/core" },
    { "type": "path", "url": "../cnd-api-maker/packages/cnd-api-maker/symfony" }
  ],
  "require": {
    "cnd-api-maker/symfony": "*"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

Then:

```bash
composer update cnd-api-maker/core cnd-api-maker/symfony
```

## Usage

### 1) Create a JDL file

Create a `.jdl` file describing your entities and relationships.

Example: `example.jdl`

```jdl
entity Employee {
  firstName String required
  lastName String required
  email String
}

entity Ticket {
  title String required
}

relationship OneToMany {
  Employee to Ticket{employee}
}
```

You can build JDL visually with JDL Studio:

* [https://start.jhipster.tech/jdl-studio/](https://start.jhipster.tech/jdl-studio/)

###  Generate from JDL

```bash
php bin/console cnd:api-maker:generate --file=example.jdl
```

Common options (depending on your implementation):

* `--force` overwrite generated files
* `--dry-run` preview without writing
* `--module=...` generate into a specific module/namespace (if supported)

### 4) Database + tests

If you generate Doctrine entities/migrations:

```bash
php bin/console doctrine:migrations:migrate
```

Run tests:

```bash
php bin/phpunit
```

## Generated output (typical)

Depending on enabled generators and features, you may get:

* API Platform resources (PHP attributes)
* Doctrine entities + migrations
* DTOs / processors / providers
* Security/RBAC scaffolding
* Tests

Check your output folders (commonly `src/`, `migrations/`, `tests/`).
 

## License

See `composer.json`.

```
::contentReference[oaicite:0]{index=0}
```
