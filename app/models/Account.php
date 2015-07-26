<?php

use Illuminate\Auth\UserInterface;

class Account extends \Eloquent implements UserInterface {

	protected $primaryKey = 'Id';

	protected $fillable = array(
		'Login',
		'PasswordHash',
		'Nickname',
		'Role',
		'Ticket',
		'SecretQuestion',
		'SecretAnswer',
		'Lang',
		'Email',
		'CreationDate',
		'SubscriptionEnd',
		'LastVote',
		'VoteCount',
	);

	protected $table = 'accounts';

	protected $connection = 'auth';

	public $timestamps = false;

	protected $hidden = array('PasswordHash');

	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	public function getAuthPassword()
	{
		return $this->PasswordHash;
	}

	public function getRememberToken()
	{
		return null; // not supported
	}

	public function setRememberToken($value)
	{
		// not supported
	}

	public function getRememberTokenName()
	{
		return null; // not supported
	}

	public function isAdmin()
	{
		if ($this->Role >= 4)
			return true;
		else
			return false;
	}

	public function isStaff()
	{
		if ($this->Role > 1)
			return true;
		else
			return false;
	}
}
