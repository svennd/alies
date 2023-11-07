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

	public function create($html, $filename, int $mode)
	{
		
		$this->dompdf->loadHtml($html);
		$this->dompdf->render();

		if ($mode == PDF_DOWNLOAD)
		{
			$this->dompdf->stream($filename.'.pdf', array("Attachment" => true));
			exit;
		}
		elseif ($mode == PDF_STREAM)
		{
			$this->dompdf->stream($filename.'.pdf', array("Attachment" => false));
			exit;
		}
		// pdf FILE
		else
		{
			// create file
			file_put_contents($filename. '.pdf', $this->dompdf->output());
			return $filename . ".pdf";
		}

	}

	// legacy wrapper
	public function create_file($html, $filename)
	{
		$this->create($html, $filename, PDF_FILE);
	}
}