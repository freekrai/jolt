<?php

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
