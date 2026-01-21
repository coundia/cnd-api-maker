
# Cnd API Maker (Monorepo)

Monorepo that contains multiple Composer packages:

- `cnd-api-maker/core`
- `cnd-api-maker/laravel`
- `cnd-api-maker/symfony`

Goal: develop everything in one repository, while publishing each package as a standalone installable Composer package.


## Development

### Requirements

* PHP 8.2+
* Composer 2.x
* Git

### Install monorepo dependencies

From the monorepo root:

```bash
composer install
```

### Coding standards / tests

Adjust to your tooling. Example:

```bash
composer test
composer pint
```

## Public distribution strategy (Packagist)

Packagist.org indexes **one package per repository** (expects `composer.json` at repo root).
This monorepo uses **subtree split** to publish each package into its own public repository.

Published repos example:

* `cnd-api-maker-core`
* `cnd-api-maker-laravel`
* `cnd-api-maker-symfony`

Each published repo root contains its own `composer.json`.

### Initial publish (manual)

From the monorepo root:

```bash
git subtree split --prefix=packages/cnd-api-maker/core -b split-core
git push git@github.com:coundia/cnd-api-maker-core.git split-core:main
git branch -D split-core

git subtree split --prefix=packages/cnd-api-maker/laravel -b split-laravel
git push git@github.com:coundia/cnd-api-maker-laravel.git split-laravel:main
git branch -D split-laravel

git subtree split --prefix=packages/cnd-api-maker/symfony -b split-symfony
git push git@github.com:coundia/cnd-api-maker-symfony.git split-symfony:main
git branch -D split-symfony
```

### Release a version

1. Tag the monorepo:

```bash
git tag v0.1.0
git push origin v0.1.0
```

2. Propagate the tag to each split repo:

Core:

```bash
git subtree split --prefix=packages/cnd-api-maker/core -b split-core
git push git@github.com:coundia/cnd-api-maker-core.git split-core:main
git tag -f v0.1.0 split-core
git push -f git@github.com:coundia/cnd-api-maker-core.git v0.1.0
git branch -D split-core
```

Laravel:

```bash
git subtree split --prefix=packages/cnd-api-maker/laravel -b split-laravel
git push git@github.com:coundia/cnd-api-maker-laravel.git split-laravel:main
git tag -f v0.1.0 split-laravel
git push -f git@github.com:coundia/cnd-api-maker-laravel.git v0.1.0
git branch -D split-laravel
```

Symfony:

```bash
git subtree split --prefix=packages/cnd-api-maker/symfony -b split-symfony
git push git@github.com:coundia/cnd-api-maker-symfony.git split-symfony:main
git tag -f v0.1.0 split-symfony
git push -f git@github.com:coundia/cnd-api-maker-symfony.git v0.1.0
git branch -D split-symfony
```

### Packagist submission

Submit the split repositories to Packagist:

* `https://github.com/coundia/cnd-api-maker-core`
* `https://github.com/coundia/cnd-api-maker-laravel`
* `https://github.com/coundia/cnd-api-maker-symfony`

Versions will appear from Git tags (`vX.Y.Z`).

## License

Choose your license per package:

`proprietary`

## Contributing

* Create a feature branch
* Open a PR
* Tag releases using SemVer (`vMAJOR.MINOR.PATCH`)

```
::contentReference[oaicite:0]{index=0}
```


Papa COUNDIA
