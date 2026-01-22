# Remote repository integration

## Configuration

First, create a `lle_crendential.yaml` file.

Next, you need to define 4 configurations:

These configurations will be used during various commands to keep your remote repository up to date ([Commands](docs/commands.md)).

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
