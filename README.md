# CredentialBundle

Credential bundle for 2le.

This bundle provides an easy credential manager to handle complex applications.

Its goal is to make easy an association between User Group and Role (credential)

## Installation

```composer require 2lenet/credential-bundle```

## Setting up

config/routes.yaml:
```yml
credential:
    resource: "@LleCredentialBundle/Resources/config/routes.yaml"
```
