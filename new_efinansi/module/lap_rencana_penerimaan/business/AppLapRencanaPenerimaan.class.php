<?php

/**
 * @package lap_rencana_penerimaan
 * @since 24 Februari 2012
 * @copyright (c) 2012 Gamatechno
 */

class AppLapRencanaPenerimaan extends Database
{

	protected $mSqlFile;
	public $_POST;
	public $_GET;

	public function __construct($connectionNumber=0)
	{
		$this->_POST 		= is_object($_POST) ? $_POST->AsArray() : $_POST;
		$this->_GET 		= is_object($_GET) ? $_GET->AsArray() : $_GET;
		$this->mSqlFile 	= 'module/lap_rencana_penerimaan/business/app_lap_rencana_penerimaan.sql.php';
		parent::__construct($connectionNumber);
	}

	public function GetCountData($tahunAnggaran, $unitkerja, $statusApprove)
	{
		$data = $this->Open($this->mSqlQueries['get_count_data'],array(
													$tahunAnggaran,
													$statusApprove,
													$unitkerja,
													$unitkerja,
													$unitkerja));
		if (!$data) {
			return 0;
		} else {
			return $data[0]['total'];
		}
	}

	//yg dipake ini----
	public function GetDataRencanaPenerimaan($tahunAnggaran, $unitkerja, $statusApprove, $startRec=0,$itemView=0)
	{
		if($itemView > 0){
			$sql = ' LIMIT '.$startRec.','.$itemView;
		} else {
			$sql = '';
		}

		$query = sprintf($this->mSqlQueries['get_data_rencana_penerimaan'],
													$tahunAnggaran,
													$statusApprove,
													$unitkerja,
													$unitkerja,
													$unitkerja,
													$sql);

      	$result = $this->Open($query,array());
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

	public function GetDataForTotal($tahunAnggaran, $statusApprove, $unitkerja)
	{
		//$this->SetDebugOn();
      $result = $this->Open($this->mSqlQueries['get_data_for_total'], array($tahunAnggaran,$tahunAnggaran,$statusApprove,$statusApprove, $unitkerja, $unitkerja));
			//echo $this->getLastError();
		return $result;
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

    public function GetTotalRencanaPenerimaanPerBulan($tahunAnggaran,$unitkerja,$statusApprove)
    {
        $result = $this->Open($this->mSqlQueries['get_total_rencana_penerimaan_perbulan'],array(
        											$tahunAnggaran,
													$statusApprove,
													$unitkerja,
													$unitkerja,
													$unitkerja
        										));
        return $result[0];
    }
}