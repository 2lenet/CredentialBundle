<h1 align="center">CredentialBundle</h1>

<p align="center">
    <a href="https://github.com/2lenet/CredentialBundle/actions" target="_blank">
        <img src="https://github.com/2lenet/CredentialBundle/actions/workflows/phpstan.yml/badge.svg?branch=master" alt="PHPStan status">
    </a>
    <a href="https://github.com/2lenet/CredentialBundle/actions" target="_blank">
        <img src="https://github.com/2lenet/CredentialBundle/actions/workflows/phpunit.yml/badge.svg?branch=master" alt="PHPUnit status">
    </a>
    <a href="https://github.com/2lenet/CredentialBundle/actions" target="_blank">
        <img src="https://github.com/2lenet/CredentialBundle/actions/workflows/validate.yml/badge.svg?branch=master" alt="PHPCS status">
    </a>
</p>

This bundle provides an easy credential manager to handle complex applications.

Its purpose is to facilitate the association between user groups and roles.

![img.png](docs/img/dashboard.png)

## Installation

```bash
composer require 2lenet/credential-bundle
```

## Setting up

Configure the `config/routes/credential.yaml` file:

```yaml
credential:
    resource: '@LleCredentialBundle/Resources/config/routes.yaml'
```

Next, generate the migration:

```bash
php bin/console make:migration
```

Check that the changes are correct and execute them:

```bash
php bin/console doctrine:migrations:migrate
```
