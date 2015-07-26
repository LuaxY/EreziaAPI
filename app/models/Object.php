<?php

class Object extends \Eloquent {

	protected $primaryKey = 'id';

	protected $table = 'shop_items';

	public $timestamps = false;

    protected $connection = 'web';

    public function item()
    {
        return $this->hasOne('Item', 'Id', 'item_id');
    }

}
