<?php

class AppRencanaPenerimaan extends Database {

	protected $mSqlFile;
	public $_POST;
	public $_GET;

	function __construct($connectionNumber=0) {
		$this->mSqlFile 	= 'module/rincian_perhitungan_rencana_penerimaan/business/apprencanapenerimaan.sql.php';
		$this->_POST 		= is_object($_POST) ? $_POST->AsArray() : $_POST;
		$this->_GET 		= is_object($_GET) ? $_GET->AsArray() : $_GET;
		parent::__construct($connectionNumber);
	}

	function GetCountData($tahunAnggaran, $unitid)
	{

		$sql = sprintf($this->mSqlQueries['get_count_data'],
								$tahunAnggaran,
								$tahunAnggaran,
								$unitid,'%',
								$unitid);
		$data = $this->Open($sql, array());
		//echo "<pre style='font-size:11px;'> "; ECHO($sql); echo "</pre>";
		if (!$data) {
			return 0;
		} else {
			return $data[0]['total'];
		}
	}

	//yg dipake ini----
	function GetDataUnitkerja($tahunAnggaran, $unitid,$startRec = 0,$itemView = 0)
	{
		if($itemView > 0){
			$sql = ' LIMIT '.$startRec.','.$itemView;
		} else {
			$sql = '';
		}

		$query = sprintf($this->mSqlQueries['get_data_unitkerja'],
							$tahunAnggaran,
							$tahunAnggaran,
							$unitid,'%',
							$unitid,
							$sql);
		$result = $this->Open($query, array());
		for($i = 0 ; $i < count($result); $i++){
			$result[$i]['pagu']=$this->FormatNumberPersen($result[$i]['pagu']);
		}
		return $result;
	}
	//-------

	function GetDataForTotal($tahunAnggaran, $userId, $unitid) {
		$result = $this->Open($this->mSqlQueries['get_data_for_total'],
			array(
					$tahunAnggaran,
					$tahunAnggaran,
					$unitid,'%',
					$unitid));
			//echo $this->getLastError();
		return $result;
	}

	function GetDataRencanaPenerimaanById($id) {
		$result = $this->Open($this->mSqlQueries['get_data_rencana_penerimaan_by_id'], array($id));
		return $result;
	}
	//get combo tahun anggaran
	function GetComboTahunAnggaran() {
		$result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
		return $result;
	}
	function GetTahunAnggaranAktif() {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
		return $result[0];
	}
	function GetTahunAnggaran($id) {
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array($id));
		return $result[0];
	}
	/**
	 * format number persen
	 * jika koma maka tampilkan dengan angka dibelakang koma
	 * jika tidak ada koma maka ditampilkan tanpa koma
	 */
	protected function FormatNumberPersen($number = 0)
	{
		if($number != NULL){
			$snumber = number_format($number,2,',','.');
			$split_snumber =explode(',',$snumber);
			if(is_array($split_snumber)){
				if(intval($split_snumber[1])> 0){
					$desimal = floatval('0.'.$split_snumber[1]);
					return $split_snumber[0] + $desimal;
				} else {
					return $split_snumber[0];
				}
			} else {
				return 0;
			}
		} else {
			return '';
		}
	}

	/*
	 * @param string $camelCasedWord Camel-cased word to be "underscorized"
	 * @param string $case case type, uppercase, lowercase
	 * @return string Underscore-syntaxed version of the $camelCasedWord
	 */
	public static function humanize($camelCasedWord, $case = 'upper')
	{
	   switch ($case) {
	      case 'upper':
	         $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
	         break;
	      case 'lower':
	         $return     = strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
	         break;
	      case 'title':
	         $return     = ucwords(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
	         break;
	      case 'sentences':
	         $return     = ucfirst(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
	         break;
	      default:
	         $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
	         break;
	   }
	   return $return;
	}

	/*
	 * @desc change key name from input data
	 * @param array $input
	 * @param string $case based on humanize method
	 * @return array
	 */
	public function ChangeKeyName($input = array(), $case = 'lower')
	{
	   if(!is_array($input)){
	      return $input;
	   }

	   foreach ($input as $key => $value) {
	      if(is_array($value)){
	         foreach ($value as $k => $v) {
	            $array[$key][self::humanize($k, $case)] = $v;
	         }
	      }
	      else{
	         $array[self::humanize($key, $case)]  = $value;
	      }
	   }

	   return (array)$array;
	}
}
?>