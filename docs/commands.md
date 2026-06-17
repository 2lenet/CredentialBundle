# Commands

## Initialize a project

This command requires the use of a remote repository ([Remote repository integration](remote_repository.md)).

It allows the current configuration to be uploaded to the remote repository (groups, credentials and credentials by group).

```bash
bin/console lle:credential:init
```

## Update credentials list

It allows you to create all your rights for your CRUD via warmups.

If you're using a remote repository, it will also be updated.

```bash
bin/console lle:credential:warmup
```

## Load credentials configuration

This command requires the use of a remote repository ([Remote repository integration](remote_repository.md)).

It enables you to update your credentials by group using the data stored on your remote repository.

```bash
bin/console lle:credential:load
```

## Sync groups

This command requires the use of a remote repository ([Remote repository integration](remote_repository.md)).

It sends all local groups to the remote repository in a single bulk operation, replacing any groups previously stored there.

Useful for an initial sync or to force a full resync after local changes made outside the application (e.g. fixtures, migrations).

```bash
bin/console lle:credential:sync-groups
```
