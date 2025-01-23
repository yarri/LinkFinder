<?php
class TcBase extends TcSuperBase {

	// The following solves this issue:
	// https://github.com/sebastianbergmann/phpunit/issues/3026

	private $requestTimeFloat;

	public function _setUp(){
		$this->requestTimeFloat = $_SERVER["REQUEST_TIME_FLOAT"];
		parent::_setUp();
	}

	public function _tearDown(){
		$_SERVER["REQUEST_TIME_FLOAT"] = $this->requestTimeFloat;
		parent::_tearDown();
	}
}
