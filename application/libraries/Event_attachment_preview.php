<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Event_attachment_preview
{
	private $allowed_mimes = array('image/jpeg', 'image/png', 'image/gif');

	public function supports(string $stored_mime): bool
	{
		return in_array($stored_mime, $this->allowed_mimes, true);
	}

	public function inspect(string $path, string $stored_mime)
	{
		if (!$this->supports($stored_mime) || !is_file($path) || !is_readable($path)) {
			return false;
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$actual_mime = $finfo->file($path);

		return in_array($actual_mime, $this->allowed_mimes, true) ? $actual_mime : false;
	}
}
