<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

use SepaQr\Data;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;

class Qr extends CI_Model
{
    /* private */
    private $sepa_name, $sepa_iban;
	private $SepaQr; 
	private $QRCode; 

    public function __construct()
	{
		// sepa text
	    $this->SepaQr = new data();

		$qrOptions = new QROptions([
			'addQuietzone' 	=> true,
			'quietzoneSize'	=> 4,
			'scale'			=> 5,
			'versionMax'	=> 13,
			'outputType' 	=> 'png',
			'eccLevel' => EccLevel::Q 	// EPC standard requires M (15%), we take caution and use Q (25%)
										// H would be 30% and is the highest level of error correction
		]);
		$this->QRCode = new QRCode($qrOptions);

		// pull from config
		// $this->sepa_name = $this->config->item('sepa_name');
		// $this->sepa_iban = $this->config->item('sepa_iban');
		$this->sepa_name = "Svenn D'Hert";
		$this->sepa_iban = "BE47850844587280";
		
	}

	public function create(float $amount, string $message)
	{
		$paymentData = $this->SepaQr->create()
			->setName($this->sepa_name)
			->setIban($this->sepa_iban)
			->setRemittanceText($message)
			->setAmount($amount); // The amount in Euro

		// add time minutes
		// header('Content-type: image/png');

		return $this->QRCode->render($paymentData);
		// $this->QRCode->render($paymentData, "data/stored/qr/" . date("His") . "sepa.png");
	}
	
}