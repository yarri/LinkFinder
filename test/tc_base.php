<?php
class TcBase extends TcSuperBase {

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
