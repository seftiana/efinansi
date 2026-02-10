<?php

/**
 * 
 * class LapRincianBelanjaPerUnit
 * @package lap_rincian_belanja_per_unit
 * @todo Untuk olah data
 * @subpackage business
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class LapRincianBelanjaPerUnit extends Database
{
	 protected $mSqlFile= 'module/lap_rincian_belanja_per_unit/business/laprincianbelanjaperunit.sql.php';
	 
	 public function __construct($connectionNumber=0) 
	 {
		 parent::__construct($connectionNumber);
	 }

	 public function GetDataLaporan($tahunAnggaran,$unitKerjaId,$startRec=0,$itemViewed =0)
	 {
		//$this->setDebugOn();
		$tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		$taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 if($itemViewed > 0){
			$sql_limit = "LIMIT ".$startRec.",".$itemViewed;
		  } else {
			$sql_limit = '';
		  }
		  
		  $sql = sprintf($this->mSqlQueries['get_data_laporan'],
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
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
	 
	 public function GetTotalPerMak($tahunAnggaran,$unitKerjaId)
	 {
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 $result = $this->open($this->mSqlQueries['get_total_per_mak'],
								array(
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['mak_id']]['sebelum']=$value['nominal_sebelum'];
				$data[$value['mak_id']]['sekarang']=$value['nominal_sekarang'];
		}
		return $data;
	 }	 
	 
	 public function GetTotalPerPagu($tahunAnggaran,$unitKerjaId)
	 {
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 $result = $this->open($this->mSqlQueries['get_total_per_pagu'],
								array(
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['mak_parent_id']]['sebelum']=$value['nominal_sebelum'];
				$data[$value['mak_parent_id']]['sekarang']=$value['nominal_sekarang'];
		}
		return $data;
	 }	 
	 
	 public function GetTotalPerSk($tahunAnggaran,$unitKerjaId)
	 {
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 $result = $this->open($this->mSqlQueries['get_total_per_sk'],
								array(
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['sk_id']]['sebelum']=$value['nominal_sebelum'];
				$data[$value['sk_id']]['sekarang']=$value['nominal_sekarang'];
		}
		return $data;
	 }	 
	 public function GetTotalPerK($tahunAnggaran,$unitKerjaId)
	 {
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 $result = $this->open($this->mSqlQueries['get_total_per_k'],
								array(
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['k_id']]['sebelum']=$value['nominal_sebelum'];
				$data[$value['k_id']]['sekarang']=$value['nominal_sekarang'];
		}
		return $data;
	 }	 
	 public function GetTotalPerP($tahunAnggaran,$unitKerjaId)
	 {
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 $result = $this->open($this->mSqlQueries['get_total_per_p'],
								array(
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['p_id']]['sebelum']=$value['nominal_sebelum'];
				$data[$value['p_id']]['sekarang']=$value['nominal_sekarang'];
		}
		return $data;
	 }	 
	 public function GetTotalPerU($tahunAnggaran,$unitKerjaId)
	 {
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 $result = $this->open($this->mSqlQueries['get_total_per_u'],
								array(
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['u_id']]['sebelum']=$value['nominal_sebelum'];
				$data[$value['u_id']]['sekarang']=$value['nominal_sekarang'];
		}
		return $data;
	 }	 
	 
	 public function GetTotalAll($tahunAnggaran,$unitKerjaId)
	 {
		 //$this->setDebugOn();
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 $result = $this->open($this->mSqlQueries['get_total_all'],
								array(
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		return $result[0];
	 }	 
	 
	 public function GetSumberDana($tahunAnggaran,$unitKerjaId)
	 {
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 $result = $this->open($this->mSqlQueries['get_sumber_dana'],
								array(
								$taIdSebelum ,$tahunAnggaran,
								$taIdSebelum ,$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
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
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array($id));
		return $result[0];
	}
	
	public function GetTahunAnggaranKemarin($id) 
	{
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_kemarin'], array($id));
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