<?php

include("SimpleXLS.php");
require_once('config.php');
$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_password = $config['db_password'];
$db_name = $config['db_name'];
$secret = $config['secret'];
$xls = SimpleXLS::parse('file.xls');

if (!$xls) {
	die("xml failed: " . SimpleXLS::parseError());
}

$flag = true;
$table = true;
$tableName = "";
$tableId = "";
$arr = [];
$arrCol = [];
$numRows = count($xls->rows());
$n = 0;
//get all data from xml
foreach ($xls->rows() as $row) {
	$n++;
	$table = ($row[0] != "") ? true : false;
	if ($table) {
		if (!$flag) {
			$arr[] = [$tableName, $tableId, $arrCol];
			$arrCol = [];
		}
		$tableName = trim($row[0]);
		$tableId = trim($row[3]);
		$arrCol[] = [trim($row[1]), trim($row[2])];
	} else {
		$arrCol[] = [trim($row[1]), trim($row[2])];;
	}
	$flag = false;
	if ($numRows == $n) {
		$arr[] = [$tableName, $tableId, $arrCol];
	}
}

if (count($arr)) {

	$conn = mysqli_connect($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);

	if (!$conn) {
		die("Connection failed: " . mysqli_connect_error());
	}

	foreach ($arr as $row) {
		$arrColumns = [];
		$tableName = $row[0];
		$tableId = $row[1];
		$columns = $row[2];
		
		foreach ($columns as $column) {
			$arrColumns[] = $column[0];
		}

		$sqlquery = 'SELECT ' . $tableId . ', '  . implode(', ', $arrColumns) . ' FROM ' . $tableName . ';';
		$result = mysqli_query($conn, $sqlquery);
		
		if ($result) {
			while ($line = mysqli_fetch_row($result)) {
				$obfuscated = [];
				$srtQuery = 'UPDATE ' . $tableName . ' SET ';
				for ($i = 0; $i < count($columns); $i++) {
					$obfuscated[] = getData($columns[$i][0], $columns[$i][1], $line[$i + 1], $secret);
				}
				$srtQuery .= implode(', ', $obfuscated) . ' WHERE ' . $tableId . ' = ' .	((is_numeric($line[0])) ? $line[0] : '\'' . $line[0] . '\'') . ';';				
				$result2 = mysqli_query($conn, $srtQuery);
			}
			mysqli_free_result($result);
		}
	}
	$conn->close();
	echo "OK";
}

function getData($name, $type, $data, $secret)
{
	switch ($type) {
		case "numeric": {
				$length = strlen((string) (int)$data);
				$min = 1 . str_repeat(0, $length - 1);
				$max = str_repeat(9, $length);
				return $name . '=' . rand((int)$min, (int)$max);;
			}
		case "varchar": {
				$size = strlen($data);
				return $name . ' = \'' . substr(hash_hmac('sha256', $data, $secret), 0, $size) . '\'';
			}
		case "date": {
				return $name . "=CURDATE()";
			}
		case "email": {
				$email = explode("@", $data)[0];
				$size = strlen($email);
				return $name . ' = \'' . substr(hash_hmac('sha256', $data, $secret), 0, $size) . '@mordigital.ie\'';
			}
		default:
			break;
	}
}
