<?php

namespace Squelette;

use Propel\Runtime\Propel;


trait Orderable
{

	public function setOrder($ord, $dir = 'ASC', $shift = 0)
	{

		$table_map = $this->getTableMap();
		$con = Propel::getWriteConnection($table_map::DATABASE_NAME);
		$table = $table_map::TABLE_NAME;

		$id_list = implode(',', $ord);
		$total = count($ord) - 1;
		$cases = '';

		foreach ($ord as $order => $id) {
			$cases .= ' WHEN '.$id.' THEN ';

			if ($dir === 'ASC') {
				$cases .= intval($order) - $shift;
			} else {
				$cases .= $total - intval($order) - $shift;
			}
		}

		$sql = "UPDATE $table SET rank = CASE id $cases END WHERE id IN($id_list)";

		$stmt = $con->prepare($sql);

		return $stmt->execute();
	}

}
