# Dokk1-gæstehåndtering

## Installation

```sh
docker compose up -d
```

```sh
docker compose exec phpfpm composer install
```

Set up database:

```sh
docker compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

Create super administrator:

```sh
docker compose exec phpfpm bin/console user:create super-admin@example.com
docker compose exec phpfpm bin/console user:promote super-admin@example.com ROLE_SUPER_ADMIN
docker compose exec phpfpm bin/console user:set-password super-admin@example.com
```

Create administrator:

```sh
docker compose exec phpfpm bin/console user:create admin@example.com
```

```sh
docker compose exec phpfpm bin/console user:promote admin@example.com ROLE_ADMIN
```

Create user:

```sh
docker compose exec phpfpm bin/console user:create user@example.com
```

Open the site:

```sh
open "http://$(docker compose port nginx 8080)"
```

## Build assets

```sh
docker compose run --rm node yarn install
docker compose run --rm node yarn build
```

During development, use

```sh
docker-compose run node yarn watch
```

to watch for changes.

## Cron jobs

The `app:aeos:code-cleanup` console command can be used to delete expires codes:

```sh
bin/console app:aeos:code-cleanup --help
```

A couple of commands can clean up guest and apps

```sh
bin/console app:expire-guests
bin/console app:expire-inactive-apps --app-sent-before='-24 hours'
```

Set up a `cron` job to have expired codes deleted daily at 02:00
(adjust paths to match your actual setup):

```sh
0 2 * * * /usr/bin/php /home/www/dokk1gh/htdocs/bin/console --env=prod app:aeos:code-cleanup
```

## API

API documentation:

```sh
open "http://$(docker compose port nginx 8080)/api/doc"
```

Using an `apikey`, users can get a list of available templates:

```sh
curl "http://$(docker compose port nginx 8080)/api/templates?apikey=apikey"
```

Get list of codes created by user:

```sh
curl "http://$(docker compose port nginx 8080)/api/codes?apikey=apikey"
```

An administrator can get all codes by adding `all=1`:

```sh
curl "http://$(docker compose port nginx 8080)/api/codes?apikey=apikey&all=1"
```

Create a code:

```sh
curl --silent "http://$(docker compose port nginx 8080)/api/codes?apikey=apikey" --header "content-type: application/json" --data @- <<'JSON'
{
    "template": 1,
    "startTime": "2017-08-14T08:00:00+02:00",
    "endTime": "2017-08-14T16:00:00+02:00"
}
JSON
```

On success the result will look like this:

```json
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

## Test and debugging

### Emails

Debug email sent to user when created:

```sh
bin/console app:debug notify-user-created [user email]
```

e.g.

```sh
bin/console app:debug notify-user-created user@example.com
```

### Mocks

```sh
bin/console doctrine:schema:update --em=mocks --force
```

#### Mock AEOS web service

`parameters.yml`:

```yaml
aeos_location: 'http://nginx/mock/aeosws'
aeos_username: null
aeos_password: null
```

#### Mock SMS gateway

```yaml
sms_gateway_location: 'http://nginx/mock/sms
sms_gateway_username: null
sms_gateway_password: null
```

## Acceptance tests

Clear out the acceptance test cache and set up the database:

```sh
SYMFONY_ENV=acceptance bin/console cache:clear --no-warmup
SYMFONY_ENV=acceptance bin/console cache:warmup
SYMFONY_ENV=acceptance bin/console doctrine:database:create
```

Run API tests:

```sh
./vendor/bin/behat
```

## Coding standards

### PHP

Check code:

```sh
docker compose exec phpfpm composer coding-standards-check
```

Apply coding standards:

```sh
docker compose exec phpfpm composer coding-standards-apply
```

### Twig (experimental)

```sh
docker compose exec phpfpm composer coding-standards-check/twigcs
```

### Markdown

```sh
docker compose run --rm node yarn coding-standards-check
```

### Git hooks

Run

```sh
docker compose exec phpfpm composer install-git-hooks
```

to install a Git `pre-commit` hook that check coding standards before a commit.
