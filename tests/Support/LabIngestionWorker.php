<?php

declare(strict_types=1);

$workerArgs = $argv;
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once APPPATH . 'third_party/api/devices/DeviceAdapterInterface.php';

final class ConcurrentLabSourceAdapter implements DeviceAdapterInterface
{
	public function __construct(private string $sourceId, private ?int $petId, private string $value) {}

	public function parse(array $payload)
	{
		return array(
			'device' => 'phpunit',
			'source' => 'remote',
			'source_id' => $this->sourceId,
			'pet_id' => $this->petId,
			'sample_date' => '2026-08-30 12:00:00',
			'results' => array(array(
				'code' => 'GLU',
				'value' => $this->value,
				'unit' => 'mmol/L',
				'ref_min' => 4.0,
				'ref_max' => 7.0,
			)),
			'plots' => array('curve' => array(1, 2, 3)),
		);
	}
}

if (count($workerArgs) !== 4) {
	fwrite(STDERR, "Expected source ID, pet ID or '-', and value.\n");
	exit(2);
}

$sourceId = $workerArgs[1];
$petId = $workerArgs[2] === '-' ? null : (int) $workerArgs[2];
$value = $workerArgs[3];
$CI =& get_instance();
$CI->load->library('LabResultService');

try {
	$result = $CI->labresultservice->ingest(
		new ConcurrentLabSourceAdapter($sourceId, $petId, $value),
		array('source_id' => $sourceId, 'value' => $value)
	);
	echo json_encode($result), PHP_EOL;
} catch (Throwable $error) {
	fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . "\n");
	exit(1);
}
