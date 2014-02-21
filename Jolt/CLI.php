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

class CLI{
	public $arguments = array();
	public function __construct( $argv ){
		$this->arguments = $this->get_arguments( $argv );
	}
	private function get_arguments($argv) {
		$_ARG = array();
		foreach ($argv as $arg) {
			if ( preg_match('/--(.*)=(.*)/',$arg,$reg) ) {
				$_ARG[$reg[1]] = $reg[2];
			} elseif( preg_match('/-([a-zA-Z0-9])/',$arg,$reg) ) {
				$_ARG[$reg[1]] = 'true';
			}
		}
		return $_ARG;
    }
}
