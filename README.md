Address book
============

An address book app based off the [symfony standard](https://github.com/symfony/symfony-standard) project.

Run the app locally
---

### Short version

After cloning the project, install the dependencies using composer. It will also prompt you to enter your database settings:

``` bash
$ composer install
```

Setup the database schema from the doctrine configuration:

``` bash
$ php bin/console doctrin:database:create
$ php app/console doctrine:schema:update --force
```

Run the PHP testing server:

``` bash
$ php app/console server:run
```

And voila, you should be able to run the app on [localhost:8000](http://localhost:8000).
