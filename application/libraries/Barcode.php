<?php

if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*	based on https://github.com/davidscotttufts/php-barcode
 *	only support code39

 * usage :
	$this->load->library('barcode');

	$barcode_key = str_pad($id, 5, 0, STR_PAD_LEFT);
	$barcode = $this->barcode->generate('por-' . $barcode_key);
 */

class Barcode
{
	# publics
	public $text 	= 'ABC-12345'; 	# text to encode in the barcode
	public $height 	= 40; 		# image height
	public $border	= 20;			# horizontal border for image
	public $barcode_dir = 'assets/barcode/';
	public $bar_width = 2;	# bar width multiplier!
	
	# privates
	private $key = ''; # converted code
	
	# code39 barcode
	protected $code_array = array(
				"0" => "111221211",				"1" => "211211112",				"2" => "112211112",				"3" => "212211111",
				"4" => "111221112",				"5" => "211221111",				"6" => "112221111",				"7" => "111211212",
				"8" => "211211211",				"9" => "112211211",				"A" => "211112112",				"B" => "112112112",
				"C" => "212112111",				"D" => "111122112",				"E" => "211122111",				"F" => "112122111",
				"G" => "111112212",				"H" => "211112211",				"I" => "112112211",				"J" => "111122211",
				"K" => "211111122",				"L" => "112111122",				"M" => "212111121",				"N" => "111121122",
				"O" => "211121121",				"P" => "112121121",				"Q" => "111111222",				"R" => "211111221",
				"S" => "112111221",				"T" => "111121221",				"U" => "221111112",				"V" => "122111112",
				"W" => "222111111",				"X" => "121121112",				"Y" => "221121111",				"Z" => "122121111",
				"-" => "121111212",				"." => "221111211",				" " => "122111211",				"$" => "121212111",
				"/" => "121211121",				"+" => "121112121",				"%" => "111212121",				"*" => "121121211"
			);
	
	# populate the key
	private function convert_input()
	{
		# length + start + stop
		$string_length = strlen($this->text) + 2;
		$text = strtoupper('*' . $this->text . '*');
		$code = '';
		
		for ($i = 1; $i <= $string_length; $i++) {
			$current_char = substr($text, ($i-1), 1);
			if (isset($this->code_array[$current_char])) {
				$code .= $this->code_array[$current_char] . "1";
			} else {
				# catch bad chars and map them to *
				$code .= $this->code_array['*'] . "1";
			}
		}
		
		# remove last space
		$this->key = substr($code, 0, strlen($code)-1);
	}
	
	public function generate($text = false)
	{
		if ($text) {
			$this->text = $text;
		}
		$this->destination = $this->barcode_dir . $this->text . ".png";
		
		# convert the text to a key;
		$this->convert_input();
		
		# adapt the dimensions
		$key_length = strlen($this->key);
		$code_length = 0;
		for ($i = 1; $i <= $key_length; $i++) {
			$code_length += (int)(substr($this->key, ($i-1), 1));
		}
		
		# dimensions
		$img_width = $this->border + ($code_length*$this->bar_width);
		$img_height = $this->height;
		
		# create the image
		$image = imagecreate($img_width, $img_height);
		
		# define the colors
		$black = imagecolorallocate($image, 0, 0, 0);
		$white = imagecolorallocate($image, 255, 255, 255);
		
		# background
		imagefill($image, 0, 0, $white);
		
		# draw barcode
		$location = 10;
		for ($position = 1 ; $position <= $key_length; $position++) {
			$cur_size = $location + (substr($this->key, ($position-1), 1) * $this->bar_width);
			imagefilledrectangle(
				$image,
				$location, 		# x1
									0, 				# y1
									$cur_size, 		# x2
									$img_height, 	# y2
									($position % 2 == 0 ? $white : $black)
			);
			$location = $cur_size;
		}
		
		# draw box for text
		$start_x = floor($img_width/3);
		$start_y = ($img_height-15);
		
		imagefilledrectangle($image, ($start_x-10), $start_y, ($start_x + $key_length - 20), $img_height, $white);
		
		# draw text
		imagestring($image, 5, $start_x, $start_y, $this->text, $black);
		
		imagepng($image, $this->destination);
		imagedestroy($image);
		
		# return image location
		return $this->destination;
	}
}
