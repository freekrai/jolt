<?php

namespace Jolt;

class HttpRequest{
	const METHOD_HEAD = 'HEAD';
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_PATCH = 'PATCH';
	const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_OVERRIDE = '_METHOD';
    protected static $formDataMediaTypes = array('application/x-www-form-urlencoded');
	protected $method;
	private $get;
	private $post;
	private $env;
	private $headers;
	private $cookies;
	public function __construct(){
		$this->env = $_SERVER;
		$this->method = isset($this->env['REQUEST_METHOD']) ? $this->env['REQUEST_METHOD'] : false;
		$this->get = $_GET;
		$this->post = $_POST;
        $this->headers = new Headers( Headers::extract($this->env) );
        @$this->cookies = Headers::parseCookieHeader( $this->env['HTTP_COOKIE'] );
	}
    public function getMethod(){
        return $this->env['REQUEST_METHOD'];
    }
	public function isGet() {
		return $this->getMethod() === self::METHOD_GET;
	}
	public function isPost() {
		return $this->getMethod() === self::METHOD_POST;
	}
	public function isPut() {
		return $this->getMethod() === self::METHOD_PUT;
	}
	public function isPatch() {
		return $this->getMethod() === self::METHOD_PATCH;
	}
	public function isDelete() {
		return $this->getMethod() === self::METHOD_DELETE;
	}
	public function isHead() {
		return $this->getMethod() === self::METHOD_HEAD;
	}
	public function isOptions(){
		return $this->getMethod() === self::METHOD_OPTIONS;
	}
	public function isAjax() {
		return ( $this->params('isajax') || $this->headers('X_REQUESTED_WITH') === 'XMLHttpRequest' );
	}
	public function isXhr(){
		return $this->isAjax();
	}
	public function params( $key ) {
		foreach( array('post', 'get') as $dataSource ){
			$source = $this->$dataSource;
			if ( isset($source[(string)$key]) ){
				return $source[(string)$key];
			}
		}
		return null;
	}
	public function post($key){
		if( isset($this->post[$key]) ){
			return $this->post[$key];
		}
		return null;
	}
	public function get($key){
		if( isset($this->get[$key]) ){
			return $this->get[$key];
		}
		return null;
	}
	public function put($key = null){
		return $this->post($key);
	}
	public function patch($key = null){
		return $this->post($key);
	}
	public function delete($key = null){
		return $this->post($key);
	}
	public function cookies($key = null){
		if ($key) {
			return $this->cookies->get($key);
		}
		return $this->cookies;
	}
	public function isFormData(){
		$method = $this->getMethod();
		return ($method === self::METHOD_POST && is_null($this->getContentType())) || in_array($this->getMediaType(), self::$formDataMediaTypes);
	}
	public function headers($key = null, $default = null){
		if ($key) {
			return $this->headers->get($key, $default);
		}
		return $this->headers;
	}
	public function getBody(){
		return $this->env['slim.input'];
	}
	public function getContentType(){
		return $this->headers->get('CONTENT_TYPE');
	}
	public function getMediaType(){
		$contentType = $this->getContentType();
		if ($contentType) {
			$contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
			return strtolower($contentTypeParts[0]);
		}
		return null;
	}
	public function getMediaTypeParams(){
		$contentType = $this->getContentType();
		$contentTypeParams = array();
		if ($contentType) {
			$contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
			$contentTypePartsLength = count($contentTypeParts);
			for ($i = 1; $i < $contentTypePartsLength; $i++) {
				$paramParts = explode('=', $contentTypeParts[$i]);
				$contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
			}
		}
		return $contentTypeParams;
	}
    public function getContentCharset(){
		$mediaTypeParams = $this->getMediaTypeParams();
		if (isset($mediaTypeParams['charset'])) {
			return $mediaTypeParams['charset'];
		}
		return null;
    }
    public function getContentLength(){
        return $this->headers->get('CONTENT_LENGTH', 0);
    }
    public function getHost(){
        if (isset($this->env['HTTP_HOST'])) {
            if (strpos($this->env['HTTP_HOST'], ':') !== false) {
                $hostParts = explode(':', $this->env['HTTP_HOST']);
                return $hostParts[0];
            }
            return $this->env['HTTP_HOST'];
        }
        return $this->env['SERVER_NAME'];
    }
    public function getHostWithPort(){
        return sprintf('%s:%s', $this->getHost(), $this->getPort());
    }
    public function getPort(){
        return (int)$this->env['SERVER_PORT'];
    }
    public function getScheme(){
		return ( empty($this->env['HTTPS']) || $this->env['HTTPS'] === 'off' ) ? 'http' : 'https';
    }
    public function getScriptName()
    {
        return $this->env['SCRIPT_NAME'];
    }
    public function getRootUri()
    {
        return $this->getScriptName();
    }
    public function getPath()
    {
        return $this->getScriptName() . $this->getPathInfo();
    }
    public function getPathInfo()
    {
        return $this->env['PATH_INFO'];
    }
    public function getResourceUri()
    {
        return $this->getPathInfo();
    }
    public function getUrl()
    {
        $url = $this->getScheme() . '://' . $this->getHost();
        if (($this->getScheme() === 'https' && $this->getPort() !== 443) || ($this->getScheme() === 'http' && $this->getPort() !== 80)) {
            $url .= sprintf(':%s', $this->getPort());
        }

        return $url;
    }
    public function getIp()
    {
        if (isset($this->env['X_FORWARDED_FOR'])) {
            return $this->env['X_FORWARDED_FOR'];
        } elseif (isset($this->env['CLIENT_IP'])) {
            return $this->env['CLIENT_IP'];
        }

        return $this->env['REMOTE_ADDR'];
    }
    public function getReferrer()
    {
        return $this->headers->get('HTTP_REFERER');
    }
    public function getReferer()
    {
        return $this->getReferrer();
    }
    public function getUserAgent()
    {
        return $this->headers->get('HTTP_USER_AGENT');
    }
}

class DataStore {
	public $token;
	public function __construct($token){
		$this->token = $token;
		$path = '_cache/';
		if( !is_dir($path) ){
			mkdir($path,0777);
		}
		if( !is_writable($path) ){
			chmod($path,0777);
		}
		return true;
	}
	public function Get($key){
		return $this->_fetch($this->token.'-'.$key);
	}
	public function Set($key,$val,$ttl=6000){
		return $this->_store($this->token.'-'.$key,$val,$ttl);
	}
	public function Delete($key){
		return $this->_nuke($this->token.'-'.$key);
	}
	private function _getFileName($key) {
		return '_cache/' . ($key).'.store';
	}
	private function _store($key,$data,$ttl) {
		$h = fopen($this->_getFileName($key),'a+');
		if (!$h) throw new Exception('Could not write to cache');
		flock($h,LOCK_EX);
		fseek($h,0);
		ftruncate($h,0);
		$data = serialize(array(time()+$ttl,$data));
		if (fwrite($h,$data)===false) {
			throw new Exception('Could not write to cache');
		}
		fclose($h);
	}
	private function _fetch($key) {
		$filename = $this->_getFileName($key);
		if (!file_exists($filename)) return false;
		$h = fopen($filename,'r');
		if (!$h) return false;
		flock($h,LOCK_SH);
		$data = file_get_contents($filename);
		fclose($h);
		$data = @unserialize($data);
		if (!$data) {
			unlink($filename);
			return false;
		}
		if (time() > $data[0]) {
			unlink($filename);
			return false;
		}
		return $data[1];
	}
	private function _nuke( $key ) {
		$filename = $this->_getFileName($key);
		if (file_exists($filename)) {
			return unlink($filename);
		} else {
			return false;
		}
	}
}