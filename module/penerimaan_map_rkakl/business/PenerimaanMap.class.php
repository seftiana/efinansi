<?php

class PenerimaanMap extends Database {

	protected $mSqlFile= 'module/penerimaan_map_rkakl/business/penerimaanmap.sql.php';

	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);

	}

	function GetData($offset, $limit) {
		$result = $this->Open($this->mSqlQueries['get_data'], array($offset, $limit));
		return $result;
	}


	function GetCountData() {
		$result = $this->Open($this->mSqlQueries['get_count_data'], array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

	function GetDataById($map_rkakl) {

		$result = $this->Open($this->mSqlQueries['get_data_by_id'], array($map_rkakl));
		return $result[0];
	}

	function GetDataDetil($map_rkakl) {
		$result = $this->Open($this->mSqlQueries['get_data_detil'], array($map_rkakl));

		return $result;
	}
}

?>