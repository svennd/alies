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

use \Clegginabox\PDFMerger\PDFMerger;

class Pdf extends CI_Model
{
	private $dompdf; 
	private $pdfmerge;

    public function __construct()
	{
		$options    =   new Options();
		
		$options->set('isRemoteEnabled', true);

	    $this->dompdf = new Dompdf($options);
		$this->dompdf->setPaper('A4', 'portrait');

		if (isset($this->conf['nameiban']['value']))
		{
			$this->dompdf->addInfo('Author', (string)base64_decode($this->conf['nameiban']['value']));
		}
		$this->dompdf->addInfo('Creator', 'github.com/svennd/alies');
		$this->pdfmerge = new PDFMerger;

	}

	public function create($html, $filename, int $mode, bool $force_remake = false)
	{

		$file_exists = file_exists((string)$filename . '.pdf');

		// check if it already exists on generation
		if ($mode == PDF_FILE && $file_exists && !$force_remake)
		{
			return $filename . ".pdf";
		}

		// if they want to open it also check pre-building
		if ($mode == PDF_STREAM && $file_exists && !$force_remake)
		{
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="' . $filename . '.pdf' . '"');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');
			readfile($filename . '.pdf');
			return;
		}

		$this->dompdf->loadHtml($html);

		$this->dompdf->render();

		// add pagination
		$canvas = $this->dompdf->getCanvas(); // get the canvas
		// add the page number and total number of pages
		$canvas->page_script('
			if($PAGE_COUNT > 1) {
			$text = "$PAGE_NUM / $PAGE_COUNT";
    		$pdf->text(535, 791.89, $text, \'Helvetica\', 10, array(0,0,0));
			}
		');

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

	public function merge_pdf(string $filename, array $list)
	{
		foreach ($list as $pdf)
		{
			$this->pdfmerge->addPDF($pdf, 'all');
		}

		$this->pdfmerge->merge('file', $filename, 'P');
		return $filename;
	}
}