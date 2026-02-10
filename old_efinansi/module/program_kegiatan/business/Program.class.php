<?php

class Program extends Database {

   protected $mSqlFile= 'module/program_kegiatan/business/program.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);       
      //$this->setDebugOn();
   }


	function GetTahunAnggaranById($id){
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_by_id'],array($id));
		return $result['0'];
	}
      
   function GetDataProgram ($offset, $limit, $data='',$is_cari) {     
      
      if(!$is_cari) 
	    unset($data);			  
	  $result = $this->Open($this->mSqlQueries['get_data_program'], array('%'.$data['kode'].'%',
	                                                                      '%'.$data['nama'].'%',
																		  $data['ta_id'],
																		  $offset, $limit));   
	  
	  
	  return $result;
   }

   function GetCountDataProgram ($data,$is_cari) {  
      
      if(!$is_cari) {	    
	    unset($data);
	  }	  
	  	  
      $result = $this->Open($this->mSqlQueries['get_count_data_program'], array('%'.$data['kode'].'%',
	                                                                            '%'.$data['nama'].'%',
																				$data['ta_id']
																				));      
     
	  if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
    
   function GetDataProgramById($ProgramId) { 
      $result = $this->Open($this->mSqlQueries['get_data_program_by_id'], array($ProgramId));	       
      return $result;
   }
   
   function GetDataRenstra (&$idaktif='') {
   
     if(trim($idaktif)=='') {
	    $id = $this->Open($this->mSqlQueries['get_renstra_aktif'],array());
		if($id) {
		   $idaktif = $id[0]['id'];
		}	
     }
	   
     $result = $this->Open($this->mSqlQueries['get_data_renstra'],array());
	 
	 return $result;
   }
   
   function GetDataTahunAnggaran(&$idaktif) {
      if(trim($idaktif)=='') {
	    $id = $this->Open($this->mSqlQueries['get_ta_aktif'],array());
		if($id) {
		   $idaktif = $id[0]['id'];
		}	
      }
      $result = $this->Open($this->mSqlQueries['get_data_ta'],array($renstra_id));	 
	  return $result;  
   }
   function GetKodeSelanjutnya($tahun) {
      $result = $this->Open($this->mSqlQueries['get_kode_selanjutnya'],array($tahun));	 
     // print_r($result);
	   return $result[0];
   }
   
   /**
    * digantikan dengan popup kode rkakl kegiatan
    * maping baru
    * since 16 november 2012
   function GetKodeRkakl(){
		return $this->Open($this->mSqlQueries['get_kode_rkakl'], array());
	}
   */ 
//get data program for popup at module sub kegiatan
   
   function GetDataProgramPopup($renstra_id,$program_id,$kegiatan_nama) {
      $result = $this->Open($this->mSqlQueries['get_data_program_popup'],array($renstra_id,$program_id,$kegiatan_nama));	 
	  return $result;  
   }

//===DO==
   
   function DoAddProgram($data) {   
	   //$this->setDebugOn();
	   if(empty($data['sasaran_id'])){
		    $data['sasaran_id'] = NULL;
	   }
	   
      $result = $this->Execute($this->mSqlQueries['do_add_program'], array($data['nomor'],
	                                                                       $data['nama'],
																		   $data['ta_id'],
                                                                           $data['label'],
	                                                                       $data['rkakl_kegiatan_id'],
																		   $data['sasaran'],
                                                                           $data['indikator'],
	                                                                       $data['strategi'],
																		   $data['kebijakan'],
																		   $data['sasaran_id'])
																		   );  
     
      return $result;
   }
   
   function DoUpdateProgram($data) {
      	if(empty($data['sasaran_id'])){
		    $data['sasaran_id'] = NULL;
	   }
      $result = $this->Execute($this->mSqlQueries['do_update_program'], array($data['nomor'],
	                                                                          $data['nama'],
																			  $data['ta_id'],
																			  $data['label'],
	                                                                          $data['rkakl_kegiatan_id'],
	                                                                          $data['sasaran'],
                                                                              $data['indikator'],
	                                                                          $data['strategi'],
	                                                                          $data['kebijakan'],
	                                                                          $data['sasaran_id'],
	                                                                          $data['id']));
																			  
																			  
	 															
      return $result;
   }
   
   function DoDeleteProgram($ProgramId) {   
      $result=$this->Execute($this->mSqlQueries['do_delete_program'], array($ProgramId));	  
      return $result;
   }
   
   function isDuplicateNomor($id,$nomor,$thnId) {
      if(trim($id) != '')	    
         $data=$this->Open($this->mSqlQueries['is_duplicate_nomor_where'] , array($nomor,$id,$thnId));		 
	  else
	     $data=$this->Open($this->mSqlQueries['is_duplicate_nomor'] , array($nomor,$thnId));
	  
	  //$debug = sprintf($this->mSqlQueries['is_duplicate_nomor'], $nomor,$id);
	  //echo $debug;    
      
	  //debug($data);exit;
	  if ( sizeof($data) > 0 ) 
	     return true;
	  else
	     return false;
   }
}
?>
