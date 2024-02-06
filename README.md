# Dokk1-gæstehåndtering

## Installation

```shell
docker compose pull
docker compose up --detach --remove-orphans
```

```shell
docker compose exec phpfpm composer install
```

Define default local settings:

``` shell
cp config/services.local.yaml.dist config/services.local.yaml
```

Edit `config/services.local.yaml` as needed (cf. [Mocks](#mocks)).

Set up database:

```shell
docker compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

Create super administrator:

```shell
docker compose exec phpfpm bin/console user:create super-admin@example.com
docker compose exec phpfpm bin/console user:promote super-admin@example.com ROLE_SUPER_ADMIN
docker compose exec phpfpm bin/console user:set-password super-admin@example.com
```

Create administrator:

```shell
docker compose exec phpfpm bin/console user:create admin@example.com
```

```shell
docker compose exec phpfpm bin/console user:promote admin@example.com ROLE_ADMIN
```

Create user:

```shell
docker compose exec phpfpm bin/console user:create user@example.com
```

Open the site:

```shell
open "http://$(docker compose port nginx 8080)"
```

## Translations

```shell
docker compose exec phpfpm composer update-translations
# Open Danish translations in Poedit (https://poedit.net/)
# Run `brew install poedit` to install Poedit.
open translations/messages+intl-icu.da.xlf
```

## Build assets

```shell
docker compose run --rm node yarn install
docker compose run --rm node yarn build
```

During development, use

```shell
docker compose run --rm node yarn watch
```

to watch for changes.

## Fixtures

Load fixtures to populate your test database:

``` shell
docker compose exec phpfpm composer fixtures:load
```

After loading fixtures, the following users exist (cf. [`fixtures/user.yaml`](fixtures/user.yaml)):

| Email                     | Password        | Roles            | API key      |
|---------------------------|-----------------|------------------|--------------|
| `super-admin@example.com` | `password`      | ROLE_SUPER_ADMIN |              |
| `user@example.com`        | `user-password` | ROLE_USER        | user-api-key |

## Cron jobs

The `app:aeos:code-cleanup` console command can be used to delete expires codes:

```shell
bin/console app:aeos:code-cleanup --help
```

A couple of commands can clean up guest and apps

```shell
bin/console app:expire-guests
bin/console app:expire-inactive-apps --app-sent-before='-24 hours'
```

Set up a `cron` job to have expired codes deleted daily at 02:00
(adjust paths to match your actual setup):

```shell
0 2 * * * /usr/bin/php /home/www/dokk1gh/htdocs/bin/console --env=prod app:aeos:code-cleanup
```

## API

A user can create an API key via the user menu: @TODO

API documentation:

```shell
open "http://$(docker compose port nginx 8080)/api/doc"
```

In the following examples, the API key of the fixture user `user@example.com` is
used.

Get a list of templates available to the user:

```shell
curl --silent --header "Authorization: Bearer user-api-key" "http://$(docker compose port nginx 8080)/api/templates"
```

Get list of codes created by user:

```shell
curl --silent --header "Authorization: Bearer user-api-key" "http://$(docker compose port nginx 8080)/api/codes"
```

An administrator can get all codes by adding `all=1`:

```shell
curl --silent --header "Authorization: Bearer user-api-key" "http://$(docker compose port nginx 8080)/api/codes?all=1"
```

Create a code:

```shell
curl --silent --silent --header "Authorization: Bearer user-api-key" "http://$(docker compose port nginx 8080)/api/codes" --header "content-type: application/json" --data @- <<'JSON'
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

```shell
bin/console app:debug notify-user-created [user email]
```

e.g.

```shell
bin/console app:debug notify-user-created user@example.com
```

Open test mail UI:

``` shell
open "http://$(docker compose port mail 8025)"
```

### Mocks

```shell
docker compose exec phpfpm bin/console doctrine:schema:update --em=mock --force --complete
```

#### Mock AEOS web service

Use this during local testing and development.

```yaml
# config/services.local.yaml
parameters:
    aeos_location: 'http://nginx:8080/mock/aeosws'
    aeos_username: null
    aeos_password: null
```

* List mock AEOS templates to use when editing templates:

  ```shell
  open "http://$(docker compose port nginx 8080)/admin/api/templates"
  ```

* List mock AEOS users to use when editing users:

  ```shell
  open "http://$(docker compose port nginx 8080)/admin/api/people"
  ```

See messages sent to the mock AEOS web service:

```shell
open "http://$(docker compose port nginx 8080)/mock/aeosws/log"
```

Show only the latest message:

```shell
open "http://$(docker compose port nginx 8080)/mock/aeosws/log/latest"
```

#### Mock SMS gateway

Use this during local testing and development.

See messages sent to the mock SMS gateway:

```shell
open "http://$(docker compose port nginx 8080)/mock/sms/log"
```

Show only the latest message:

```shell
open "http://$(docker compose port nginx 8080)/mock/sms/log/latest"
```

## Acceptance tests

Clear out the acceptance test cache and set up the database:

```shell
SYMFONY_ENV=acceptance bin/console cache:clear --no-warmup
SYMFONY_ENV=acceptance bin/console cache:warmup
SYMFONY_ENV=acceptance bin/console doctrine:database:create
```

Run API tests:

```shell
./vendor/bin/behat
```

## Coding standards

### PHP

Check code:

```shell
docker compose exec phpfpm composer coding-standards-check
```

Apply coding standards:

```shell
docker compose exec phpfpm composer coding-standards-apply
```

### Twig (experimental)

```shell
docker compose exec phpfpm composer coding-standards-check/twigcs
```

### Markdown

```shell
docker compose run --rm node yarn coding-standards-check
```

### Rector

```shell
docker compose exec phpfpm vendor/bin/rector process
```
