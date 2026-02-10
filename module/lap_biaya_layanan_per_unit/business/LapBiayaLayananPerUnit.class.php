<?php

/**
 * 
 * class LapBiayaLayananPerUnit
 * @package lap_biaya_layanan_per_unit
 * @todo Untuk olah data
 * @subpackage business
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class LapBiayaLayananPerUnit extends Database
{
	 protected $mSqlFile= 'module/lap_biaya_layanan_per_unit/business/lapbiayalayananperunit.sql.php';
	 
	 public function __construct($connectionNumber=0) 
	 {
		 parent::__construct($connectionNumber);
	 }

	 public function GetData($tahunAnggaranId,$unitKerjaId,$startRec=0,$itemViewed=0)
	 {
		// $this->setDebugOn();
		 if($itemViewed > 0){
			$sql_limit = "LIMIT ".$startRec.",".$itemViewed;
		  } else {
			$sql_limit = '';
		  }
		  $sql = sprintf($this->mSqlQueries['get_data'],
													$tahunAnggaranId,
													$unitKerjaId,'%',
													$unitKerjaId,
													$sql_limit);
		 $result = $this->open($sql,array());
         return $result;
	 }	 
	 
	 public function GetTotalPerMak($tahunAnggaranId,$makId,$unitKerjaId)
	 {
		 
		 $result = $this->open($this->mSqlQueries['get_total_per_mak'],array(
													$tahunAnggaranId,
													$makId,
													$unitKerjaId
												));
         
         return $result[0]['total'];
	 }	 
	 
	 public function GetTotalPerBiaya($tahunAnggaranId,$biayaId,$unitKerjaId)
	 {
		 
		 $result = $this->open($this->mSqlQueries['get_total_per_biaya'],array(
													$tahunAnggaranId,
													$biayaId,
													$unitKerjaId
												));
         
         return $result[0]['total'];
	 }	 
	 
	 public function GetTotalPerUnit($tahunAnggaranId,$unitKerjaId)
	 {
		 
		 $result = $this->open($this->mSqlQueries['get_total_per_unit'],array(
													$tahunAnggaranId,
													$unitKerjaId
												));
         
         return $result[0]['total'];
	 }	 
	 	 
	 public function GetCountData()
	 {
		 $result = $this->open($this->mSqlQueries['get_count_data'],array());
         return $result[0]['total'];
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
    * fungsi GetFormatAngka
    * untuk memformat angka
    * @param number $angka
    * @return String
    * @acces Public
    */
   public function SetFormatAngka($angka,$des = 0)
   {
	   $angka = (float) $angka;
	    $str_angka ='';
		if($angka < 0){
			 $str_angka= '('.number_format(($angka * (-1)), $des, ',', '.').')';
		 } else{
			$str_angka = number_format($angka,$des, ',', '.');	
		 }
		 return $str_angka;
   }		
}
?>