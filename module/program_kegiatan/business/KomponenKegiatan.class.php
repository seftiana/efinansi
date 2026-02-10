<?php

class KomponenKegiatan extends Database {

   protected $mSqlFile= 'module/program_kegiatan/business/komponen_kegiatan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);     
   }
//==GET==      
   function GetData ($offset, $limit, $id) {       
	  
      $result = $this->Open($this->mSqlQueries['get_data'], array($id,$offset,$limit));  															 
	  return $result;
   }

   function GetCount ($id) {   
     $result = $this->Open($this->mSqlQueries['get_data_count'], array($id));	
	 
     if (!$result)
       return 0;
     else
       return $result[0]['total'];    
   }
   
   function GetDataDetail($id) {
      $result = $this->Open($this->mSqlQueries['get_data_detail'], array($id));  
	  if($result)
	     return $result[0];
	  else																 
	    return $result;  
   }
   
   function GetDataById($kegref_id,$komponen_id){
      $result = $this->Open($this->mSqlQueries['get_data_by_id'], array($kegref_id,$komponen_id));																  	  
	  
      if($result)
	     return $result[0];
	  else																 
	    return $result;     
   }
    
     
   function GetKomponen($offset, $limit,$kode,$nama, $kegref_id)
   {
	   
	   /**
	    * @modified since 5 Dec 2013
	    * @by noor hadi <noor.hadi@gamatechno.com>
	    * @untuk antisipasi jikalau kode komponen NULL
	    */
		if(empty($kode) || (trim($kode) == '')){
			$flagKode = 1;
		} else {
			$flagKode = 0;
		}
		
		$ret = $this->Open($this->mSqlQueries['get_komponen'],
									array(
											'%'.$kode.'%',$flagKode,
											'%'.$nama.'%',
											$kegref_id,
											$offset, 
											$limit));
											
		return $ret; 
   }
   
   function GetKomponenCount($kode,$nama)
   {      
	   /**
	    * @modified since 5 Dec 2013
	    * @by noor hadi <noor.hadi@gamatechno.com>
	    * @untuk antisipasi jikalau kode komponen NULL
	    */
		if(empty($kode) || (trim($kode) == '')){
			$flagKode = 1;
		} else {
			$flagKode = 0;
		}
			   
		$ret = $this->Open($this->mSqlQueries['get_komponen_count'],
											array(
													'%'.$kode.'%',$flagKode,
													'%'.$nama.'%'
												));
		if($ret)
			return $ret[0]['total'];	  
		else return 0;      
		
   }
   
   function DoAdd($komponen_id,$kegref_id,$nominal) {
     $result = $this->Execute($this->mSqlQueries['do_add'], array($komponen_id,
	                                                              $kegref_id,
																  $nominal
																  ));
     
	 return $result;
   }
   
   function DoUpdate($komponen_id,$komponen_id_old,$kegref_id,$nominal) {
     $result = $this->Execute($this->mSqlQueries['do_update'], array($komponen_id,
	                                                                 $nominal,
																	 $kegref_id,
																	 $komponen_id_old																  
																  ));
     
	 return $result;
   }
   
   function DoDelete($komponen_id,$kegref_id) {
      $result = $this->Execute($this->mSqlQueries['do_delete'], array($komponen_id,
	                                                              $kegref_id
																	));
																	
																	//$this->mdebug(1);
     
	 return $result;
   }
   
   
//ke tabel finansi_pa_komponen_unit_kerja
   function DoAddKomponenUnitKerja($kompid,$unitkerjaid)
   {                                             
   		$result=$this->Execute($this->mSqlQueries['do_add_komponen_unit_kerja'], 
	   	array($kompid,$unitkerjaid));
      	return $result;
   }
   function DoDeleteKomponenUnitKerja($kompid)
   {
   		$result=$this->Execute($this->mSqlQueries['do_delete_unit_kerja_ref'], 
	   	array($kompid));
      	return $result;
   }
   function GetCountKomponenUnitKerja($kompid)
   {
   		$result = $this->Open($this->mSqlQueries['get_count_komponen_unit_kerja'], 
		   	array($kompid));
 		return $result[0]['jumlah'];
   }

   
   /**
    * fungsi GetListUnitKerja
    * untuk mendapatkan data unit kerja dan jumlah unit kerja yang 
    * berada pada tabel finansi_pa_komponen_unit_kerja
    * @param number $kompId : id komponen
    * @return array
    */
   function GetListUnitKerja($kompId)
   {
 		$result = $this->Open($this->mSqlQueries['get_unit_kerja_komponen'], array($kompId));
 		return $result;
   }


   
}
?>
