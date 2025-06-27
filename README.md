# sr-oauth-jwt-security

## Lokális fejlesztés

### Docker

```shell
docker build -t sr-oauth-jwt-security .
```

Run the container:

```shell
docker run -d --name sr-oauth-jwt-security-container -v $(pwd):/var/www sr-oauth-jwt-security
```

This command:

-d: Runs the container in detached mode (background)
--name: Assigns a name to the container
-v $(pwd):/var/www: Mounts your current directory to /var/www in the container

Enter the running container:

```shell
docker exec -it sr-oauth-jwt-security-container bash
````
