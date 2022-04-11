<?php
namespace StopConfusion;

class DebugHelper 
{
	protected $file;

	public function __construct($file) {
		$this->file = $file;
	}

	public function debug($content) {
		$content = \print_r($content, true);
		$content .= "\n\n";
		if (\is_writable($this->file)) {
			$file = \fopen($this->file, 'a');
			\fwrite($file, $content);
			\fclose($file);
		} else {
			$file = \fopen($this->file, 'w');
			$written = \fwrite($file, $content);
			\fclose($file);
		}
	}

	public function delete() {
		if ( isset($this->file) && !empty($this->file) && \file_exists($this->file) ) {
			\unlink($this->file);
			return true;
		} else {
			return false;
		}
	}
}