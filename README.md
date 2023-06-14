# CredentialBundle

Credential bundle for 2le.

This bundle provides an easy credential manager to handle complex applications.

Its goal is to make easy an association between User Group and Role (credential)

## Installation

```composer require 2lenet/credential-bundle```

flex should do the job

## Setting up

config/routes/credential.yaml:

```yml
credential:
    resource: "@LleCredentialBundle/Resources/config/routes.yaml"
```

## RESET Rubrique name

```sql
UPDATE lle_credential_credential
SET rubrique= SUBSTRING_INDEX(SUBSTRING_INDEX(role, '_', 2), '_', -1)
WHERE role like "ROLE_%";
``` 
