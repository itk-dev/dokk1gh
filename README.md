Dokk1-gæstehåndtering
=====================

Install:

```
composer install
```

```
bin/console assets:install --symlink
```

Set up database:

```
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate --no-interaction
```

Create super administrator:

```
bin/console fos:user:create --super-admin super-admin super-admin@example.com
```

Create administrator:

```
bin/console fos:user:create admin admin@example.com
```

```
bin/console fos:user:promote admin ROLE_ADMIN
```

Create user:

```
bin/console fos:user:create user user@example.com
```

Go to `http://dokk1gh.vm/login`.
