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

class Controller{
	protected  $app;
	protected static $nonce = NULL;
	function __construct(){
	 	$this->app  = Jolt::getInstance();
	}
	protected function sanitize( $dirty ){
		return htmlentities(strip_tags($dirty), ENT_QUOTES);
	}

	protected function generate_nonce(  ){
		// Checks for an existing nonce before creating a new one
		if (empty(self::$nonce)) {
			self::$nonce = base64_encode(uniqid(NULL, TRUE));
			$_SESSION['nonce'] = self::$nonce;
		}
		return self::$nonce;
	}
	protected function check_nonce(  ){
		if (
		isset($_SESSION['nonce']) && !empty($_SESSION['nonce']) 
		&& isset($_POST['nonce']) && !empty($_POST['nonce']) 
		&& $_SESSION['nonce']===$_POST['nonce']
			) {
			$_SESSION['nonce'] = NULL;
			return TRUE;
		} else {
			return FALSE;
		}
	}

}


?>