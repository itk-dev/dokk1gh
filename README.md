Dokk1-gæstehåndtering
=====================

# Installation

```
docker-compose up -d
```

```
docker-compose exec phpfpm composer install
```

Install bundle assets:

```
docker-compose exec phpfpm bin/console assets:install --symlink
```

Set up database:

```
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

Create super administrator:

```
docker-compose exec phpfpm bin/console fos:user:create --super-admin super-admin super-admin@example.com
```

Create administrator:

```
docker-compose exec phpfpm bin/console fos:user:create admin@example.com admin@example.com
```

```
docker-compose exec phpfpm bin/console fos:user:promote admin@example.com ROLE_ADMIN
```

Create user:

```
docker-compose exec phpfpm bin/console fos:user:create user user@example.com
```

Open the site:

```
open http://dokk1gh.docker.localhost:$(docker-compose port reverse-proxy 80 | cut -d: -f2)
```

# Cron jobs

The `app:aeos:code-cleanup` console command can be used to delete expires codes:

```sh
bin/console app:aeos:code-cleanup --help
```

Set up a `cron` job to have expired codes deleted daily at 02:00
(adjust paths to match your actual setup):

```
0 2 * * * /usr/bin/php /home/www/dokk1gh/htdocs/bin/console --env=prod app:aeos:code-cleanup
```


# API

API documentation:

```
http://dokk1gh.vm/api/doc
```

Using an `apikey`, users can get a list of available templates:

```
curl http://dokk1gh.vm/api/templates?apikey=apikey
```

Get list of codes created by user:

```
curl http://dokk1gh.vm/api/codes?apikey=apikey
```

An administrator can get all codes by adding `all=1`:

```
curl http://dokk1gh.vm/api/codes?apikey=apikey&all=1
```

Create a code:

```
curl --silent 'http://dokk1gh.vm/api/codes?apikey=apikey' --header 'Content-type: application/json' --data @- <<'JSON'
{
	"template": 1,
	"startTime": "2017-08-14T08:00:00+02:00",
	"endTime": "2017-08-14T16:00:00+02:00"
}
JSON
```

On success the result will look like this:

```
{
   "status" : "ok",
   "code" : "21347994",
   "endTime" : "2017-08-14T16:00:00+0200",
   "startTime" : "2017-08-14T08:00:00+0200",
   "template" : {
      "name" : "G<E6>st ITK",
      "id" : 1
   }
}
```

# Test and debugging

## Emails

Debug email sent to user when created:

```
bin/console app:debug notify-user-created [user email]
```

e.g.

```
bin/console app:debug notify-user-created user@example.com
```

## Mocks

```
bin/console doctrine:schema:update --em=mocks --force
```

### Mock AEOS web service

`parameters.yml`:

```
aoes_location: 'http://nginx/mock/aeosws'
aoes_username: null
aoes_password: null
```

### Mock SMS gateway

```
sms_gateway_location: 'http://nginx/mock/sms
sms_gateway_username: null
sms_gateway_password: null
```


# Acceptance tests

Clear out the acceptance test cache and set up the database:

```
SYMFONY_ENV=acceptance bin/console cache:clear --no-warmup
SYMFONY_ENV=acceptance bin/console cache:warmup
SYMFONY_ENV=acceptance bin/console doctrine:database:create
```

Run API tests:

```
./vendor/bin/behat
```


## Styling

Gulp builds stylesheets/app.css from scss/* so you need to install node dependencies via npm:
`npm install`

Next you can add your styling to the files in scss/* and run:
`gulp scss` to update stylesheets/app.css.

To watch files and refresh browser after saved changes run:
`gulp watch`


# Coding standards

Check code:

```sh
composer check-coding-standards
```

Fix code (if possible):

```sh
composer fix-coding-standards
```

Linting Twig (experimental):

```sh
composer check-coding-standards/twigcs
```

## Git hooks

Run

```sh
composer install-git-hooks
```

to install a Git `pre-commit` hook that check coding standards before a commit.
