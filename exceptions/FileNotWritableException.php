<?php

namespace greenodeen\language\exceptions;

use yii\web\ServerErrorHttpException;

class FileNotWritableException extends ServerErrorHttpException {

	public function __construct($file, $message = null, $code = 0, \Exception $previous = null) {
		if($message === null) {
			$message = "File {$file} is not writable!";
		}
		parent::__construct($message, $code, $previous);
	}
}
