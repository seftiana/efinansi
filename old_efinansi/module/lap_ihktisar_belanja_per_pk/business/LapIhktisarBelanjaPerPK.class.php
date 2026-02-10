<?php

/**
 * 
 * class LapIhktisarBelanjaPerPK
 * @package lap_Ihktisar_belanja_per_pk
 * @todo Untuk olah data
 * @subpackage business
 * @since 12 September 2012
 * @SystemAnalyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class LapIhktisarBelanjaPerPK extends Database
{
	 protected $mSqlFile= 'module/lap_ihktisar_belanja_per_pk/business/lapihktisarbelanjaperpk.sql.php';
	 
	 public function __construct($connectionNumber=0) 
	 {
		 parent::__construct($connectionNumber);
	 }
	 
	 public function GetDataLaporan($tahunAnggaran,$unitKerjaId,$startRec=0,$itemViewed =0)
	 {
		
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
		
		 $result = $this->open($this->mSqlQueries['get_count_data_laporan'],array());
		 return $result[0]['total'];
	 }
	 	 
	 public function GetPaguBasMak($jenisLaporan = 2)
	 {
		 //$this->setDebugOn();
		 $result = $this->open($this->mSqlQueries['get_mak'],array($jenisLaporan));
		 return $result;
	 }
	
	 public function GetPaguBasHeader($tahunAnggaran,$unitKerjaId)
	 {
		 $result = $this->open($this->mSqlQueries['get_pagu_bas_header'],
											array(
												$tahunAnggaran,
												$unitKerjaId,'%',
												$unitKerjaId								
												));
		 return $result;
	 }
	
	 public function GetNominalPengeluaran($tahunAnggaran,$unitKerjaId)
	 {
		 $result = $this->open($this->mSqlQueries['get_nominal_pengeluaran'],
								array(
								$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['sub_keg_id']][$value['pb_id']]=$value['nominal'];
		}
		return $data;
	 }
	 
	 public function GetNominalPengeluaranPerK($tahunAnggaran,$unitKerjaId)
	 {
		 
		 $result = $this->open($this->mSqlQueries['get_nominal_pengeluaran_per_k'],
								array(
								$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['k_id']][$value['pb_id']]=$value['nominal'];
		}
		return $data;
	 }
	 
	 public function GetNominalPengeluaranPerP($tahunAnggaran,$unitKerjaId)
	 {
		 
		 $result = $this->open($this->mSqlQueries['get_nominal_pengeluaran_per_p'],
								array(
								$tahunAnggaran,
								$unitKerjaId,'%',
								$unitKerjaId								
								));
		foreach($result as $key => $value){
				$data[$value['p_id']][$value['pb_id']]=$value['nominal'];
		}
		return $data;
	 }
	
	 public function GetData($idParent = 0)
	 {
		 $result = $this->open($this->mSqlQueries['get_data'],array($idParent));
         return $result;
	 }	 
	 	 
	 public function GetCountData($idParent = 0)
	 {
		 $result = $this->open($this->mSqlQueries['get_count_data'],array($idParent));
         return $result[0]['total'];
	 }	 

   /**
    * generate data 
    * membuat pengelompokan data
    * @return array()
    */
   	public function GenerateDataLaporan($id = 0,$nomor = '',$padding ='')
   	{
		$getData = $this->GetData($id);
			
			for($i = 0 ;$i < sizeof($getData);$i++){
				if($nomor == '') {
					$no = $i + 1;
				} else {
					$no ='';
					$no =$nomor.'.'.($i+1);
				}
				
				if($padding ==''){
					$pad = $i+1;
				} else {
					$pad =0;
					$pad = $padding + 15;
				}			
				if($getData[$i]['kl_kode']== ''){
					$kode = $no;
				} else {
					$kode = $getData[$i]['kl_kode'];
				}
				$data[] = array(
							'kl_nomor' => $no,
							'kl_id' =>$getData[$i]['kl_id'],
							'kl_nama'=>$getData[$i]['kl_nama'],
							'sts_mak' =>$getData[$i]['sts_mak'],
							'kl_nominal' =>$getData[$i]['nominal'],
							'kl_parent_id' =>$getData[$i]['kl_parent_id'],
							'kl_kode' => $kode,
							'padding'=>$pad
							);
			if($getData[$i]['kl_id'] != ''){
				$count = $this->GetCountData($getData[$i]['kl_id']);
				if($count > 0){
					$data = array_merge($data, $this->GenerateDataLaporan($getData[$i]['kl_id'],$no,$pad));
				} 	
			}
		
		}
		return $data;
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
