<?php

class Jolt{
	public $name;
	public $debug = false;
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
	public function add_route($method = 'GET',$pattern,$cb = null){
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
	}
	function route_to_regex($route) {
		$route = preg_replace_callback('@:[\w]+@i', function ($matches) {
			$token = str_replace(':', '', $matches[0]);
			return '(?P<'.$token.'>[a-z0-9_\0-\.]+)';
		}, $route);
		return '@^'.rtrim($route, '/').'$@i';
	}
	public function route($pattern,$cb = null){
		return $this->add_route('GET',$pattern,$cb);
	}
	public function get($pattern,$cb = null){
		return $this->add_route('GET',$pattern,$cb);
	}
	public function post($pattern,$cb = null){
		return $this->add_route('GET',$pattern,$cb);
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
	function option($key, $value = null) {
		static $_option = array();
		if ($key === 'source' && file_exists($value))
			$_option = parse_ini_file($value, true);
		else if ($value == null)
			return (isset($_option[$key]) ? $_option[$key] : null);
		else
			$_option[$key] = $value;
	}
	function error($code, $message) {
		@header("HTTP/1.0 {$code} {$message}", true, $code);
		die($message);
	}
	function warn($name = null, $message = null) {
		static $warnings = array();
		if ($name == '*')
			return $warnings;
		if (!$name)
			return count(array_keys($warnings));
		if (!$message)
			return isset($warnings[$name]) ? $warnings[$name] : null ;
		$warnings[$name] = $message;
	}
	function from($source, $name) {
		if (is_array($name)) {
			$data = array();
			foreach ($name as $k)
				$data[$k] = isset($source[$k]) ? $source[$k] : null ;
			return $data;
		}
		return isset($source[$name]) ? $source[$name] : null ;
	}
	function store($name, $value = null) {
		static $_store = array();
		if ($value === null)
			return isset($_store[$name]) ? $_store[$name] : null;
		$_store[$name] = $value;
		return $value;
	}
	function method($verb = null) {
		if ($verb == null || (strtoupper($verb) == strtoupper($_SERVER['REQUEST_METHOD'])))
		return strtoupper($_SERVER['REQUEST_METHOD']);
		
		$this->error(400, 'bad request');
	}		
	function client_ip() {
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}
	
	function partial($view, $locals = null) {
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
	function content($value = null) {
		return $this->store('$content$', $value);
	}	
	function render($view, $locals = null, $layout = null) {
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
	function json($obj, $code = 200) {
		//	output a json stream
		header('Content-type: application/json', true, $code);
		echo json_encode($obj);
		exit;
	}
	function condition() {
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
	function middleware($cb_or_path = null) {	
		static $cb_map = array();
		if ($cb_or_path == null || is_string($cb_or_path)) {
			foreach ($cb_map as $cb) {
				call_user_func($cb, $cb_or_path);
			}
		} else {
			array_push($cb_map, $cb_or_path);
		}
	}
	function filter($sym, $cb_or_val = null) {
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
}