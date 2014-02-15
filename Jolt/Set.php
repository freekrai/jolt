<?php

namespace Jolt;

class Set implements \ArrayAccess, \Countable, \IteratorAggregate{
	protected $data = array();
	public function __construct($items = array()){
		$this->replace($items);
	}
	protected function normalizeKey($key){
		return $key;
	}
	public function set($key, $value){
		$this->data[$this->normalizeKey($key)] = $value;
	}
	public function get($key, $default = null){
		if ($this->has($key)) {
			$isInvokable = is_object($this->data[$this->normalizeKey($key)]) && method_exists($this->data[$this->normalizeKey($key)], '__invoke');
			return $isInvokable ? $this->data[$this->normalizeKey($key)]($this) : $this->data[$this->normalizeKey($key)];
		}
		return $default;
	}
	public function replace($items){
		foreach ($items as $key => $value) {
			$this->set($key, $value); // Ensure keys are normalized
		}
	}
	public function all(){
		return $this->data;
	}
	public function keys(){
		return array_keys($this->data);
	}
	public function has($key){
		return array_key_exists($this->normalizeKey($key), $this->data);
	}
	public function remove($key){
		unset($this->data[$this->normalizeKey($key)]);
	}
	public function clear(){
		$this->data = array();
	}
	public function offsetExists($offset){
		return $this->has($offset);
	}
	public function offsetGet($offset){
		return $this->get($offset);
	}
	public function offsetSet($offset, $value){
		$this->set($offset, $value);
	}
	public function offsetUnset($offset){
		$this->remove($offset);
	}
	public function count(){
		return count($this->data);
	}
	public function getIterator(){
		return new \ArrayIterator($this->data);
	}
	public function singleton($key, $value){
		$this->set($key, function ($c) use ($value) {
			static $object;
			if (null === $object) {
				$object = $value($c);
			}
			return $object;
		});
	}
	public function protect(\Closure $callable){
		return function () use ($callable) {
			return $callable;
		};
	}
}