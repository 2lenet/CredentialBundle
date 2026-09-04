# Remote repository integration

## Configuration

First, create a `lle_credential.yaml` file.

Next, you need to define 4 configurations:

These configurations will be used during various commands to keep your remote repository up to date ([Commands](commands.md)).

- `client_url`: the url of your remote repository (can be a Docker url)
- `client_public_url`: the public url of your remote repository (to have a button allowing you to access it)
- `project_code`: a unique code to identify the project
- `project_token`: a token to secure the API call

```yaml
lle_credential:
    client_url: http://remote-repository
    client_public_url: https://www.remote-repository.com
    project_code: PROJECT
    project_token: abcde123
```

## Remote configuration — optional vs required

The remote configuration (`client_url`, `project_code`, `project_token`) is **optional** for most operations. The bundle behaves as follows depending on how much of it is provided:

| Configuration state | Behaviour |
|---|---|
| None of the three keys defined | Remote calls are silently skipped — the bundle works in local-only mode |
| Partially defined | An exception is thrown — incomplete configuration is treated as a misconfiguration |
| All three keys defined | Remote calls are made normally |

Commands that **require** a remote (they always fail without it): `lle:credential:init`, `lle:credential:load`, `lle:credential:sync-groups`.

Commands that use the remote **when available** (they succeed in local-only mode): `lle:credential:warmup`.

## Guard against the test environment

Every implicit remote call - the automatic group synchronisation below, and the push at the end of
`lle:credential:warmup` - is silently skipped when `kernel.environment` is `test`, regardless of what
`lle_credential.*` resolves to in that environment. These calls are side effects of a purely local
operation (persisting a `Group`, running a warmup); a project's own test suite (fixtures, `hautelook:fixtures:load`)
should never be able to reach a real repository just because `lle_credential.yaml` has no
`when@test:` override - which is exactly the misconfiguration that once filled a real project's group list
with dozens of duplicates on every test run.

`lle:credential:load`, `lle:credential:init` and `lle:credential:sync-groups` are different: talking to the
remote is the entire point of running them, and they are always invoked deliberately (never as a side effect
of a Doctrine event or a warmup), so silently doing nothing in the test environment would defeat their
documented "requires a remote" contract. `lle:credential:sync-groups` is the one exception: unlike `load`
(read-only) and `init` (rejected server-side once the project already has data), it **replaces** the
project's entire remote group list, so the same misconfiguration would silently delete real groups instead
of merely duplicating one. It is therefore refused (`RemoteApiException`) when run from the test
environment; `load` and `init` are not.

**In all cases**, define a `when@test:` override for `lle_credential` that blanks the configuration (see
crudit-studio's own `config/packages/lle_credential.yaml` for an example) - the guards above reduce the
blast radius of a missing override, they do not replace having one.

## Automatic group synchronisation

When the remote is configured, any create, update or delete operation on a `Group` entity is automatically mirrored to the remote repository via a Doctrine event listener (`GroupListener`).

- Group created → `POST /api/project/group/create/{code}`
- Group renamed or updated → `POST /api/project/group/edit/{code}/{oldName}`
- Group deleted → `DELETE /api/project/group/delete/{code}/{name}`

This synchronisation never interrupts a Doctrine flush — errors are caught and handled gracefully:

- In an **HTTP context** (web request), a flash message is displayed to the user:
  - Remote failure (`RemoteApiException`) → `warning` flash explaining the local change was applied but remote sync failed
  - Misconfiguration → `danger` flash asking the user to contact an administrator
- In a **CLI context** (commands, migrations), errors are silently ignored

The listener is automatically disabled during `lle:credential:load` to avoid a sync loop when loading data from the remote.

## Actor attribution headers

Every remote call sent by `ClientService` attaches two best-effort headers so the remote repository can
record who actually triggered the action (for audit purposes), on top of the project-level Bearer token:

- `X-Credential-Actor`: the identifier (`Security::getUser()->getUserIdentifier()`) of the currently
  authenticated user in the **calling app**, when there is one.
- `X-Credential-Actor-Ip`: that user's real client IP (`RequestStack::getCurrentRequest()->getClientIp()`),
  which is only accurate if the calling app itself has `framework.trusted_proxies` correctly configured
  for its own front-proxy.

Both are omitted (not sent at all) when there is no HTTP request/authenticated user in scope — e.g. CLI
commands like `lle:credential:load` or `lle:credential:warmup` run outside of any user's action, so nothing
is attributed to a person. This is purely additive: a remote repository that doesn't know about these
headers just ignores them, so this requires no coordinated upgrade between this bundle and the repository.

## Error handling

Remote errors throw a `RemoteApiException`. The bundle handles them as follows depending on the context:

| Context | Behaviour on remote error |
|---|---|
| Doctrine listener — HTTP context | Local operation succeeds; translated `warning` (remote failure) or `danger` (misconfiguration) flash displayed |
| Doctrine listener — CLI context | Silently ignored — local operation is not affected |
| `lle:credential:warmup`, `lle:credential:sync-groups`, `lle:credential:init` | Warning message printed, command still exits with success |
| `lle:credential:load` | Error message printed, command exits with failure |
| Frontend (toggle group / credential) | Checkbox state is preserved (local change applied), translated warning toast displayed |
| Frontend (any HTTP/network error) | Checkbox reverted, translated error toast displayed |

Remote errors triggered by both `HTTP 404` (project or resource not found) and `HTTP 403` (invalid token) are unified under `RemoteApiException`.
