<?php

/**
 * 
 * AppPopupKodePenerimaanHeader
 * untuk olah data kode penerimaan bertipe header
 * @since 10 Januari 2013
 * @analist nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2013 Gamatechno Indonesia
 * 
 */ 
class AppPopupKodePenerimaanHeader extends Database 
{
   protected $mSqlFile= 'module/kode_penerimaan/business/app_popup_kode_penerimaan_header.sql.php';
   
   public function __construct($connectionNumber=0) 
   {
      parent::__construct($connectionNumber);       
   }
      
   /**
    * @name function GetData
    * @description untuk menampilkan data kode penerimaan
    * @param string $kode :kode penerimaan
    * @param string $nama : nama penerimaan
    * @param number $startRec : awal record
    * @param number $limit : batas record
    * @return array
    * @access public
    */
   public function GetData($kode,$nama,$startRec,$limit) 
   {
		$result = $this->Open($this->mSqlQueries['get_data'], 
						array(
							'%'.$kode.'%',
							'%'.$nama.'%',
							$startRec,
							$limit
						));
		return $result;
   }

   /**
    * @name function GetCount
    * @description untuk menghitung row 
    * @return number
    * @access public 
    */	
   public function GetCount() 
   {
     $result = $this->Open($this->mSqlQueries['get_count'], array());
     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }
}

?>