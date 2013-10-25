<?php

require("jolt.php");

$app = new Jolt('site',false);
$app->option('source', 'config.ini');

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
$app->get('/hello', function() use ($app){
	$app->render( 'page', array(
		"pageTitle"=>"Hello",
		"body"=>"Hello world!"
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


$app->get('/', function() use ($app){
	$app->render( 'home' );
});
$app->listen();
