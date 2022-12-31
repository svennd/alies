<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Product_price_model extends MY_Model
{
	public $table = 'products_price';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['products'] = array(
							'foreign_model' => 'Products_model',
							'foreign_table' => 'product',
							'foreign_key' => 'id',
							'local_key' => 'product_id'
						);
		parent::__construct();
	}

	/* products :
	 *  pid : product id
	 *  price: float/decimal
	 *  volume : float/decimal
	 */
	public function add_price(int $pid, $price, $volume)
	{
		$this->logs->logger(INFO, "add_price", "Add price:" . (int) $price . " for volume: ". (float) $volume . " for pid " . $pid);

		return $this->insert(array(
					'volume' 		=> $volume,
					'price' 		=> $price,
					'product_id' 	=> $id
				));
	}

	public function update_price(int $pid, int $ppid, $price, $volume)
	{
		$this->logs->logger(INFO, "modify_price", "Change price pid:" . (int) $pid . " on price_id:" . (int) $ppid . " for volume: ". (float) $volume . " to (price)" . (float) $price);

		return $this->
				where(array(
								"id" 	=> $ppid
						))->
				update(array(
								"price" => $price,
								"volume" => $volume,
						));

	}

	/*
		remove price from a product
	*/
	public function rm_price(int $ppid)
	{
		// get the pid
		$to_remove_price = $this->get($ppid);

		if (!$to_remove_price)
		{ 
			$this->logs->logger(ERROR, "failed_to_rm_price", "rm price pid:" . (int) $ppid);
			return 0;
		}

		// actually remove it
		$this->delete($ppid);

		// log this
		$this->logs->logger(INFO, "rm_price", "rm price pid:" . (int) $ppid . " pid:" . $to_remove_price['product_id']);

		return $to_remove_price['product_id'];
	}
}
