<?php
class CComponent {
	public function __get($name) {
		if (method_exists($this, 'get'.$name))
			return call_user_func(array($this, 'get'.$name));
	}
}
