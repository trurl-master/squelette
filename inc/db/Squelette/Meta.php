<?php

namespace Squelette;

use Squelette\Base\Meta as BaseMeta;

/**
 * Skeleton subclass for representing a row from the 'meta' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Meta extends BaseMeta
{
	protected $customParsed = null;

	public function getCustom()
	{

		if ($this->customParsed === null) {
			$this->customParsed = $this->custom === '' || $this->custom === NULL ? [] : json_decode($this->custom, true);
		}

		return $this->customParsed;
	}
}
