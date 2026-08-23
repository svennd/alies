<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pet_avatar
{
	private const MAX_BYTES = 8388608;
	private const MAX_PIXELS = 40000000;
	private const OUTPUT_SIZE = 512;

	private $storage_path;

	public function __construct(array $config = array())
	{
		$default_path = defined('FCPATH') ? FCPATH . 'data/stored/pets/' : './data/stored/pets/';
		$this->storage_path = rtrim(isset($config['storage_path']) ? $config['storage_path'] : $default_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}

	public function store(array $source, string $crop_data): array
	{
		$source_error = $this->validate_source($source);
		if ($source_error !== null) {
			return array('success' => false, 'error' => $source_error);
		}

		$crop = $this->decode_crop($crop_data);
		if (!$crop['success']) {
			return $crop;
		}

		if (!$this->ensure_storage_path()) {
			imagedestroy($crop['image']);
			return array('success' => false, 'error' => 'storage');
		}

		$filename = 'pet_' . bin2hex(random_bytes(16)) . '.jpg';
		$temporary_path = $this->storage_path . '.' . $filename . '.tmp';
		$final_path = $this->storage_path . $filename;
		$output = imagecreatetruecolor(self::OUTPUT_SIZE, self::OUTPUT_SIZE);

		if (!$output) {
			imagedestroy($crop['image']);
			return array('success' => false, 'error' => 'processing');
		}

		$white = imagecolorallocate($output, 255, 255, 255);
		imagefilledrectangle($output, 0, 0, self::OUTPUT_SIZE, self::OUTPUT_SIZE, $white);
		$written = imagecopyresampled(
			$output,
			$crop['image'],
			0,
			0,
			0,
			0,
			self::OUTPUT_SIZE,
			self::OUTPUT_SIZE,
			$crop['width'],
			$crop['height']
		) && @imagejpeg($output, $temporary_path, 88);

		imagedestroy($output);
		imagedestroy($crop['image']);

		if (!$written || !is_file($temporary_path) || !@rename($temporary_path, $final_path)) {
			if (is_file($temporary_path)) {
				@unlink($temporary_path);
			}
			return array('success' => false, 'error' => 'storage');
		}

		@chmod($final_path, 0640);

		return array('success' => true, 'filename' => $filename);
	}

	public function path(string $filename)
	{
		if (!preg_match('/\Apet_[a-f0-9]{32}\.jpg\z/', $filename)) {
			return false;
		}

		return $this->storage_path . $filename;
	}

	public function delete(string $filename): bool
	{
		$path = $this->path($filename);
		if ($path === false || !is_file($path)) {
			return $path !== false;
		}

		return unlink($path);
	}

	private function validate_source(array $source)
	{
		$error = isset($source['error']) ? (int) $source['error'] : UPLOAD_ERR_NO_FILE;
		if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
			return 'size';
		}
		if ($error !== UPLOAD_ERR_OK || empty($source['tmp_name']) || !is_file($source['tmp_name'])) {
			return 'invalid';
		}

		$size = filesize($source['tmp_name']);
		if ($size === false || $size < 1) {
			return 'invalid';
		}
		if ($size > self::MAX_BYTES) {
			return 'size';
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($source['tmp_name']);
		if (!in_array($mime, array('image/jpeg', 'image/png'), true)) {
			return 'type';
		}

		$dimensions = @getimagesize($source['tmp_name']);
		if (!$this->dimensions_are_safe($dimensions)) {
			return 'dimensions';
		}

		$expected_type = $mime === 'image/png' ? IMAGETYPE_PNG : IMAGETYPE_JPEG;
		return (int) $dimensions[2] === $expected_type ? null : 'type';
	}

	private function decode_crop(string $crop_data): array
	{
		if (!preg_match('/\Adata:image\/(?:jpeg|png);base64,([A-Za-z0-9+\/=]+)\z/', $crop_data, $matches)) {
			return array('success' => false, 'error' => 'crop');
		}

		$decoded = base64_decode($matches[1], true);
		if ($decoded === false || $decoded === '' || strlen($decoded) > self::MAX_BYTES) {
			return array('success' => false, 'error' => 'crop');
		}

		$dimensions = @getimagesizefromstring($decoded);
		if (!$this->dimensions_are_safe($dimensions) || !in_array((int) $dimensions[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) {
			return array('success' => false, 'error' => 'crop');
		}

		$image = @imagecreatefromstring($decoded);
		if (!$image) {
			return array('success' => false, 'error' => 'crop');
		}

		return array(
			'success' => true,
			'image' => $image,
			'width' => (int) $dimensions[0],
			'height' => (int) $dimensions[1],
		);
	}

	private function dimensions_are_safe($dimensions): bool
	{
		if (!is_array($dimensions) || empty($dimensions[0]) || empty($dimensions[1])) {
			return false;
		}

		$width = (int) $dimensions[0];
		$height = (int) $dimensions[1];
		return $width > 0 && $height > 0 && ($width * $height) <= self::MAX_PIXELS;
	}

	private function ensure_storage_path(): bool
	{
		if (!is_dir($this->storage_path) && !@mkdir($this->storage_path, 0750, true)) {
			return false;
		}

		return is_writable($this->storage_path);
	}
}
