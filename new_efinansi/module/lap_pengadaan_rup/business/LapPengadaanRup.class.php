<?php

/**
 * 
 * class LapPengadaanRup
 * @package lap_pengadaan_rup
 * @todo Untuk olah data
 * @subpackage business
 * @since 29 April 2013
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 */
 
class LapPengadaanRup extends Database
{
	 protected $mSqlFile= 'module/lap_pengadaan_rup/business/lappengadaanrup.sql.php';
	 
	 public function __construct($connectionNumber=0) 
	 {
		 parent::__construct($connectionNumber);
	 }

	 public function GetDataLaporan($tahunAnggaran,$unitKerjaId,$startRec=0,$itemViewed =0)
	 {
		//$this->setDebugOn();
		 if($itemViewed > 0){
			$sql_limit = "LIMIT ".$startRec.",".$itemViewed;
		  } else {
			$sql_limit = '';
		  }
		  
		  $sql = sprintf($this->mSqlQueries['get_data_laporan'],
								$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId,
								$sql_limit);
		
		 $result = $this->open($sql,array());
		 return $result;
	 }
	 
	 public function GetCountDataLaporan()
	 {
		//$this->setDebugOn();
		 $result = $this->open($this->mSqlQueries['get_count_data_laporan'],array());
		 return $result[0]['total'];
	 }
	 
	 public function GetTotalPerSk($tahunAnggaran,$unitKerjaId)
	 {
		 $result = $this->open($this->mSqlQueries['get_total_per_sk'],
								array(
								$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['komponen_id']]=$value['nominal'];
		}
		return $data;
	 }	 
	 public function GetTotalPerK($tahunAnggaran,$unitKerjaId)
	 {		 
		 $result = $this->open($this->mSqlQueries['get_total_per_k'],
								array(								
								$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['output_id']]=$value['nominal'];
		}
		return $data;
	 }	 
	 
	 public function GetTotalAll($tahunAnggaran,$unitKerjaId)
	 {
		 //$this->setDebugOn();	
		 $result = $this->open($this->mSqlQueries['get_total_all'],
								array(
								$tahunAnggaran,								
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		return $result[0]['nominal'];
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
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array($id));
		return $result[0];
	}
	

   /**
    * fungsi GetFormatAngka
    * untuk memformat angka
    * @param number $angka
    * @return String
    * @acces Public
    */
   public function SetFormatAngka($angka,$digit=0)
   {
	   $angka = (float) $angka;
	    $str_angka ='';
		if($angka < 0){
			 $str_angka= '('.number_format(($angka * (-1)), $digit, ',', '.').')';
		 } else{
			$str_angka = number_format($angka,$digit , ',', '.');	
		 }
		 return $str_angka;
   }		
}

?>