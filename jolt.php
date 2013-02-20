<?php

class Jolt{
	public $name;
	public $debug = false;
	public $notFound;
	protected static $baseUri;
	protected static $uri;
	private $route_map = array(
		'GET' => array(),
		'POST' => array()
	);
	public function __construct($name='',$debug = false){
		$this->name = $name;
		$this->debug = false;
	}
	public function listen(){
		if( $this->debug ){
			echo '<pre>'.print_r( $this->route_map,true ).'</pre>';		
		}	
		$this->router();
	}
	private function add_route($method = 'GET',$pattern,$cb = null){
		$method = strtoupper($method);
		if (!in_array($method, array('GET', 'POST')))
			error(500, 'Only GET and POST are supported');
		$this->route_map[$method][$pattern] = array(
			'xp' => $this->route_to_regex($pattern),
			'cb' => $cb
		);
	}
	private function router(){
		$route_map = $this->route_map;
		foreach ($route_map['GET'] as $pat => $obj) {
			foreach($_GET as $k=>$v){
				if( $obj['xp'] == '@^$@i')	$obj['xp'] = '@^/$@i';	//	covers home page
#				echo $k.' --- '.$v.' --- '.$obj['xp'].'<br />';
				$pattern = $v;
				if (!preg_match($obj['xp'], $v, $vals))	continue;
				$this->middleware($pattern);
				array_shift($vals);
				preg_match_all('@:([\w]+)@', $pat, $keys, PREG_PATTERN_ORDER);
				$keys = array_shift($keys);
				$argv = array();				
				foreach ($keys as $index => $id) {
					$id = substr($id, 1);
					if (isset($vals[$id])) {
						array_push($argv, trim(urldecode($vals[$id])));
					}
				}
				if (count($keys)) {
					$this->filter( array_values($keys), $vals );
				}
				if (is_callable($obj['cb'])) {
					call_user_func_array($obj['cb'], $argv);
				}
#				exit;
				return;
			}
		}
		$this->notFound();
	}
	private function route_to_regex($route) {
		$route = preg_replace_callback('@:[\w]+@i', function ($matches) {
			$token = str_replace(':', '', $matches[0]);
			return '(?P<'.$token.'>[a-z0-9_\0-\.]+)';
		}, $route);
		return '@^'.rtrim($route, '/').'$@i';
	}
	private function got404( $callable = null ) {
		if ( is_callable($callable) ) {
			$this->notFound = $callable;
		}
		return $this->notFound;
	}
	public function notFound( $callable = null ) {
		if ( !is_null($callable) ) {
			$this->got404($callable);
		} else {
			ob_start();
			$customNotFoundHandler = $this->got404();
			if ( is_callable($customNotFoundHandler) ) {
				call_user_func($customNotFoundHandler);
			} else {
				call_user_func(array($this, 'defaultNotFound'));
			}
			$this->error(404, ob_get_clean());
		}
	}
	public function route($pattern,$cb = null){	//	doesn't care about GET or POST...
		return $this->add_route('GET',$pattern,$cb);
	}
	public function get($pattern,$cb = null){
		if( $this->method('GET') ){	//	only process during GET
			return $this->add_route('GET',$pattern,$cb);
		}
	}
	public function post($pattern,$cb = null){
		if( $this->method('POST') ){	//	only process during POST
			return $this->add_route('GET',$pattern,$cb);
		}
	}
	public function req($key){
		return $_REQUEST[$key];
	}
	public function send($str){
		echo $str;
	}
	public function redirect(/* $code_or_path, $path_or_cond, $cond */) {
		$argv = func_get_args();
		$argc = count($argv);
		$path = null;
		$code = 302;
		$cond = true;
		switch ($argc) {
			case 3:
			list($code, $path, $cond) = $argv;
			break;
			case 2:
			if (is_string($argv[0]) ? $argv[0] : $argv[1]) {
			$code = 302;
			$path = $argv[0];
			$cond = $argv[1];
			} else {
			$code = $argv[0];
			$path = $argv[1];
			}
			break;
			case 1:
				if (!is_string($argv[0]))
					$this->error(500, 'bad call to redirect()');
				$path = $argv[0];
				break;
			default:
				$this->error(500, 'bad call to redirect()');
		}
		$cond = (is_callable($cond) ? !!call_user_func($cond) : !!$cond);
		if (!$cond)return;
		header('Location: '.$path, true, $code);
		exit;
	}
	public function option($key, $value = null) {
		static $_option = array();
		if ($key === 'source' && file_exists($value))
			$_option = parse_ini_file($value, true);
		else if ($value == null)
			return (isset($_option[$key]) ? $_option[$key] : null);
		else
			$_option[$key] = $value;
	}
	public function error($code, $message) {
		@header("HTTP/1.0 {$code} {$message}", true, $code);
		die($message);
	}
	public function warn($name = null, $message = null) {
		static $warnings = array();
		if ($name == '*')
			return $warnings;
		if (!$name)
			return count(array_keys($warnings));
		if (!$message)
			return isset($warnings[$name]) ? $warnings[$name] : null ;
		$warnings[$name] = $message;
	}
	public function from($source, $name) {
		if (is_array($name)) {
			$data = array();
			foreach ($name as $k)
				$data[$k] = isset($source[$k]) ? $source[$k] : null ;
			return $data;
		}
		return isset($source[$name]) ? $source[$name] : null ;
	}
	public function store($name, $value = null) {
		static $_store = array();
		if ($value === null)
			return isset($_store[$name]) ? $_store[$name] : null;
		$_store[$name] = $value;
		return $value;
	}
	public function method($verb = null) {
		if ($verb == null || (strtoupper($verb) == strtoupper($_SERVER['REQUEST_METHOD'])))
			return strtoupper($_SERVER['REQUEST_METHOD']);
		return false;
#		$this->error(400, 'bad request');
	}		
	public function client_ip() {
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}
	
	public function partial($view, $locals = null) {
		if (is_array($locals) && count($locals)) {
			extract($locals, EXTR_SKIP);
		}		
		if (($view_root = $this->option('views.root')) == null)
			$this->error(500, "[views.root] is not set");
		$path = basename($view);
		$view = preg_replace('/'.$path.'$/', "_{$path}", $view);
		$view = "{$view_root}/{$view}.html";		
		if (file_exists($view)) {
			ob_start();
			require $view;
			return ob_get_clean();
		} else {
			$this->error(500, "partial [{$view}] not found");
		}		
		return '';
	}
	public function content($value = null) {
		return $this->store('$content$', $value);
	}	
	public function render($view, $locals = null, $layout = null) {
		if (is_array($locals) && count($locals)) {
			extract($locals, EXTR_SKIP);
		}
		if (($view_root = $this->option('views.root')) == null)
			$this->error(500, "[views.root] is not set");
		ob_start();
		include "{$view_root}/{$view}.html";
		$this->content(trim(ob_get_clean()));
		if ($layout !== false) {
			if ($layout == null) {
				$layout = $this->option('views.layout');
				$layout = ($layout == null) ? 'layout' : $layout;
			}
			$layout = "{$view_root}/{$layout}.html";	
			header('Content-type: text/html; charset=utf-8');
			$pageContent = $this->content();
			ob_start();
			require $layout;
			echo trim(ob_get_clean());
		} else {
			//	no layout
			echo $this->content();
		}
	}
	public function json($obj, $code = 200) {
		//	output a json stream
		header('Content-type: application/json', true, $code);
		echo json_encode($obj);
		exit;
	}
	public function condition() {
		static $cb_map = array();		
		$argv = func_get_args();
		$argc = count($argv);		
		if (!$argc)
			$this->error(500, 'bad call to condition()');		
		$name = array_shift($argv);
		$argc = $argc - 1;
		if (!$argc && is_callable($cb_map[$name]))
			return call_user_func($cb_map[$name]);		
		if (is_callable($argv[0]))
			return ($cb_map[$name] = $argv[0]);		
		if (is_callable($cb_map[$name]))
			return call_user_func_array($cb_map[$name], $argv);		
		$this->error(500, 'condition ['.$name.'] is undefined');
	}
	public function middleware($cb_or_path = null) {	
		static $cb_map = array();
		if ($cb_or_path == null || is_string($cb_or_path)) {
			foreach ($cb_map as $cb) {
				call_user_func($cb, $cb_or_path);
			}
		} else {
			array_push($cb_map, $cb_or_path);
		}
	}
	public function filter($sym, $cb_or_val = null) {
		static $cb_map = array();
		if (is_callable($cb_or_val)) {
			$cb_map[$sym] = $cb_or_val;
			return;
		}
		if (is_array($sym) && count($sym) > 0) {
			foreach ($sym as $s) {
				$s = substr($s, 1);
				if (isset($cb_map[$s]) && isset($cb_or_val[$s]))
				call_user_func($cb_map[$s], $cb_or_val[$s]);
			}
			return;
		}
		$this->error(500, 'bad call to filter()');
	}	

	public function set_cookie($name, $value, $expire = 31536000, $path = '/') {
		setcookie($name, $value, time() + $expire, $path);
	}
	public function get_cookie($name) {
		$value = $this->from($_COOKIE, $name);
		if ($value)
			return $value;
	}
	public function delete_cookie() {
		$cookies = func_get_args();
		foreach ($cookies as $ck)
			setcookie($ck, '', -10, '/');
	}
	public function flash($key, $msg = null, $now = false) {
		static $x = array(),
		$f = null;
		$f = ( $this->option('cookies.flash') ? $this->option('cookies.flash') : '_F');
		if ($c = $this->get_cookie($f))
			$c = json_decode($c, true);
		else
			$c = array();
		if ($msg == null) {
			if (isset($c[$key])) {
				$x[$key] = $c[$key];
				unset($c[$key]);
				$this->set_cookie($f, json_encode($c));
			}
			return (isset($x[$key]) ? $x[$key] : null);
		}
		if (!$now) {
			$c[$key] = $msg;
			$this->set_cookie($f, json_encode($c));
		}
		$x[$key] = $msg;
	}

	public static function getBaseUri( $reload = false ) {
		if ( $reload || is_null(self::$baseUri) ) {
			$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']; //Full Request URI
			$scriptName = $_SERVER['SCRIPT_NAME']; //Script path from docroot
			$baseUri = strpos($requestUri, $scriptName) === 0 ? $scriptName : str_replace('\\', '/', dirname($scriptName));
			self::$baseUri = rtrim($baseUri, '/');
		}
		return self::$baseUri;
	}
	protected static function generateErrorMarkup( $message, $file = '', $line = '', $trace = '' ) {
		$body = '<p>The application could not run because of the following error:</p>';
		$body .= "<h2>Details:</h2><strong>Message:</strong> $message<br/>";
		if ( $file !== '' ) $body .= "<strong>File:</strong> $file<br/>";
		if ( $line !== '' ) $body .= "<strong>Line:</strong> $line<br/>";
		if ( $trace !== '' ) $body .= '<h2>Stack Trace:</h2>' . nl2br($trace);
		return self::generateTemplateMarkup('Jolt Application Error', $body);
	}
	protected static function generateTemplateMarkup( $title, $body ) {
		$html = "<html><head><title>$title</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body>";
		$html .= "<h1>$title</h1>";
		$html .= $body;
		$html .= '</body></html>';
		return $html;
	}
	protected function defaultNotFound() {
		echo self::generateTemplateMarkup('404 Page Not Found', '<p>The page you are looking for could not be found. Check the address bar to ensure your URL is spelled correctly. If all else fails, you can visit our home page at the link below.</p><a href="' . $this->getBaseUri() . '">Visit the Home Page</a>');
	}
	protected function defaultError() {
		echo self::generateTemplateMarkup('Error', '<p>A website error has occured. The website administrator has been notified of the issue. Sorry for the temporary inconvenience.</p>');
	}
}