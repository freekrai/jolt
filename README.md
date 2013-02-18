Jolt
====

Jolt is a PHP micro framework that helps you quickly write simple yet powerful web applications and APIs.

Jolt takes some inspiration from ExpressJS.

Jolt is not a full featured MVC framework, it is built to be a micro framework that handles routing and carries some basic template rendering. Feel free to use your own template engine such as Twig instead.

For database, I recommend [Idiorm and Paris](http://j4mie.github.com/idiormandparis/)

### Requirements
* PHP 5.3

### Quick and Basic
A typical PHP app using Jolt will look like this.

```php
<?php
// include the library
include 'jolt.php';

$app = new Jolt('my app');

// define your routes
$app->get('/greet', function () use ($app){
	// render a view
	$app->render( 'page', array(
		"pageTitle"=>"Greetings",
		"body"=>"Greetings world!"
	));
});

$app->get('/', function()  use ($app) {
	$app->render('home');
});

$app->listen();

?>
```

### Route Symbol Filters
This is taken from ExpressJS. Route filters let you map functions against symbols in your routes. These functions then get executed when those symbols are matched.

```php
<?php
// preload blog entry whenever a matching route has :blog_id in it
$app->filter('blog_id', function ($blog_id) use ($app) {
	$blog = Blog::findOne($blog_id);
	// store() lets you store stuff for later use (NOT a cache)
	$app->store('blog', $blog);
});

// here, we have :blog_id in the route, so our preloader gets run
$app->get('/blogs/:blog_id', function ($blog_id) use ($app)  {
	// pick up what we got from the stash
	$blog = $app->store('blog');
	$app->render('single', array('blog' => $blog);
});
?>
```

### Conditions
Conditions are basically helper functions.

```php
<?php
// require that users are signed in
$app->condition('signed_in', function () use ($app) {
  $app->redirect(403, '/403-forbidden', !$app->store('user'));
});

// require a valid token when accessing a page
$app->get('/admin', function () use ($app)  {
  $app->condition('signed_in');
  $app->render('admin');
});
?>
```
*NOTE:* Because of the way conditions are defined, conditions can't have anonymous functions as their first parameter.

### Configurations
You can make use of ini files for configuration by doing something like `config('source', 'myconfig.ini')`.
This lets you put configuration settings in ini files instead of making `config()` calls in your code.

```php
<?php
// load a config.ini file
$app->option('source', 'my-settings.ini');

// set a different folder for the views
$app->option('views', __DIR__.'/myviews');

// get the encryption secret
$secret = $app->option('secret');
?>
```

### Utility Functions
There are a lot of other useful routines in the library. Documentation is still lacking but they're very small and easy to figure out. Read the source for now.

```php
<?php
// store a setting and get it
$app->option('views', './views');
$app->option('views'); // returns './views'

// store a variable and get it (useful for moving stuff between scopes)
$app->store('user', $user);
$app->store('user'); // returns stored $user var

// redirect with a status code
$app->redirect(302, '/index');

// redirect if a condition is met
$app->redirect(403, '/users', !$authenticated);

// redirect only if func is satisfied
$app->redirect('/admin', function () use ($auth) { return !!$auth; });

// redirect only if func is satisfied, and with a diff code
$app->redirect(301, '/admin', function () use ($auth) { return !!$auth; });

// send a http error code and print out a message
$app->error(403, 'Forbidden');

// get the current HTTP method or check the current method
$app->method(); // GET, POST, PUT, DELETE
$app->method('POST'); // true if POST request, false otherwise

// client's IP
$app->client_ip();

// get something or a hash from a hash
$name = $app->from($_POST, 'name');
$user = $app->from($_POST, array('username', 'email', 'password'));

// load a partial using some file and locals
$html = $app->partial('users/profile', array('user' => $user));
?>
```