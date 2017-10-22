<?php

namespace Squelette;

// use \App;
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

		$sql = "UPDATE $table SET ord = CASE id $cases END WHERE id IN($id_list)";

		$stmt = $con->prepare($sql);

		return $stmt->execute();

		// var_dump($result);

		// echo $this->filterById($ord)->toString();

		// var_dump($ord);

		// $collection = $this->filterById($ord)->find();

		// foreach ($ord as $order_index => $id) {
		// 	$collection[$order_index]->setOrd($order_index);
		// 	echo $collection[$order_index]->getTitle() , "\n";
		// }

		// var_dump($collection->toString());


		// var_dump($ord);

		// if ($id_order_list === null) {
		//     $id_order_list = Input::get_num_list('order', Input::$required);
		// }

		// if (is_string($id_order_list)) {
		//     $id_order_list = explode(',', $id_order_list);
		// }


		// $shift = Input::get_int('order_shift', 0);
		// $dir = Input::get_task_param('order_dir', 'ASC'); //

		// $cases = '';

		// $total = count($id_order_list) - 1;

		// foreach ($id_order_list as $order => $id) {
		//     $cases .= ' WHEN '.$id.' THEN ';

		//     if ($dir === 'ASC') {
		//         $cases .= intval($order) - $shift;
		//     } else {
		//         $cases .= $total - intval($order) - $shift;
		//     }
		// }

		// return $dbs->query("UPDATE ?# SET `order` = CASE id".$cases." END WHERE id IN(?a)", self::$prefix.$table, $id_order_list);
	}

}
