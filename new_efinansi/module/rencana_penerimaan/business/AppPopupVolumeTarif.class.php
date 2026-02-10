<?php

/**
 * class AppPopupVolumeTarif
 * untuk mendapatkan data volume dan tarif
 * @package rencana_penerimaan
 * @since 06 Februari 2012
 * @copyright 2012 gamatechno
 * @access public
 */
 
 
class AppPopupVolumeTarif extends Database 
{

   	protected $mSqlFile= 'module/rencana_penerimaan/business/apppopupvolumetarif.sql.php';
   
   	public function __construct($connectionNumber=0) 
    {
      	parent::__construct($connectionNumber);
	  	//$this->SetDebugOn();       
   	}
      
    /**
     * fungsi GetData
     * Untuk mendapatkan data volume dan tarif
     * @param $offset number untuk kepentingan paging
     * @param $limit number untuk membatasi jumlah maximum halaman uang ditampilkan
     * @param $nama string nama tarif
     * @param $fakprodi string nama fakultas / program studi
     * @return array
     * @access public
     */
	public function GetData($offset, $limit, $nama='', $fakprodi='') 
	{
		$result = $this->Open($this->mSqlQueries['get_data_volume_tarif'],
									array(
										'%'.$fakprodi.'%',
										'%'.$fakprodi.'%',
										'%'.$nama.'%',
										$offset,
										$limit
									));
		/**
		 * proses integrasi
		 * memasukkan nilai volume
		 */
	 	for ($i=0; $i<sizeof($result); $i++) {
			$result[$i]['volume'] = $this->GetVolume($result[$i]['prodi_kode']);
	 	}
		/**
		 * end
		 */
		return $result;
	}

	/**
	 * fungsi GetCountData
	 * untuk mendapatkan jumlah total data yang tampil
     * @param $nama string nama tarif
     * @param $fakprodi string nama fakultas / program studi
     * @return number
     * @access public
     */
	public function GetCountData($nama='', $fakprodi='') 
	{
		$result = $this->Open($this->mSqlQueries['get_count_data_volume_tarif'],
									array(
										'%'.$fakprodi.'%',
										'%'.$fakprodi.'%',
										'%'.$nama.'%',
									));
	
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
	
	/**
	 * fungsi GetVolume
	 * untuk mendapatkan volume ( diambil dari aplikasi lain)
	 * @param $prodi_kode Kode Program studi
	 * @access protected
	 * @return number
	 */
 	protected function GetVolume($prodi_kode)
 	{
 		/**
 		 * ambil query dari tabel finansi_ref_formula
 		 */
 		$result = $this->Open($this->mSqlQueries['get_volume'], array());
		$result =  $this->open($result['0']['volume_query'],array($prodi_kode));
 		//$result = $this->Open($this->mSqlQueries['get_volume'],array($prodi_kode));
 		if (!$result) {
			return 0;
		} else {
			return $result[0]['volume'];
		}
 		return $result;
 	}
}

?>