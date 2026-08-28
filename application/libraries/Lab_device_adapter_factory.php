<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lab_device_adapter_factory
{
	public function create($device)
	{
		switch (strtolower((string) $device)) {
			case 'ms4s2':
				require_once APPPATH . 'third_party/api/devices/Ms4s2.php';
				return new Ms4s2();
			case 'ikems':
				require_once APPPATH . 'third_party/api/devices/Ikems.php';
				return new Ikems();
			case 'lmscan':
				require_once APPPATH . 'third_party/api/devices/Lmscan.php';
				return new Lmscan();
			case 'medilab':
				require_once APPPATH . 'third_party/api/devices/Medilab.php';
				return new Medilab();
		}

		return null;
	}
}
