<?php

class Item extends \Eloquent {

	protected $primaryKey = 'Id';

	protected $table = 'items_templates';

	public $timestamps = false;

    protected $connection = 'world';

}
