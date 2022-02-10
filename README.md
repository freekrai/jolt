Jolt
====

Jolt is a PHP micro framework that helps you quickly write simple yet powerful web applications and APIs.

Jolt takes some inspiration from ExpressJS.

Jolt is not a full featured MVC framework, it is built to be a micro framework that handles routing and carries some basic template rendering. Feel free to use your own template engine such as Twig instead.

You can see more info at the [Jolt wiki](https://github.com/freekrai/jolt/wiki/):

For database, I recommend [Idiorm and Paris](http://j4mie.github.com/idiormandparis/)

If you don't want to use Composer, then you can use the autoloader.

If you're looking for the older version of Jolt, you can [find it here](https://github.com/freekrai/jolt/tree/legacy) in the legacy branch of the repo.

### Requirements

* PHP 7+

## Getting Started

### Install

You may install the Jolt Framework with Composer (recommended) or manually.

[Read how to install Jolt](https://github.com/freekrai/jolt/wiki/install).

### Hello World Tutorial

A typical PHP app using Jolt will look like this.

If you didn't install Jolt via Composer, then use the following to load Jolt:

```php
<?php

require 'Jolt/Jolt.php';
\Jolt\Jolt::registerAutoloader();
```

Instantiate a Jolt application:

```php
$app = new Jolt\Jolt();
```

Define a HTTP GET route:

```php
$app->get('/hello/:name', function ($name) use ($app){
    echo "Hello, $name";
});
```

Run the Jolt application:

```php
$app->listen();
```

### Setup your web server

#### Apache

Ensure the `.htaccess` and `index.php` files are in the same public-accessible directory. The `.htaccess` file
should contain this code:

	RewriteEngine On

	# Some hosts may require you to use the `RewriteBase` directive.
	# If you need to use the `RewriteBase` directive, it should be the
	# absolute physical path to the directory that contains this htaccess file.

	RewriteBase /

	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^ index.php [QSA,L]


#### Nginx

The nginx configuration file should contain this code (along with other settings you may need) in your `location` block:

    try_files $uri $uri/ /index.php?$args;

This assumes that Jolt's `index.php` is in the root folder of your project (www root).

#### HipHop Virtual Machine for PHP

Your HipHop Virtual Machine configuration file should contain this code (along with other settings you may need).
Be sure you change the `ServerRoot` setting to point to your Jolt app's document root directory.

    Server {
        SourceRoot = /path/to/public/directory
    }

    ServerVariables {
        SCRIPT_NAME = /index.php
    }

    VirtualHost {
        * {
            Pattern = .*
            RewriteRules {
                    * {
                            pattern = ^(.*)$
                            to = index.php/$1
                            qsa = true
                    }
            }
        }
    }

#### lighttpd ####

Your lighttpd configuration file should contain this code (along with other settings you may need). This code requires
lighttpd >= 1.4.24.

    url.rewrite-if-not-file = ("(.*)" => "/index.php/$0")

This assumes that Jolt's `index.php` is in the root folder of your project (www root).

#### IIS

Ensure the `Web.config` and `index.php` files are in the same public-accessible directory. The `Web.config` file should contain this code:

    <?xml version="1.0" encoding="UTF-8"?>
    <configuration>
        <system.webServer>
            <rewrite>
                <rules>
                    <rule name="jolt" patternSyntax="Wildcard">
                        <match url="*" />
                        <conditions>
                            <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                        </conditions>
                        <action type="Rewrite" url="index.php" />
                    </rule>
                </rules>
            </rewrite>
        </system.webServer>
    </configuration>

## Documentation

<https://github.com/freekrai/jolt/wiki/usage>

## How to Contribute

### Pull Requests

1. Fork the Jolt Framework repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the **develop** branch

It is very important to separate new features or improvements into separate feature branches, and to send a pull
request for each branch. This allows me to review and pull in new features or improvements individually.
