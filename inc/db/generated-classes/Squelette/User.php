<?php

namespace Squelette;

use Squelette\Base\User as BaseUser;

/**
 * Skeleton subclass for representing a row from the 'user' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class User extends BaseUser
{
	const PRIVILEGE_BLOCKED = 0;
	const PRIVILEGE_ORDINARY = 1;
	const PRIVILEGE_ADMIN = 128;

	public function isAdmin()
	{
		// return $this->getPrivilege() === self::PRIVILEGE_ADMIN;
		return $this->hasPrivilege(self::PRIVILEGE_ADMIN);
	}

	public function hasPrivilege($privilege)
	{
		return $this->getPrivilege() === $privilege;
	}

	public function setPassword($password)
	{
		return parent::setPassword(password_hash($password, PASSWORD_DEFAULT));
	}
}
