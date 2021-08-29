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

require_once(dirname(__FILE__) . '/dompdf/autoload.inc.php');
use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf
{
	public function create($html, $filename, $download = false)
    {
		
		$options    =   new Options();
		$options->set('enable_html5_parser', true);
		$options->set('isRemoteEnabled', true);
		
	    $dompdf = new Dompdf($options);
	    $dompdf->loadHtml($html);
	    $dompdf->render();
	    $dompdf->stream($filename.'.pdf', array("Attachment" => $download));
  }
}