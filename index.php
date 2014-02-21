<?php
$_GET['route'] = isset($_GET['route']) ? '/'.$_GET['route'] : '/';

/**
 * Step 1: Require the Jolt Framework
 *
 * If you are not using Composer, you need to require the
 * Jolt Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */

require 'jolt.php';


/**
 * Step 2: Instantiate a Jolt application
 *
 */

$app = new Jolt();
$app->option('source', 'config.ini');


/**
 * Step 3: Define the Jolt application routes
 *
 * Here we define several Jolt application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Jolt::get`, `Jolt::post`, `Jolt::put`, `Jolt::patch`, and `Jolt::delete`
 * is an anonymous function.
 */


// preload blog entry whenever a matching route has :blog_id in it
$app->filter('blog_id', function ($blog_id) use ($app){
	//	load this blog post
	$app->store('blog', $blog_id);
});
$app->get('/hello/:blog_id', function($blog_id) use ($app){
	$bid = $app->store('blog');
	$app->render( 'hello', array(
		"pageTitle"=>"Hello {$bid}",
		"name"=>$bid
	));
});
$app->get('/hello/:name', function ($name){
	$app = Jolt::getInstance();
	// render a view
	$app->render( 'page', array(
		"pageTitle"=>"Hello",
		"body"=>"Hello {$name}!",
	));
});


$app->get('/greet', function() use ($app){
	$app->render( 'page', array(
		"pageTitle"=>"Greetings",
		"body"=>"Greetings world!"
	));
});
$app->post('/greet', function() use ($app){
	$app->render( 'page', array(
		"pageTitle"=>"Greetings ".$_POST['name']."!",
		"body"=>"Greetings ".$_POST['name']."!"
	));
});

//	instead of a function, we can also define a controller and action and have it called that way as well...
$app->route('/greet2(/:name)', array("controller"=>'Greetings',"action"=>'my_name') );
//	we can also define the class and action as a string.. Class#Action
$app->route('/greet3(/:name)', 'Greetings#my_name' );

class Greetings extends Jolt_Controller{
    public function my_name($name = 'default'){
		$this->app->render( 'page', array(
			"pageTitle"=>"Greetings ".$this->sanitize($name)."!",
			'title'=>'123',
			"body"=>"Greetings ".$this->sanitize($name)."!"
		),'marketing');

    }
}


// POST route
$app->post('/post', function () use ($app){
	echo 'This is a POST route';
});
	
// PUT route
$app->put('/put',function ()  use ($app){
	echo 'This is a PUT route';
});

// PATCH route
$app->patch('/patch', function () use ($app){
	echo 'This is a PATCH route';
});

// DELETE route
$app->delete('/delete', function ()  use ($app){
	echo 'This is a DELETE route';
});


//	Default Home Page
$app->get('/', function() use ($app){
	$app->render( 'home' );
});

/**
 * Step 4: Run the Jolt application
 *
 * This method should be called last. This executes the Jolt application
 * and returns the HTTP response to the HTTP client.
 */

$app->listen();


/*
 * Some handy utility functions
 ************************************************************************************/
 
/*
 *	shortcut function to grab the site.url variable we've stored in our config.ini file
 */
function site_url(){
	return config( 'site.url' );
}
/*
 *	return a value that matches the key we pass
 */
function config($key){
	$app = Jolt::getInstance();
	return $app->option($key);
}