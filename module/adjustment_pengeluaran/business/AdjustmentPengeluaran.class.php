<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';

class AdjustmentPengeluaran extends Database {

   protected $mSqlFile= 'module/adjustment_pengeluaran/business/adjustment_pengeluaran.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);  
	  //$this->setdebugOn();
   } 

   function GetUnitIdentity($id){
      return $this->Open($this->mSqlQueries['get_unit_kerja_id'],array($id));
   } 
   
//== untuk combo box
   function GetDataTahunAnggaran(&$idaktif,&$namaaktif) {
      if(trim($idaktif)=='') {
	    $id = $this->Open($this->mSqlQueries['get_ta_aktif'],array());
		if($id) {
		   $idaktif = $id[0]['id'];
		   $namaaktif = $id[0]['nama'];
		}	
      }	 
      $result = $this->Open($this->mSqlQueries['get_data_ta'],array());	 
	  return $result;  
   }
   
   function GetData($startRec,$itemViewed,$data) {
      $objUserUnitKerja = new UserUnitKerja;
	  $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	  $userrole = $objUserUnitKerja->GetRoleUser($userid);	   	  
	  
      if(trim($data['unit_id'])=='' || trim($data['unit_id'])=='all'){	      
	      if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) { //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
		      $data['unit_id'] = '%%'; 
			  $data['parent_id']='%%';
	      } elseif($unitkerja['is_unit_kerja']) {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = $data['unit_id'];
		  } else {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = '';
		  } 
	   } else $data['parent_id']='';
	   
	   if(trim($data['program_id']) == '')
	     $data['program_id']='%%';
	   if(trim($data['subprogram_id']) == '')
	     $data['subprogram_id']='%%';
	   if(trim($data['kegiatanref_id']) == '')
	     $data['kegiatanref_id']='%%';
	   
	   if(trim($data['jenis_kegiatan']) == 'all' || trim($data['jenis_kegiatan'])=='')
	     $data['jenis_kegiatan']='%%';
       $ret = $this->Open($this->mSqlQueries['get_data'],array(
					$data['ta_id'],
					$data['unit_id'],'%',
					$data['unit_id'],
					$data['program_id'],
					$data['subprogram_id'],
					$data['kegiatanref_id'],
					$data['kodenama'],
					$data['kodenama'],
					$data['kodenama'],
					"%".$data['kodenama']."%",
					"%".$data['kodenama']."%",
					"%".$data['kodenama']."%",
					$startRec,
					$itemViewed));	 
	   //debug($ret);
	   //$this->mdebug();
	   return $ret;
   }
   
   function GetCount() {
	$result = $this->Open($this->mSqlQueries['get_count'],array());	 
	  
	  //$this->mdebug();
	  if (!$result)
       return 0;
     else
       return $result[0]['total'];   	   
	}      
   function GetCountOld($data) {      
	  $objUserUnitKerja = new UserUnitKerja;
	  $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	  $userrole = $objUserUnitKerja->GetRoleUser($userid);	   	  
	  
      if(trim($data['unit_id'])=='' || trim($data['unit_id'])=='all'){	      
	      if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) { //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
		      $data['unit_id'] = '%%'; 
			  $data['parent_id']='%%';
	      } elseif($unitkerja['is_unit_kerja']) {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = $data['unit_id'];
		  } else {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = '';
		  } 
	   } else $data['parent_id']='';
	   
	 if(trim($data['program_id']) == '')
	     $data['program_id']='%%';
	   if(trim($data['subprogram_id']) == '')
	     $data['subprogram_id']='%%';
	   if(trim($data['kegiatanref_id']) == '')
	     $data['kegiatanref_id']='%%';
	   
	   if(trim($data['jenis_kegiatan']) == 'all' || trim($data['jenis_kegiatan'])=='')
	     $data['jenis_kegiatan']='%%';
		 
      $result = $this->Open($this->mSqlQueries['get_count'],array($data['ta_id'],$data['unit_id'],$data['unit_id'],$data['parent_id'],$data['parent_id'],$data['program_id'],$data['subprogram_id'],$data['kegiatanref_id'],$data['kodenama'],$data['kodenama'],$data['kodenama'],"%".$data['kodenama']."%","%".$data['kodenama']."%","%".$data['kodenama']."%"));	 
	  
	  //$this->mdebug();
	  if (!$result)
       return 0;
     else
       return $result[0]['total'];   
      
   }
   
   function GetResumeUnitKerja($data){
      $objUserUnitKerja = new UserUnitKerja;
	  $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	  $userrole = $objUserUnitKerja->GetRoleUser($userid);	   	  
	  
      if(trim($data['unit_id'])=='' || trim($data['unit_id'])=='all'){	      
	      if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) { //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
		      $data['unit_id'] = '%%'; 
			  $data['parent_id']='%%';
	      } elseif($unitkerja['is_unit_kerja']) {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = $data['unit_id'];
		  } else {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = '';
		  } 
	   } else $data['parent_id']='';
	   
	   if(trim($data['program_id']) == '')
	     $data['program_id']='%%';
	   
	   if(trim($data['jenis_kegiatan']) == 'all' || trim($data['jenis_kegiatan'])=='')
	     $data['jenis_kegiatan']='%%';
		 
		
	      
		  
       $ret = $this->Open($this->mSqlQueries['get_resume_unit_kerja'],array($data['ta_id'],$data['unit_id'],$data['unit_id'],$data['parent_id'],$data['parent_id'],$data['program_id'],$data['jenis_kegiatan']));	 
	   //$this->mdebug();
	   return $ret;
   }
   
   function GetResumeProgram($data){
      $objUserUnitKerja = new UserUnitKerja;
	  $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	  $userrole = $objUserUnitKerja->GetRoleUser($userid);	   	  
	  
      if(trim($data['unit_id'])=='' || trim($data['unit_id'])=='all'){	      
	      if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) { //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
		      $data['unit_id'] = '%%'; 
			  $data['parent_id']='%%';
	      } elseif($unitkerja['is_unit_kerja']) {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = $data['unit_id'];
		  } else {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = '';
		  } 
	   } else $data['parent_id']='';
	   
	   if(trim($data['program_id']) == '')
	     $data['program_id']='%%';
	   
	   if(trim($data['jenis_kegiatan']) == 'all' || trim($data['jenis_kegiatan'])=='')
	     $data['jenis_kegiatan']='%%';
		 
		
	      
		  
       $ret = $this->Open($this->mSqlQueries['get_resume_program'],array($data['ta_id'],$data['unit_id'],$data['unit_id'],$data['parent_id'],$data['parent_id'],$data['program_id'],$data['jenis_kegiatan']));	 
	   //$this->mdebug();
	   return $ret;
   }
   
   function GetResumeKegiatan($data){
      $objUserUnitKerja = new UserUnitKerja;
	  $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	  $userrole = $objUserUnitKerja->GetRoleUser($userid);	   	  
	  
      if(trim($data['unit_id'])=='' || trim($data['unit_id'])=='all'){	      
	      if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) { //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
		      $data['unit_id'] = '%%'; 
			  $data['parent_id']='%%';
	      } elseif($unitkerja['is_unit_kerja']) {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = $data['unit_id'];
		  } else {
		      $data['unit_id'] = $unitkerja['unit_kerja_id'];
			  $data['parent_id'] = '';
		  } 
	   } else $data['parent_id']='';
	   
	   if(trim($data['program_id']) == '')
	     $data['program_id']='%%';
	   
	   if(trim($data['jenis_kegiatan']) == 'all' || trim($data['jenis_kegiatan'])=='')
	     $data['jenis_kegiatan']='%%';
		 
		
	      
		  
       $ret = $this->Open($this->mSqlQueries['get_resume_kegiatan'],array($data['ta_id'],$data['unit_id'],$data['unit_id'],$data['parent_id'],$data['parent_id'],$data['program_id'],$data['jenis_kegiatan']));	 
	   //$this->mdebug();
	   return $ret;
   }
   
  function GetCountUnitKerja ($nama) {
      $objUserUnitKerja = new UserUnitKerja;
	  
      $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	  //$userrole = $objUserUnitKerja->GetRoleUser($userid);	  
	  $parent_id = $unitkerja['unit_kerja_id'];
	 /**	   
	  if(isset($userrole['role_id']))
	    if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
		   $parent_id = '%%'; 
		
     $result = $this->Open($this->mSqlQueries['get_count_unit_kerja'], array($parent_id,'%'.$nama.'%'));	
	 	*/    	 
	$result = $this->Open($this->mSqlQueries['get_count_nit_kerja'],
					array(
							$parentId,
							'.%',
							$parentId,
							'%'.$nama.'%',
						 )
					);
     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }
   
   function GetUnitKerja($startRec,$itemViewed,$nama) {
      $objUserUnitKerja = new UserUnitKerja;
	  
      $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	  //$userrole = $objUserUnitKerja->GetRoleUser($userid);	  
	  $parent_id = $unitkerja['unit_kerja_id'];
	  
	   /**
	  if(isset($userrole['role_id']))
	    if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
		   $parent_id = '%%';   
		 
      $ret = $this->Open($this->mSqlQueries['get_unit_kerja'],array($parent_id,'%'.$nama.'%',$startRec,$itemViewed));	 	  	  
	  
	  
	  return $ret;
	  */
	  	$result = $this->Open($this->mSqlQueries['get_unit_kerja'],
					array(
							$parent_id,
							'.%',
							$parent_id,
							'%'.$nama.'%',
							$startRec, $itemViewed
						 )
					);
	
		return $result;  
   }
   
   function GetComboJenisKegiatan() {
		$result = $this->Open($this->mSqlQueries['get_combo_jenis_kegiatan'], array());
		return $result;
	}
   
   function GetCountDataRealisasiPencairan($kegiatandetail_id){
		$result = $this->Open($this->mSqlQueries['get_count_data_realisasi'],array($kegiatandetail_id));
		
		return $result[0]['total_data'];
   }
   
   /**
	 * added fitur baru
	 * @since 11 Januari 2012
	 */
 	public function GetTotalSubUnitKerja($parentId)
	 {
	 	$result = $this->Open($this->mSqlQueries['get_total_sub_unit_kerja'], 
		 	array($parentId));
	 	return $result[0]['total'];
	 }

}
?>