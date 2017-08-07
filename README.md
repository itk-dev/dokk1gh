Dokk1-gæstehåndtering
=====================

# Install

Create database:

```
mysql --user=… --password
create database dokk1gh;
```

Install Composer packages:

```
composer install
```

Install bundle assets:

```
bin/console assets:install --symlink
```

Set up database:

```
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


## Debugging

Debug email sent to user when created:

```
bin/console app:debug notify-user-created [user email]
```

e.g.

```
bin/console app:debug notify-user-created user@example.com
```

## Styling

Gulp builds stylesheets/app.css from scss/* so you need to install node dependencies via npm:
`npm install`

Next you can add your styling to the files in scss/* and run:
`gulp scss` to update stylesheets/app.css.

To watch files and refresh browser after saved changes run:
`gulp watch`