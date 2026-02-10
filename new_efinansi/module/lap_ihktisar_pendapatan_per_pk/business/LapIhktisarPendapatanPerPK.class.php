<?php

/**
 * 
 * class LapIhktisarPendapatanPerPK
 * @package lap_Ihktisar_pendapatan_per_pk
 * @todo Untuk olah data
 * @subpackage business
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class LapIhktisarPendapatanPerPK extends Database
{
	 protected $mSqlFile= 'module/lap_ihktisar_pendapatan_per_pk/business/lapihktisarpendapatanperpk.sql.php';
	 
	 public function __construct($connectionNumber=0) 
	 {
		 parent::__construct($connectionNumber);
	 }
	 
	 public function GetDataLaporan($tahunAnggaran,$unitKerjaId)
	 {
		
		 $result = $this->open($this->mSqlQueries['get_data_laporan'],
							array(
								$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId
							));
		 return $result;
	 }
	 
	public function GetPaguBasMak($jenisLaporan = 2)
	{
		 //$this->setDebugOn();
		 $result = $this->open($this->mSqlQueries['get_mak'],array($jenisLaporan));
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
