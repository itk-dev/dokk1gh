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


## API

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

## Debugging

Debug email sent to user when created:

```
bin/console app:debug notify-user-created [user email]
```

e.g.

```
bin/console app:debug notify-user-created user@example.com
```

## Mock AEOS web service

`parameters.yml`:

```
aoes_location: 'http://127.0.0.1/mock/aeosws'
aoes_username: null
aoes_password: null
```

## Styling

Gulp builds stylesheets/app.css from scss/* so you need to install node dependencies via npm:
`npm install`

Next you can add your styling to the files in scss/* and run:
`gulp scss` to update stylesheets/app.css.

To watch files and refresh browser after saved changes run:
`gulp watch`
