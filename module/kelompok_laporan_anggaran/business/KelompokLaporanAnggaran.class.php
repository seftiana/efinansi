<?php

/**
 *
 * class KelompokLaporanAnggaran
 * @package kelompok_laporan_anggaran
 * @subpackage business
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since 6 september 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
 
class KelompokLaporanAnggaran extends Database 
{

	protected $mSqlFile= 'module/kelompok_laporan_anggaran/business/kelompoklaporananggaran.sql.php';

	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
	}

	public function DoAdd($nama,$jenisLaporanId,$isTambah,$noUrutan,$arrPaguBasMak) 
	{
		
		$this->StartTrans();
		
		$result = $this->Execute($this->mSqlQueries['do_add'], 
										array(
												$nama,
												$jenisLaporanId,
												$isTambah,
												$noUrutan
											  ));
		
		$kelompokLaporanId = $this->LastInsertId();
		if($result){
			if(!empty($arrPaguBasMak) && is_array($arrPaguBasMak)){
				foreach($arrPaguBasMak as $key => $value){
					$result = $this->Execute($this->mSqlQueries['do_add_pagu_bas_mak'], 
										array(
												$kelompokLaporanId,
												$value['mak_id']
											  ));
				}
			}
		}
		
		$this->EndTrans($result);	 
		return $result;
	}

	public function DoUpdate($nama,$jenisLaporanId,$isTambah,$noUrutan, $id,$arrPaguBasMak) 
	{
		$this->StartTrans();
		
		$result = $this->Execute($this->mSqlQueries['do_update'], 
										array(
												$nama, 
												$jenisLaporanId, 
												$isTambah,
												$noUrutan, 
												$id
											));
											
        if($result){
			$result = $this->Execute($this->mSqlQueries['do_delete_pagu_bas_mak'], array($id));
			
			if(!empty($arrPaguBasMak) && is_array($arrPaguBasMak)){
				foreach($arrPaguBasMak as $key => $value){
					$result = $this->Execute($this->mSqlQueries['do_add_pagu_bas_mak'], 
										array(
												$id,
												$value['mak_id']
											  ));
				}
			}			
		}
		$this->EndTrans($result);	 
		return $result;
	}	
	
	public function DoDelete($arrId) 
	{
		//$this->setDebugOn();
		if(!is_array($arrId)){
			$makeArrId[] = $arrId;
		} else{
			$makeArrId = $arrId;
		}
		
		if(is_array($makeArrId)){
			$strId = implode("','", $makeArrId);
			$result=$this->Execute($this->mSqlQueries['do_delete'], array($strId));
		}		
		
		return $result;
	}
	
	public function GetError() 
	{
		$errno = mysql_errno();
		if($errno == "1451") {
			$return = "Terdapat data lain yang menggunakan data ini.";
		}
		return $return;
	}
	
	
	public function GetData($offset, $limit, $nama='',$jenisLaporan = '') 
	{
		//$this->setDebugOn();
		if($jenisLaporan == 'all' or empty($jenisLaporan)){
			$flagNumber = 1;
		} else {
			$flagNumber = 0;
		}
		
		$query = sprintf($this->mSqlQueries['get_data'], 
						'%'.$nama.'%',
						$jenisLaporan, 
						$jenisLaporan, 
						$flagNumber, 
						$offset, 
						$limit);
						
		$result = $this->Open($query, array());
		return $result;
	}

	public function GetCountData($nama='',$jenisLaporan = '') 
	{
		//$this->setDebugOn();
		if($jenisLaporan == 'all' or empty($jenisLaporan)){
			$flagNumber = 1;
		} else {
			$flagNumber = 0;
		}
		$query = sprintf($this->mSqlQueries['get_count'], 
						'%'.$nama.'%',
						$jenisLaporan, 
						$jenisLaporan, 
						$flagNumber);
		
		$result = $this->Open($query, array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

	public function GetDataById($id) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));
		return $result;
	}
	
	public function GetDataDetil($id) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_detil'], array($id));
		return $result[0];
	}
	

	
	/**
	 * data pagu bas mak
	 */
	 
	public function GetCountPaguBasMak($idKelompokLaporan) 
	{
		$result = $this->Open($this->mSqlQueries['get_count_pagu_bas_mak'], array($idKelompokLaporan));
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}

	public function GetDataPaguBasMak($idKelompokLaporan) 
	{
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['get_data_pagu_bas_mak'], array($idKelompokLaporan));
		return $result;
	}

	// do add detil coa kel lap
	public function DoAddDetilData($id_kel_lap, $id_coa, $coa_type) 
	{
      $result = $this->Execute(
         $this->mSqlQueries['do_add_detil_coa_kel_lap'], 
         array(
            $id_kel_lap, 
            $id_coa, 
            $coa_type
         )
      );
      return $result;
	}

	public function DoDeleteDetilDataById($id) 
	{
      $result=$this->Execute($this->mSqlQueries['do_delete_detil_by_id'], array($id));
      return $result;
	}

	public function DoDeleteDetilDataByArrayId($arrId) 
	{
      $id_coa_klp_lap = implode("', '", $arrId);
      $result=$this->Execute(
         $this->mSqlQueries['do_delete_detil_by_array_id'], 
         array(
            $id_coa_klp_lap
         )
      );
      return $result;
	}

/**
 * untuk mendapatkan no urut
 * @since 11 April 2012
 */
	public function GenerateNoUrutan($jenisLaporanId)
	{
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['generate_no_urutan'], 
												array(
													$jenisLaporanId,
													$jenisLaporanId
													));
		return $result[0]['no_urutan'];
	}
 
}
