<?php

/**
 * 
 * class LapPendapatanBelanjaAgregat
 * @package lap_pendapatan_belanja_agregat
 * @todo Untuk olah data
 * @subpackage business
 * @since 10 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class LapPendapatanBelanjaAgregat extends Database
{
	 protected $mSqlFile= 'module/lap_pendapatan_belanja_agregat/business/lappendapatanbelanjaagregat.sql.php';
	 
	 public function __construct($connectionNumber=0) 
	 {
		 parent::__construct($connectionNumber);
	 }
	 
	 /**
	  * class GetData
	  * @todo untuk mendapatkan data laporan
	  * @param number tahunAnggaran , id tahun anggaran
	  * @param number unitKerjaId , unit kerja id
	  * @access protected
	  * @return arary()
	  */
	 protected function GetData($tahunAnggaran,$unitKerjaId)
	 {

		 $result = $this->open($this->mSqlQueries['get_data_laporan'],
										array(
											$tahunAnggaran,
											$unitKerjaId,
											$unitKerjaId,
											'%'
											));
         return $result;
	 }
	 
	 public function GetDataPendapatan($tahunAnggaran,$unitKerjaId)
	 {
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 
		 $result = $this->open($this->mSqlQueries['get_data_laporan_p'],
										array(
											$taIdSebelum,
											$tahunAnggaran,
											$taIdSebelum,
											$tahunAnggaran,
											$unitKerjaId,'%',
											$unitKerjaId
											));
         return $result;
	 }
	 
	 public function GetDataBelanja($tahunAnggaran,$unitKerjaId)
	 {		
		 $result = $this->open($this->mSqlQueries['get_data_laporan_b'],
										array(
											$taIdSebelum,
											$tahunAnggaran,
											$taIdSebelum,
											$tahunAnggaran,
											$unitKerjaId,'%',
											$unitKerjaId
											));
         return $result;
	 }
	 
	 
	 public function GetTotalLaporanBPerSd($tahunAnggaran,$unitKerjaId)
	 {		
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 		 
		 $result = $this->open($this->mSqlQueries['get_total_laporan_b_per_sd'],
										array(
											$taIdSebelum,
											$tahunAnggaran,
											$taIdSebelum,
											$tahunAnggaran,
											$unitKerjaId,'%',
											$unitKerjaId
											));
		foreach($result as $key => $value){
				$data[$value['mak_parent_id']]['realisasi_sebelum']=$value['realisasi_sebelum'];
				$data[$value['mak_parent_id']]['nominal_sekarang']=$value['nominal_sekarang'];
		}
		
		return $data;
	 }
	 
	 
	 public function GetTotalLaporanPPerMap($tahunAnggaran,$unitKerjaId)
	 {		
		 //$this->setDebugOn();
		 $tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		 $taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 		 
		 $result = $this->open($this->mSqlQueries['get_data_laporan_p_per_map'],
										array(
											$taIdSebelum,
											$tahunAnggaran,
											$taIdSebelum,
											$tahunAnggaran,
											$unitKerjaId,'%',
											$unitKerjaId
											));
		foreach($result as $key => $value){
				$data[$value['map_id']]['realisasi_sebelum']=$value['realisasi_sebelum'];
				$data[$value['map_id']]['nominal_sekarang']=$value['nominal_sekarang'];
		}
		
		return $data;
	 }
	 
	 public function GetCountData($idParent = 0)
	 {
		 //$this->setDebugOn();
		 $result = $this->open($this->mSqlQueries['get_count_data'],array($idParent));
         return $result[0]['total'];
	 }
	  
		 
	 public function GetNominalMak($idParent = 0)
	 {
		 $result = $this->open($this->mSqlQueries['get_nominal_mak'],array($idParent,$idParent,'%'));
         return $result[0]['kl_nominal'];
	 }
	 

	 
	 /**
	  * function GetNilaiProyeksi
	  * untuk menhitung nilai proyeksi pada tahun kedepan berdasarkan nilai di tabel setting
	  * @param Number $nilai
	  * @access public
	  * @return Number
	  */
	 public function GetNilaiProyeksi($nilai=0)
	 {
		$data = $this->Open($this->mSqlQueries['get_nilai_proyeksi'], array());
		if(!$data){
			$nilai_proyeksi = $nilai;
		} else {
			$nilai_proyeksi = $nilai + ($nilai * ($data[0]['nilai']/100));
		}
		return $nilai_proyeksi;
	 }
	 

   
    /**
     * fungsi GetPersenNT
     * hitung prosentase nilai kenaikan penurunan
     * @param Number $nilaiSekarang
     * @param Number $nilaiSebelum
     * @return Number 
     * @access Public
     */
   public function GetPersenNT($nilaiSekarang,$nilaiSebelum)
   {
	   $total = 0;
	   if($nilaiSebelum != 0){
			$total = ((($nilaiSekarang - $nilaiSebelum) / $nilaiSebelum) * 100);
	   }
	   return $total;
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
   public function SetFormatAngka($angka,$des=0)
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