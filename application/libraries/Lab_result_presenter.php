<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lab_result_presenter
{
	public function normalize_many(array $results): array
	{
		foreach ($results as &$result) {
			$result = $this->normalize($result);
		}
		unset($result);

		return $results;
	}

	public function normalize(array $result): array
	{
		$result['is_text'] = $result['value_num'] === null;
		$result['value'] = (
			!$result['is_text'] && strlen((string) $result['value_text']) <= 1
		) ? $result['value_num'] : $result['value_text'];

		$result['draw_plot'] = (
			!$result['is_text'] &&
			$result['ref_min'] !== null &&
			$result['ref_max'] !== null &&
			!((float) $result['ref_min'] === 0.0 && (float) $result['ref_max'] === 0.0)
		);
		$result['is_low'] = false;
		$result['is_high'] = false;
		$result['is_out'] = false;
		$result['limit'] = '';
		$result['pct'] = null;

		if ($result['draw_plot']) {
			$result['is_low'] = (float) $result['value_num'] < (float) $result['ref_min'];
			$result['is_high'] = (float) $result['value_num'] > (float) $result['ref_max'];
			$result['is_out'] = $result['is_low'] || $result['is_high'];
			$result['limit'] = $result['ref_min'] . ' - ' . $result['ref_max'];
			$result['pct'] = $this->position(
				(float) $result['value_num'],
				(float) $result['ref_min'],
				(float) $result['ref_max']
			) * 100;
		}

		return $result;
	}

	private function position(float $value, float $low, float $high): float
	{
		if ($high === $low) {
			return $value === $low ? 0.5 : ($value < $low ? 0.0 : 1.0);
		}

		$span = $high - $low;
		if ($value <= $low - $span) {
			return 0.0;
		}
		if ($value >= $high + $span) {
			return 1.0;
		}
		if ($value < $low) {
			return (($value - ($low - $span)) / $span) / 3.0;
		}
		if ($value > $high) {
			return (2.0 / 3.0) + (($value - $high) / $span) / 3.0;
		}

		return (1.0 / 3.0) + (($value - $low) / $span) / 3.0;
	}
}
