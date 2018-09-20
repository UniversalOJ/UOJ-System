<?php

class DB {
	public static function init() {
		global $uojMySQL;
		@$uojMySQL = mysqli_connect(UOJConfig::$data['database']['host'] . ':3306', UOJConfig::$data['database']['username'], UOJConfig::$data['database']['password'], UOJConfig::$data['database']['database']);
		if (!$uojMySQL) {
			echo 'There is something wrong with database >_<.... ' . mysqli_connect_error();
			die();
		}
	}
	public static function escape($str) {
		global $uojMySQL;
		return mysqli_real_escape_string($uojMySQL, $str);
	}
	public static function fetch($r, $opt = MYSQLI_ASSOC) {
		global $uojMySQL;
		return mysqli_fetch_array($r, $opt);
	}
	
	public static function query($q) {
		global $uojMySQL;
		return mysqli_query($uojMySQL, $q);
	}
	public static function update($q) {
		global $uojMySQL;
		return mysqli_query($uojMySQL, $q);
	}
	public static function insert($q) {
		global $uojMySQL;
		return mysqli_query($uojMySQL, $q);
	}
	public static function insert_id() {
		global $uojMySQL;
		return mysqli_insert_id($uojMySQL);
	}
		
	public static function delete($q) {
		global $uojMySQL;
		return mysqli_query($uojMySQL, $q);
	}
	public static function select($q) {
		global $uojMySQL;
		return mysqli_query($uojMySQL, $q);
	}
	public static function selectAll($q, $opt = MYSQLI_ASSOC) {
		global $uojMySQL;
		$res = array();
		$qr = mysqli_query($uojMySQL, $q);
		while ($row = mysqli_fetch_array($qr, $opt)) {
			$res[] = $row;
		}
		return $res;
	}
	public static function selectFirst($q, $opt = MYSQLI_ASSOC) {
		global $uojMySQL;
		return mysqli_fetch_array(mysqli_query($uojMySQL, $q), $opt);
	}
	public static function selectCount($q) {
		global $uojMySQL;
		list($cnt) = mysqli_fetch_array(mysqli_query($uojMySQL, $q), MYSQLI_NUM);
		return $cnt;
	}
	
	public static function checkTableExists($name) {
		global $uojMySQL;
		return DB::query("select 1 from $name") !== false;
	}
	
	public static function num_rows() {
		global $uojMySQL;
		return mysqli_num_rows($uojMySQL);
	}
	public static function affected_rows() {
		global $uojMySQL;
		return mysqli_affected_rows($uojMySQL);
	}
}