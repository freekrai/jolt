<?php
/**
 * Jolt - a micro PHP 5 framework
 *
 * @author      Roger Stringer <roger.stringer@me.com>
 * @copyright   2013 Roger Stringer
 * @link        http://www.joltframework.com
 * @license     http://www.joltframework.com/license
 * @version     2.0
 * @package     Jolt
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Jolt;

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