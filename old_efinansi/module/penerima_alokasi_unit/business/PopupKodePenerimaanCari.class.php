<?php
class PopupKodePenerimaanCari extends Database {

   protected $mSqlFile= 'module/penerima_alokasi_unit/business/popup_kode_penerimaan_cari.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);  
      //$this->setDebugOn();
   }

	function GetData($offset, $limit, $kodePenerimaan) 
	{
		//$this->setDebugOn();
		$result = $this->Open($this->mSqlQueries['get_data_kode'], 	
						array(
								'%'.$kodePenerimaan.'%', 
								'%'.$kodePenerimaan.'%',
								$offset, 
								$limit));
		return $result;
	}

	function GetCountData( $kodePenerimaan) 
	{
		
		$result = $this->Open($this->mSqlQueries['get_count_data'],
						array(
								'%'.$kodePenerimaan.'%', 
								'%'.$kodePenerimaan.'%',
						)
						);
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
	
}
?>