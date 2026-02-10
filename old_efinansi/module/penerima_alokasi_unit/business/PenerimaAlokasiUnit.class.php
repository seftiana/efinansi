<?php

/**
 * class PenerimaAlokasiUnit
 * 
 * @package penerima_alokasi_unit
 * @subpackage business
 * 
 * @analyst Nanang Ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * 
 * @since 20 November 2012
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class PenerimaAlokasiUnit extends Database 
{

	protected $mSqlFile= 'module/penerima_alokasi_unit/business/penerima_alokasi_unit.sql.php';
	
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
		//$this->setDebugOn();
	}
	

	/**
	 * 
	 * GetData
	 * @description untuk mendapatkan data
	 * @param number  $offset nilai batas awal pagging
	 * @param number  $limit jumlah data yang ingin ditampilkan
	 * @param number  $tahunAnggaranId
	 * @param number  $unitKerjaId
	 * 
	 * @access public 
	 * @return bool
	 * 
	 */	
	public function GetData($offset, $limit,$unitKerjaSumberId,$kodePenerimaanId) 
    { 
		//$this->setDebugOn();
		if($kodePenerimaanId != ''){
			$flag = 0;
		} else {
			$flag = 1;			
		}
			
		$result  = $this->Open($this->mSqlQueries['get_data'],
										array(
										        $unitKerjaSumberId,'%',
										        $unitKerjaSumberId,
										        $kodePenerimaanId,$flag,
												$offset, 
												$limit
											));

		for($i = 0 ; $i < count($result); $i++){
			$result[$i]['besar_alokasi_sumber']=$this->FormatNumberPersen($result[$i]['besar_alokasi_sumber'],4);
			$result[$i]['alokasi_nilai']=$this->FormatNumberPersen($result[$i]['alokasi_nilai'],4);
		}		
		return $result;
	}

	/**
	 * 
	 * GetCountData
	 * @description untuk mendapatkan total data
	 * @param number  $offset nilai batas awal pagging
	 * @param number  $limit jumlah data yang ingin ditampilkan
	 * @param number  $tahunAnggaranId
	 * @param number  $unitKerjaId
	 * 
	 * @access public 
	 * @return bool
	 * 
	 */	
	public function GetCountData() 
    {
       
		$result = $this->Open($this->mSqlQueries['get_count_data'],array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
		
	}

	/**
	 * 
	 * GetDataById
	 * @description untuk mendapatkan data  per id
	 * @param number  $id
	 * 
	 * @access public 
	 * @return bool
	 * 
	 */	
	public function GetDataById($id) 
	{
		$result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));
		
			$result[0]['alokasi_unit_nilai']=$this->FormatNumberPersen($result[0]['alokasi_unit_nilai'],4);
			$result[0]['alokasi_pusat_nilai']=$this->FormatNumberPersen($result[0]['alokasi_pusat_nilai'],4);
		
		return $result[0];
	}


	public function GetUnitKerjaPenerima($alokasiId,$unitKerjaIndukId) 
    { 
		$result  = $this->Open($this->mSqlQueries['get_unit_kerja_penerima'],
												array(
													$alokasiId,
													$unitKerjaIndukId
													));
		return $result;
	}	
	
	
	public function GetCountKodePenerimaanAlokasi($alokasiId) 
    { 
		$result  = $this->Open($this->mSqlQueries['get_count_kode_penerimaan_alokasi'],
												array($alokasiId));
		return $result[0]['total'];
	}	
	
	
	/**
	 * 
	 * DoAdd
	 * @param number  $tahunAnggaranId
	 * @param number  $unitKerjaId
	 * @param number  $nilai
	 * 
	 * @access public 
	 * @return bool
	 * 
	 */	
	public function DoAdd($alokasiPenerimaanId,$alokasiUnitId,$alokasiPusatId,
											$dataUnit,$dataUnitPusat) 
	{
		//$this->setDebugOn();
		$this->StartTrans();
		$result = false;
		$flag_u = 1;
		$flag_p = 1;
		if(!empty($dataUnit) && is_array($dataUnit)){
				foreach( $dataUnit as $key => $value){
					$result = $this->Execute($this->mSqlQueries['do_add'], 
								array(
										$alokasiPenerimaanId,
										$alokasiUnitId,
										$value['unit_kerja_id'],
										$value['nilai']
									));
					if($result)	{
						$flag_u = 1;
					} else {
						$flag_u = 0;
					}
									
				}
		} 
		
		if(!empty($dataUnitPusat) && is_array($dataUnitPusat)){
				foreach( $dataUnitPusat as $key => $value){
					$result = $this->Execute($this->mSqlQueries['do_add'], 
								array(
										$alokasiPenerimaanId,
										$alokasiPusatId,
										$value['unit_kerja_id'],
										$value['nilai']
									));
					if($result)	{
						$flag_p = 1;
					} else {
						$flag_p = 0;
					}
									
				}
		}
		
		if(($flag_u * $flag_p) == 1){
			$result = true;
		} else {
			$result = false;
		}
		
		$this->EndTrans($result);
		return $result;
	}

	
	/**
	 * 
	 * DoUpdate
	 * @param number  $tahunAnggaranId
	 * @param number  $unitKerjaId
	 * @param number  $nilai
	 * @param number  $id
	 * 
	 * @access public 
	 * @return bool
	 * 
	 */	
	public function DoUpdate($alokasiPenerimaanId,$alokasiUnitId,$alokasiPusatId,
											$dataUnit,$dataUnitPusat,$dataId) 
	{
		//$this->setDebugOn();
		$this->StartTrans();
		$result =($dataId != '') ? true : false;
		$flag_u = 1;
		$flag_p = 1;
		
		if($result){
			
			$jmlAlokasiPenerima = $this->Open(
											$this->mSqlQueries['get_count_unit_kerja_penerima'],
											array($dataId)
										);

			if($jmlAlokasiPenerima[0]['total'] > 0){
					$result = 	$this->Execute(
											$this->mSqlQueries['do_delete'], 
											array($dataId)
											);
			}
				
		
		   if($result){
				if(!empty($dataUnit) && is_array($dataUnit)){
					foreach( $dataUnit as $key => $value){
						$result = $this->Execute($this->mSqlQueries['do_add'], 
								array(
										$alokasiPenerimaanId,
										$alokasiUnitId,
										$value['unit_kerja_id'],
										$value['nilai']
									));
						if($result)	{
							$flag_u = 1;
						} else {
							$flag_u = 0;
						}
									
					}
				}
		
				if(!empty($dataUnitPusat) && is_array($dataUnitPusat)){
					foreach( $dataUnitPusat as $key => $value){
						$result = $this->Execute($this->mSqlQueries['do_add'], 
								array(
										$alokasiPenerimaanId,
										$alokasiPusatId,
										$value['unit_kerja_id'],
										$value['nilai']
									));
						if($result)	{
							$flag_p = 1;
						} else {
							$flag_p = 0;
						}
									
					}
				} 
			}
		}
		
		if(($flag_u * $flag_p) == 1){
			$result = true;
		} else {
			$result = false;
		}
		
		$this->EndTrans($result);
		return $result;		
		
	}
	
	
	/**
	 * 
	 * DoDelete
	 * @param number  $id
	 * 
	 * @access public 
	 * @return bool
	 * 
	 */		
	public function DoDelete($alokasiPenerimaanId) 
	{ 
		//$this->setDebugOn();
		if($alokasiPenerimaanId != ''){
			$result = $this->Execute(
								$this->mSqlQueries['do_delete'], 
								array($alokasiPenerimaanId)
								);
		}	
		$this->EndTrans($result);
		return $result;
	}

		/**
	 * format number persen
	 * jika koma maka tampilkan dengan angka dibelakang koma
	 * jika tidak ada koma maka ditampilkan tanpa koma
	 */
	protected function FormatNumberPersen($number = 0,$des=0)
	{
		if($number != NULL){
			$snumber = number_format($number,$des,',','.');
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
		} else {
			return '';
		}
	}
	

}
?>