<?php

namespace greenodeen\language\exceptions;

use yii\web\ServerErrorHttpException;

class FileNotExistsException extends ServerErrorHttpException {

	public function __construct($file, $message = null, $code = 0, \Exception $previous = null) {
		if($message === null) {
			$message = "File {$file} does not exist!";
		}
		parent::__construct($message, $code, $previous);
	}
}
