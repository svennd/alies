<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter PDF Library
 *
 * @package			CodeIgniter
 * @subpackage		Libraries
 * @category		Libraries
 * @author			Muhanz
 * @license			MIT License
 * @link			https://github.com/hanzzame/ci3-pdf-generator-library
 *
 */

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf
{
	private $dompdf; 

    public function __construct()
	{
		$options    =   new Options();
		$options->set('enable_html5_parser', true);
		$options->set('isRemoteEnabled', true);

	    $this->dompdf = new Dompdf($options);
	}

	public function create($html, $filename, $download = false)
	{
		$this->dompdf->loadHtml($html);
		$this->dompdf->render();
		$this->dompdf->stream($filename.'.pdf', array("Attachment" => $download));
		exit;
	}

	public function create_file($html, $file)
	{
		$this->dompdf->loadHtml($html);
		$this->dompdf->render();

		// data is overwritten if it exists
		file_put_contents('data/stored/' . $file, $this->dompdf->output());

		return 'data/stored/' . $file;
	}
}