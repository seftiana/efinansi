<?php

/**
 * 
 * @package realisasi_pencairan_2
 * @sub_package business
 * @class SppBerdasarkanNoPengajuan
 * @description untuk menangani query pembuatan cetak spp berdasarkan pengajuan
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @copyright 2013 gamatechno indonesia
 *  
 */

class SppBerdasarkanNoPengajuan extends Database
{
	protected $mSqlFile  = 'module/realisasi_pencairan_2/business/spp_berdasarkan_no_pengajuan.sql.php';
   
	public function __construct($connectionNumber = 0)
	{
		parent::__construct($connectionNumber);
	}
   
	public function GetGenerateNomorSppNoPengajuan()
	{		
		$return  = $this->Open($this->mSqlQueries['get_generate_nomor_spp_no_pengajuan'], array());
		return $return[0]['nomor'];
	}
	public function GetDataProgram($tahunAnggaran)
	{
		
		$return  = $this->Open($this->mSqlQueries['get_data_program'], array($tahunAnggaran));
      
		return $return;
	}
   
	//COMBO TA
	public function GetTahunAnggaran()
	{
		$return  = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array());
      
		return $return;
	}	
	
	public function GetTahunAnggaranAktif()
	{
		$return  = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
		return $return[0]['id'];
	}


	public function GetMinTahun()
	{		
		$tgl     = date("Y");
		return $tgl - 5;		
	}

	public function GetMaxTahun()
	{
		$tgl     = date("Y");
		return $tgl + 5;		
	}
      
	public function GetData($tahunAnggaranId,$unitKerjaId,$programId,$noSppt,$offset,$limit)
	{
		//$this->SetDebugOn();
		if(empty($programId) || ($programId =='all')){
			$flag = 1;
		} else {
			$flag = 0;
		}
		$return = $this->Open($this->mSqlQueries['get_data'], 
												array(
														$tahunAnggaranId,
														$unitKerjaId,'%',
														$unitKerjaId,
														$programId,$flag,
														'%'.$noSppt.'%',
														$offset,
														$limit
													));
		return $return;
	}
   
	public function GetDataById($id)
	{
		$return = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));
		return $return[0];
	}
   
	public function GetCountData()
	{
		$return     = $this->Open($this->mSqlQueries['get_count_data'], array());
		return $return[0]['total'];
	}   
	
	/**
	 * @function GetDataNoPengajuan
	 * @param $id id pada tabel finansi_pa_spp_pengajuan_real
	 * @access public
	 * @return array
	 */ 
	public function GetDataNoPengajuan($id)
	{   //$this->SetDebugOn();
		$return     = $this->Open($this->mSqlQueries['get_no_pengajuan'], array($id));
		return $return;
	}
	
	/**
	 * @function GetDataNoPengajuan
	 * @param $id id pada tabel finansi_pa_spp_pengajuan_real
	 * @access public
	 * @return array
	 */ 
	public function GetDataNoPengajuanGroup($id)
	{   //$this->SetDebugOn();
		$return     = $this->Open($this->mSqlQueries['get_no_pengajuan_group'], array($id));
		return $return;
	}
	
	/**
	 * @function GetDataIndex
	 * @param $id id pada tabel finansi_pa_spp_pengajuan_real
	 * @access public
	 * @return array
	 */
	public function GetDataIndex($id)
	{   //$this->SetDebugOn();
		$return     = $this->Open($this->mSqlQueries['get_data_index'], array($id));
		return $return;
	}
	
	/**
	 * @function GetDataIndexDetail
	 * @param $id id pada tabel finansi_pa_spp_pengajuan_real
	 * @access public
	 * @return array
	 */
	public function GetDataIndexDetail($id)
	{   //$this->SetDebugOn();
		$return     = $this->Open($this->mSqlQueries['get_data_index_detail'], array($id));
		return $return;
	}
	
	public function Add($dataSpp)
	{
		//$this->SetDebugOn();
		$this->StartTrans();
		
		$result = $this->Execute($this->mSqlQueries['add_sppt'], 
														array(
															$dataSpp['tahun_anggaran_id'],
															$dataSpp['program_id'],
															$dataSpp['unit_kerja_id'],
															$dataSpp['nomor_spp_no_pengajuan'],
															$dataSpp['tanggal'],
															$dataSpp['jumlah_total'],
															$dataSpp['keterangan']
															));
													
		$sppPengRealId = $this->LastInsertId();
		if($result && (!empty($dataSpp['pengajuan']))){
			foreach($dataSpp['pengajuan'] as $key => $value){
				$result = $this->Execute($this->mSqlQueries['add_sppt_detail'], 
														array(
																$sppPengRealId,
																$value['pengajuan_id'],
																$value['pengajuan_detail_id'],
																$value['nominal'],
																$value['catatan']
															));
				if($result)	{
					continue;
				} else {
					break;
				}
				
			}
		}
		
		$this->EndTrans($result);
		return $result;
	}
	
	public function Update($dataSpp)
	{
		//$this->SetDebugOn();
		$this->StartTrans();
		
		$result = $this->Execute($this->mSqlQueries['update_sppt'], 
														array(
															$dataSpp['tahun_anggaran_id'],
															$dataSpp['program_id'],
															$dataSpp['unit_kerja_id'],
															$dataSpp['nomor_spp_no_pengajuan'],
															$dataSpp['tanggal'],
															$dataSpp['keterangan'],
															$dataSpp['jumlah_total'],
															$dataSpp['spp_no_pengajuan_id']
															));	
		
			
		$countPengajuan = $this->Open($this->mSqlQueries['get_count_spp_no_pengajuan_detail'], array($dataSpp['spp_no_pengajuan_id']));
		if($countPengajuan[0]['total'] > 0){
			if($result){
				$result = $this->Execute($this->mSqlQueries['delete_sppt_detail'], array($dataSpp['spp_no_pengajuan_id']));
			}
		}
		
		if($result && (!empty($dataSpp['pengajuan']))){	
			foreach($dataSpp['pengajuan'] as $key => $value){
				$result = $this->Execute($this->mSqlQueries['add_sppt_detail'], 
														array(
																$dataSpp['spp_no_pengajuan_id'],
																$value['pengajuan_id'],
																$value['pengajuan_detail_id'],
																$value['nominal'],
																$value['catatan']
															));
				if($result)	{
					continue;
				} else {
					break;
				}
				
			}
		}
		
		$this->EndTrans($result);
		return $result;
	}
	
	public function Delete($sppPengRealId)
	{
		$this->StartTrans();
		//$this->SetDebugOn();
		if(is_array($sppPengRealId)){
			$ids = implode("','",$sppPengRealId);
		} else {
			$ids = $sppPengRealId;
		}
		
		$result = $this->Execute($this->mSqlQueries['delete_sppt_detail'], array($sppPengRealId));
		
		if($result){
			$result = $this->Execute($this->mSqlQueries['delete_sppt'], array($sppPengRealId));
		}
		
		$this->EndTrans($result);
		return $result;
	}	

}

?>