<?php

//action.php

$connect = new PDO("mysql:host=localhost;dbname=testing", "root", "");

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'fetch')
	{
		$order_column = array('order_number', 'order_total', 'order_date');
		//$order_column = array('employee_name', 'present_day', 'date');
		$main_query = "SELECT order_number, SUM(order_total) AS order_total, order_date 
		FROM test_order_table 
		";
		//$main_query="SELECT a.code,a.edesc,(SELECT count(DISTINCT c.log_no) FROM hr_attendance_log b, hr_attendance_detail c where a.code=c.employee_code AND b.log_no=c.log_no AND b.log_date>='2020-12-16' AND b.log_date<='2021-01-16') AS present FROM hr_employee_setup a where a.branch_code='1002'";

		$search_query = 'WHERE order_date <= "'.date('Y-m-d').'" AND ';

		if(isset($_POST["start_date"], $_POST["end_date"]) && $_POST["start_date"] != '' && $_POST["end_date"] != '')
		{
			$search_query .= 'order_date >= "'.$_POST["start_date"].'" AND order_date <= "'.$_POST["end_date"].'" AND ';
		}

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= '(order_number LIKE "%'.$_POST["search"]["value"].'%" OR order_total LIKE "%'.$_POST["search"]["value"].'%" OR order_date LIKE "%'.$_POST["search"]["value"].'%")';
		}



		$group_by_query = " GROUP BY order_date ";

		$order_by_query = "";

		if(isset($_POST["order"]))
		{
			$order_by_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_by_query = 'ORDER BY order_date DESC ';
		}

		$limit_query = '';

		if($_POST["length"] != -1)
		{
			$limit_query = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}

		$statement = $connect->prepare($main_query . $search_query . $group_by_query . $order_by_query);

		$statement->execute();

		$filtered_rows = $statement->rowCount();

		$statement = $connect->prepare($main_query . $group_by_query);

		$statement->execute();

		$total_rows = $statement->rowCount();

		$result = $connect->query($main_query . $search_query . $group_by_query . $order_by_query . $limit_query, PDO::FETCH_ASSOC);
//print $result;
		$data = array();

		foreach($result as $row)
		{
			$sub_array = array();

			$sub_array[] = $row['order_number'];

			$sub_array[] = $row['order_total'];

			$sub_array[] = $row['order_date'];

			$data[] = $sub_array;
		}

		$output = array(
			"draw"			=>	intval($_POST["draw"]),
			"recordsTotal"	=>	$total_rows,
			"recordsFiltered" => $filtered_rows,
			"data"			=>	$data
		);

		echo json_encode($output);
	}
}

?>