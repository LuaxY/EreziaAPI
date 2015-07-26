<?php

class Category extends \Eloquent {

	protected $primaryKey = 'id';

	protected $table = 'shop_categories';

	public $timestamps = false;

    protected $connection = 'web';

    public function childs()
    {
        return $this->hasMany('Category', 'parent', 'id');
    }

}
