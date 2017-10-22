<?php

namespace Squelette;

use Squelette\Base\ConfigQuery as BaseConfigQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'config' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class ConfigQuery extends BaseConfigQuery
{
	public static function getParsed($subset = null)
	{

		if ($subset === null) {
			$data = \Top50\ConfigQuery::create()->find();
		} else {
			$data = \Top50\ConfigQuery::create()->filterByKey($subset)->find();
		}

		// $data->toArray(null, false, \Propel\Runtime\Map\TableMap::TYPE_FIELDNAME);

		$result = [];
		$i = 0;

		foreach ($data as $item) {
			$result[$i] = [
				'key' => $item->getKey(),
				'value' => $item->getValue(),
				'type' => $item->getType(),
				'label' => $item->getLabel(),
				'note' => $item->getNote()
			];

			switch ($result[$i]['type']) {
				case 'select':
					$result[$i]['params'] = json_decode($item->getParams(), true);
					break;

				default: break;
			}


			$i++;
		}

		return $result;

	}

	public static function getConfigValue($key)
	{
		$item = \Top50\ConfigQuery::create()->filterByKey($key)->findOne();

		return $item->getValue();
	}

}
