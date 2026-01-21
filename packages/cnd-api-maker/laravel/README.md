````md
# cnd-api-maker/laravel

Laravel integration for **Cnd API Maker**.

This package builds on top of `cnd-api-maker/core` and provides:
- Laravel Service Provider (auto-discovered)
- Artisan commands
- Laravel generators (migrations, models, factories, seeders, tests… depending on enabled modules)
- API Platform (Laravel) resource generation (if `api-platform/laravel` is installed)

## Requirements
- PHP 8.2+
- Laravel 10 / 11 / 12
- `cnd-api-maker/core`
- (optional) `api-platform/laravel` if you generate API Platform resources

## Install

### Public install (Packagist)
```bash
composer require cnd-api-maker/laravel
````

### Local dev install (monorepo path)

In your Laravel app `composer.json`:

```json
{
  "repositories": [
    { "type": "path", "url": "../cnd-api-maker/packages/cnd-api-maker/core" },
    { "type": "path", "url": "../cnd-api-maker/packages/cnd-api-maker/laravel" }
  ],
  "require": {
    "cnd-api-maker/laravel": "*"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

Then:

```bash
composer update cnd-api-maker/core cnd-api-maker/laravel
```

## Laravel registration

Auto-discovery is enabled via:

```json
"extra": {
  "laravel": {
    "providers": [
      "CndApiMaker\\Laravel\\ApiPlatformMakerServiceProvider"
    ]
  }
}
```

If auto-discovery is disabled, register the provider manually in `config/app.php`.

## Configuration
 

## Quick start

### install

- 1 create a laravel project 

`composer create-project laravel/laravel starter-laravel` 

- 2 create a laravel project

`composer require cnd-api-maker/laravel`

### 1) Create a JDL file

Create a `.jdl` file describing your entities and relationships.

Example: `example.jdl`

```jdl
entity Employee {
  firstName String required
  lastName String required
  email String
  phoneNumber String
  hireDate Instant
  salary Long
  commissionPct Long
}

entity Ticket {
  title String required
  due Long
}

relationship OneToMany {
  Employee to Ticket{employee}
}
```

Generate JDL using **JHipster JDL Studio**:

* [https://start.jhipster.tech/jdl-studio/](https://start.jhipster.tech/jdl-studio/)

### 2) Install the generator (one-time)

```bash
php artisan cnd:api-maker:install --force
```

### 3) Generate from JDL

```bash
php artisan cnd:api-maker:generate --file=example.jdl
```

Common options (depending on your implementation):

* `--force` overwrite generated files
* `--dry-run` preview without writing
* `--module=...` generate into a specific module/namespace (if supported)

### 4) Run database + tests

```bash
php artisan migrate
php artisan test
```

## Generated output (example: multi-tenant + RBAC starter)

### API / Config

* `app/ApiResource/Health.php`
* `config/api-platform.php`
* `bootstrap/app.php`
* `bootstrap/providers.php`

### Console / Providers

* `app/Console/Commands/GeneratePermissionsCommand.php`
* `app/Providers/TenancyServiceProvider.php`

### Tenancy

* `app/Tenancy/TenantContext.php`
* `app/Models/Concerns/TenantOwned.php`
* `app/Tenancy/Http/Middleware/Authenticate.php`
* `app/Tenancy/Http/Middleware/ResolveTenant.php`

### Security / Auth / RBAC

* RBAC:

	* `app/Security/Rbac/PermissionChecker.php`
	* `app/Security/Rbac/GrantsRbacPermissions.php`
	* `app/Security/Rbac/GrantsRbacPermissionsTenant.php`
* Auth API:

	* `app/Models/AuthResource.php`
	* `app/Dto/Auth/*`
	* `app/State/Auth/*`
	* `tests/Feature/Security/AuthApiTest.php`

### CRUD API (DTO + State layer)

* DTOs:

	* `app/Dto/{Tenant,Role,Permission,RolePermission,UserRole}/*`
* State:

	* `app/State/{Tenant,Role,Permission,RolePermission,UserRole}/*`

### Eloquent Models + Factories

* Models:

	* `app/Models/{Tenant,Role,Permission,RolePermission,User,UserRole}.php`
* Factories:

	* `database/factories/{Tenant,Role,Permission,RolePermission,UserRole}Factory.php`

### Database

* Migrations:

	* `database/migrations/0001...0006_*`
* Seeders:

	* `database/seeders/{SecuritySeederTenant,DatabaseSeeder}.php`

### Tests

* `tests/Support/BaseApiTestCase.php`
* `tests/Feature/*ApiTest.php`

## Notes

### API Platform endpoints

If you generate API Platform resources, your documentation and endpoints depend on your `api-platform` configuration.
Check `config/api-platform.php` and the generated resources under `app/ApiResource`.

### Regenerating code

* Use `--dry-run` to preview changes.
* Use `--force` to overwrite files when you intentionally want to regenerate.

## Versioning

SemVer tags: `vMAJOR.MINOR.PATCH`

## License

See `composer.json`.

```
::contentReference[oaicite:0]{index=0}
```
