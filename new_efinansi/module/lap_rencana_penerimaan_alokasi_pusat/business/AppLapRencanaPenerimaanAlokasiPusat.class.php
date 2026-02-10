<?php

/**
 * @package lap_rencana_penerimaan_alokasi_pusat
 * @since 24 Februari 2012
 * @copyright (c) 2012 Gamatechno
 */

class AppLapRencanaPenerimaanAlokasiPusat extends Database
{

	protected $mSqlFile;
	public $_POST;
	public $_GET;

	public function __construct($connectionNumber=0)
	{
		$this->mSqlFile 	= 'module/lap_rencana_penerimaan_alokasi_pusat/business/app_lap_rencana_penerimaan_alokasi_pusat.sql.php';
		$this->_POST 		= is_object($_POST) ? $_POST->AsArray() : $_POST;
		$this->_GET 		= is_object($_GET) ? $_GET->AsArray() : $_GET;
		parent::__construct($connectionNumber);
		//$this->setdebugOn();
	}

	public function GetCountData($tahunAnggaran,$unitkerja,$kodePenerimaanId)
	{
		if($kodePenerimaanId == ''){
			$flag = 1;
		}else{
			$flag = 0;
		}

		/**
		 * unit kerja pusat
		 */
		//$unitkerja = 1;
		$data = $this->Open($this->mSqlQueries['get_count_data'],array(
												$unitkerja,'%',
												$unitkerja,
												$tahunAnggaran,
												$kodePenerimaanId,
												$flag
												));
		if (!$data) {
			return 0;
		} else {
			return $data[0]['total'];
		}
	}

	//yg dipake ini----
	public function GetDataRencanaPenerimaan($tahunAnggaran,$unitkerja,$kodePenerimaanId,$startRec=0,$itemView=0)
	{ //$this->setDebugOn();
		if($kodePenerimaanId == ''){
			$flag = 1;
		}else{
			$flag = 0;
		}
		/**
		 * unit kerja pusat
		 */
		//$unitkerja = 1;

		if($itemView > 0){
			$sql = ' LIMIT '.$startRec.','.$itemView;
		} else {
			$sql = '';
		}

		$query = sprintf($this->mSqlQueries['get_data_rencana_penerimaan'],
							$unitkerja,'%',
						 	$unitkerja,
						 	$tahunAnggaran,
						 	$kodePenerimaanId,
							$flag,
							$sql);
		/**
      	$result = $this->Open($this->mSqlQueries['get_data_rencana_penerimaan'],
	  							array( $unitkerja, $unitkerja,$tahunAnggaran,$startRec,$itemView));
		*/
		$result = $this->Open($query, array());
		for($i = 0 ; $i < count($result); $i++){
			$result[$i]['pjanuari']=$this->FormatNumberPersen($result[$i]['pjanuari']);
			$result[$i]['pfebruari']=$this->FormatNumberPersen($result[$i]['pfebruari']);
			$result[$i]['pmaret']=$this->FormatNumberPersen($result[$i]['pmaret']);
			$result[$i]['papril']=$this->FormatNumberPersen($result[$i]['papril']);
			$result[$i]['pmei']=$this->FormatNumberPersen($result[$i]['pmei']);
			$result[$i]['pjuni']=$this->FormatNumberPersen($result[$i]['pjuni']);
			$result[$i]['pjuli']=$this->FormatNumberPersen($result[$i]['pjuli']);
			$result[$i]['pagustus']=$this->FormatNumberPersen($result[$i]['pagustus']);
			$result[$i]['pseptember']=$this->FormatNumberPersen($result[$i]['pseptember']);
			$result[$i]['poktober']=$this->FormatNumberPersen($result[$i]['poktober']);
			$result[$i]['pnovember']=$this->FormatNumberPersen($result[$i]['pnovember']);
			$result[$i]['pdesember']=$this->FormatNumberPersen($result[$i]['pdesember']);
		}
		return $result;
	}
	//-------

	public function GetTotalDataRencanaPenerimaanPerBulan($tahunAnggaran,$unitkerja,$kodePenerimaanId)
	{
		if($kodePenerimaanId == ''){
			$flag = 1;
		}else{
			$flag = 0;
		}
	   //$unitkerja = 1;
       $result = $this->Open($this->mSqlQueries['get_total_data_rencana_penerimaan_per_bulan'],
	  							array(
										$unitkerja,
										'%',
										$unitkerja,
										$tahunAnggaran,
										$kodePenerimaanId,
										$flag
										));
		return $result[0];
	}

	public function GetDataRencanaPenerimaanById($id)
	{
		$result = $this->Open($this->mSqlQueries['get_data_rencana_penerimaan_by_id'], array($id));
		return $result;
	}
	//get combo tahun anggaran
	public function GetComboTahunAnggaran()
	{
		$result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
		return $result;
	}

	public function GetTahunAnggaranAktif()
	{
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
		return $result[0];
	}

	public function GetTahunAnggaran($id)
	{
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
	}
}

?>