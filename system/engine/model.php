<?php 
/**
 * @property DB $db
 * @property Image $image
 * @property Store $store
 * @property Currency $currency
 * @property ModelStoreProduct $model_store_product
 * @property Loader $load
 */

abstract class Model {

	protected $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}
}