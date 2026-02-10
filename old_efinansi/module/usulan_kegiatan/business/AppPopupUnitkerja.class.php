<?php

class AppPopupUnitKerja extends Database 
{

   	protected $mSqlFile= 'module/usulan_kegiatan/business/app_popup_unit_kerja.sql.php';
   
   	public function __construct($connectionNumber=0) 
   	{
      parent::__construct($connectionNumber);   
	  //$this->SetDebugOn();	  
   	}
      
   	public function GetDataSatker ($offset, $limit, $satker='', $pimpinan='') 
   	{
      $sql = sprintf($this->mSqlQueries['get_data_satker'], '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array('%'.$satker.'%', '%'.$pimpinan.'%', $offset, $limit));
	  //$debug = sprintf($sql, '%'.$satker.'%', '%'.$pimpinan.'%', $offset, $limit);
	  //echo $debug;
      return $result;
   	}

   	public function GetCountDataSatker ($satker='', $pimpinan='') 
   	{
      $result = $this->Open(
	  					$this->mSqlQueries['get_count_data_satker'], 
				  		array(
						  		'%'.$satker.'%', 
						  		'%'.$pimpinan.'%'
						  ));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   	}
   
  	public function GetDataSatkerPusat ($offset, $limit, $satker='', $pimpinan='') 
	{
      $sql = sprintf($this->mSqlQueries['get_data_satker_pusat'], '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array('%'.$satker.'%', '%'.$pimpinan.'%', $offset, $limit));
	  //$debug = sprintf($sql, '%'.$satker.'%', '%'.$pimpinan.'%', $offset, $limit);
	  //echo $debug;
      return $result;
   	}

   	public function GetCountDataSatkerPusat ($satker='', $pimpinan='') 
   	{
      $result = $this->Open(
	  					$this->mSqlQueries['get_count_data_satker_pusat'], 
				  		array(
						  		'%'.$satker.'%', 
						  		'%'.$pimpinan.'%'
						  ));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   	}
   
  	public function GetDataSatkerUnit ($offset, $limit, $satker, $pimpinan,$unitKode) 
	{
	  $result = $this->Open(
	  					$this->mSqlQueries['get_data_satker_unit'],
						array(
								$unitKode,$unitKode.'.%',
								'%'.$satker.'%', 
								'%'.$pimpinan.'%', 
								$offset, 
								$limit
							));
	  return $result;
   	}

   	public function GetCountDataSatkerUnit ($satker='', $pimpinan='',$unitKode) 
   	{
      $result = $this->Open(
	  				$this->mSqlQueries['get_count_data_satker_unit'], 
				  	array(
					  		$unitKode,$unitKode.'.%',
					  		'%'.$satker.'%', 
					  		'%'.$pimpinan.'%'
					  	));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   	}
   
   	public function GetUnit()
   	{
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());		
		$result = $this->Open($this->mSqlQueries['get_unit'], array($userId));
		return $result;	
	}
	
	/**
	 * Fungsi GetTotalSunUnit
	 * Untuk mendapatkan total sub unit
	 * add
	 * @since 2 Januari 2012
	 * @access public
	 */
	 public function GetTotalSubUnit($unitParentId)
	 {
	 	$total = $this->Open($this->mSqlQueries['get_total_sub_unit'],array($unitParentId));
	 	return $total[0]['total'];
	 }
}
