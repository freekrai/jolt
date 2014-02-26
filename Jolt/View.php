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

class View{
	protected  $app;

	function __construct( ){
	}

	/**
	* Set application
	*
	* This method injects the primary Jolt application instance into
	* this view.
	*
	* @param  \Jolt\Jolt $application
	*/
	final public function setApp( $app ){
		$this->app = $app;
	}
	
	/**
	* Get application
	*
	* This method retrieves the application previously injected
	* into this middleware.
	*
	* @return \Jolt\Jolt
	*/
	final public function getApp(){
		return $this->app;
	}

	public function render($view, $locals = null, $layout = null){
		if ( is_array($locals) && count($locals) ) {
			extract($locals, EXTR_SKIP);
		}
		if (($view_root = $this->app->option('views.root')) == null)
			$this->app->error(500, "[views.root] is not set");
		ob_start();
		include "{$view_root}/{$view}.php";
		$this->app->content(trim(ob_get_clean()));
		if ($layout !== false) {
			if ($layout == null) {
				$layout = $this->app->option('views.layout');
				$layout = ($layout == null) ? 'layout' : $layout;
			}
			$layout = "{$view_root}/{$layout}.php";	
			header('Content-type: text/html; charset=utf-8');
			$pageContent = $this->app->content();
			ob_start();
			require $layout;
			echo trim( ob_get_clean() );
		} else {
			//	no layout
			echo $this->app->content();
		}
	}
}
