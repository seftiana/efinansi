<?php

/**
 * class AppLapRincianPendapatanPerUnit
 * @package lap_rincian_pendapatan_per_unit
 * @subpackage response
 * @todo untuk manipulasi data laporan rincian pendapatan per unit
 * @since juli 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 
class AppLapRincianPendapatanPerUnit extends Database 
{

	protected $mSqlFile= 'module/lap_rincian_pendapatan_per_unit/business/app_lap_rincian_pendapatan_per_unit.sql.php';

	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
	}

	/**
	 * fungsi GetPersen
	 * untuk menghitung nilai persentase
	 * @param Number $target
	 * @param Number @realisasi
	 * @return Mix
	 * @access public
	 */
	 public function GetPersen($target = 0,$realisasi = 0,$format = false)
	 {
		 if($realisasi == 0){
			return 0;
		 } else {
			 $hasil =  (($realisasi / $target) * 100); 
			 
			 if($format == false ){
				 return $hasil;
			 } else {
				if(strpos($hasil,'.')){
					return number_format($hasil ,2,',','.');
				} else {
					return $hasil;
				}
			 }
		 }
	 }
	/**
	 * fungsi GetNilaiProyeksi
	 * untuk mendapatkan nilai proyeksi tahun selanjutnya/depan
	 * @param Number $number
	 * @return Number
	 * @access public
	 */
	public function GetNilaiProyeksi($number=0)
	{
		
		$data = $this->Open($this->mSqlQueries['get_nilai_proyeksi'], array());
		if(!$data){
			$nilai_proyeksi = $number ;
		} else {
			$nilai_proyeksi = $number + ($number * ($data[0]['nilai']/100));
		}
		return $nilai_proyeksi;
	}
	
	public function GetCountData($tahunAnggaran, $unitkerja='') 
	{
		//$this->setDebugOn();
		$data = $this->Open($this->mSqlQueries['get_count_data'], 
	  						array(
									$tahunAnggaran,
									$tahunAnggaran, 
									$unitkerja,'.%', 
									$unitkerja));
		
		if (!$data) {
			return 0;
		} else {
			return $data[0]['total'];
		}
	}

	public function GetDataPage($tahunAnggaran,$unitkerja,$startRec,$itemViewed) 
	{
		//$this->SetDebugOn();
		$params = ' LIMIT '.$startRec.','.$itemViewed;
		return $this->GetData($tahunAnggaran,$unitkerja,$params);
	}
	public function GetData($tahunAnggaran,$unitKerjaId,$params='') 
	{
		//$this->SetDebugOn();
		$tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		$taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		$sql = sprintf($this->mSqlQueries['get_data_rincian_pendapatan_per_unit'],
									$taIdSebelum,
									$taIdSebelum,
									$tahunAnggaran, 
									$taIdSebelum,
									$tahunAnggaran, 
									$unitKerjaId,'%', 
									$unitKerjaId,$params);
		$result = $this->Open($sql, array());
		return $result;
	}
	
	public function GetTotalRincianPendapatanPerSD($tahunAnggaran,$unitKerjaId) 
	{//$this->setDebugOn();
		$tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		$taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		$result = $this->open($this->mSqlQueries['get_total_rincian_pendapatan_per_sd'],
								array(
								$taIdSebelum,
								$taIdSebelum,
								$tahunAnggaran, 
								$taIdSebelum,
								$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['sd_id']]['target_sebelum']=$value['target_sebelum'];
				$data[$value['sd_id']]['realisasi_sebelum']=$value['realisasi_sebelum'];
				$data[$value['sd_id']]['target_sekarang']=$value['target_sekarang'];
		}
		return $data;		
	}
	
	public function GetTotalRincianPendapatanAll($tahunAnggaran,$unitKerjaId) 
	{
		//$this->setDebugOn();
		$tahunAnggaranSebelum = $this->GetTahunAnggaranKemarin($tahunAnggaran);
		$taIdSebelum = (empty($tahunAnggaranSebelum['id']) ? $tahunAnggaran : $tahunAnggaranSebelum['id']);
		 $result = $this->open($this->mSqlQueries['get_total_rincian_pendapatan_all'],
								array(
								$taIdSebelum,
								$taIdSebelum,
								$tahunAnggaran, 
								$taIdSebelum,
								$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		return $result[0];		
	}

	public function GetTotalPendapatanPeneriman($tahunAnggaran, $unitkerja) 
	{
		$ret = $this->Open($this->mSqlQueries['get_total_pendapatan_penerimaan'], 
										array(
												$tahunAnggaran,
												$tahunAnggaran,
												$unitkerja,'.%',
												$unitkerja));
		return $ret[0];
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
