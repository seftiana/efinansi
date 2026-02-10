<?php

/**
 * Class PopupSatker
 * untuk menangani proses pemilihan unit kerja
 */
 
class AppPopupSatker extends Database 
{

   	protected $mSqlFile= 'module/rencana_kinerja_tahunan_kegiatan/business/apppopupsatker.sql.php';
   
   	public function __construct($connectionNumber=0) 
   	{
   		parent::__construct($connectionNumber);       
   	}
      
   	public function GetDataSatker ($offset, $limit, $satker='', $pimpinan='',$unitkode='') 
   	{
      $sql = sprintf($this->mSqlQueries['get_data_satker'], '%s', '%s', '%s', '%s', '%d','%d');
      $result = $this->Open($sql, array(
      							'%'.$unitkode.'%',
      							'%'.$unitkode.'%',
			   				    '%'.$satker.'%', 
							    '%'.$pimpinan.'%', 
							    $offset, 
							    $limit));
	  //$debug = sprintf($sql, '%'.$satker.'%', '%'.$pimpinan.'%', $offset, $limit);
	  //echo $debug;
      return $result;
   	}

   	public function GetCountDataSatker ($satker='', $pimpinan='',$unitkode='') 
   	{
      $result = $this->Open($this->mSqlQueries['get_count_data_satker'], 
	  					array(
	  							'%'.$unitkode.'%',
	  							'%'.$unitkode.'%',
						  		'%'.$satker.'%', 
						        '%'.$pimpinan.'%'));      
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
