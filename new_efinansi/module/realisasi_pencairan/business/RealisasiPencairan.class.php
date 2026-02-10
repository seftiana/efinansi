<?php

class RealisasiPencairan extends Database {

   protected $mSqlFile= 'module/realisasi_pencairan/business/realisasi_pencairan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);     
   }
//==GET==      
   function GetData ($offset, $limit, $data) {  
      
      if($data['program_id']=='' || $data['program_id']=='all')	  
	     $data['program_id']='%%';
	  
	  if($data['jenis_kegiatan']=='' || $data['jenis_kegiatan']=='all')	  
	     $data['jenis_kegiatan']='%%';
	
	  if(!isset($data['subunit_id']) || trim($data['subunit_id']) =='' )	  
	     $data['subunit_id']=$data['unit_id'];
	  else {
	    $data['unit_id']=$data['subunit_id'];
		$data['subunit_id']='';
	  }
	     
	  
      $result = $this->Open($this->mSqlQueries['get_data'], array($data['ta_id'],
                                                                  $data['unit_id'],
																  $data['subunit_id'],
																  $data['program_id'],
																  $data['jenis_kegiatan'],																                                                              															  
																  $offset,$limit));  		  	    
	  
	  if($result) {
	     foreach($result as &$row) {
		    $row['pr_tanggal'] = $this->date2string($row['pr_tanggal']);
		 }
	  }
	  return $result;
   }

   function GetCount ($data) { 
     if($data['program_id']=='' || $data['program_id']=='all')	  
	     $data['program_id']='%%';
	  
	  if($data['jenis_kegiatan']=='' || $data['jenis_kegiatan']=='all')	  
	     $data['jenis_kegiatan']='%%';
	
	  if(!isset($data['subunit_id']) || trim($data['subunit_id']) =='' )	  
	     $data['subunit_id']=$data['unit_id'];
	  else {
	    $data['unit_id']=$data['subunit_id'];
		$data['subunit_id']='';
	  }
	     
	  
      $result = $this->Open($this->mSqlQueries['get_count'], array($data['ta_id'],
                                                                  $data['unit_id'],
																  $data['subunit_id'],
																  $data['program_id'],
																  $data['jenis_kegiatan']));  													  
	 
     if (!$result)
       return 0;
     else
       return $result[0]['total'];    
   }
   
   function GetDataCetak ($id) {       	  
      $result = $this->Open($this->mSqlQueries['get_data_cetak'], array($id));			
      //$this->mdebug();	  
	  return $result;
   }
   
   function GetDataById($id) {      
      $result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));		        
	  //$this->mdebug();
	  if($result)
	     return $result[0];
	  else
	     return $result;	  
   }
    
     
   function GetDataTahunAnggaran(&$idaktif) {
      if(trim($idaktif)=='') {
	    $id = $this->Open($this->mSqlQueries['get_ta_aktif'],array());
		if($id) {
		   $idaktif = $id[0]['id'];
		}	
      }	 
      $result = $this->Open($this->mSqlQueries['get_data_ta'],array());	 
	  return $result;  
   }
   
   function GetDataTahunAnggaranSekarang(){
      $result = $this->Open($this->mSqlQueries['get_ta_aktif'],array());	 
	  if($result)	  
	     return $result[0];  
	  else
	     return false;
   }
   
   function GetDataProgram(){
      $result = $this->Open($this->mSqlQueries['get_data_program'],array());	 
	  return $result;  
   }
   
   function GetDataJenisKegiatan(){
      $result = $this->Open($this->mSqlQueries['get_data_jenis_kegiatan'],array());	  
	  return $result;  
   }
   
   function GetMinTahun(){      
      $result = $this->Open($this->mSqlQueries['get_min_tahun'],array());	
      if($result) {
	     $tgl = $result[0]['min'];
		 $tgl=explode('-',$tgl);
		 return $tgl[0]-5;
	  } else {
	     $tgl =date("Y");
		 return $tgl -5;	     
	  }  
   }
   
   function GetMaxTahun(){
      $result = $this->Open($this->mSqlQueries['get_max_tahun'],array());
      if($result) {
	     $tgl = $result[0]['max'];
		 $tgl=explode('-',$tgl);
		 return $tgl[0]+5;
	  } else {
	     $tgl =date("Y");
		 return $tgl + 5;	     
	  }   	  
	  
   }
   
   
   function GetCountUnitKerja ($nama,$parent_id) {
      /*$objUserUnitKerja = new UserUnitKerja;
	  
      $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	  $userrole = $objUserUnitKerja->GetRoleUser($userid);	  
	  $parent_id = $unitkerja['unit_kerja_id'];
	 
	  if(isset($userrole['role_id']))
	    if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
		   $parent_id = '%%'; 
		   */
		
     $result = $this->Open($this->mSqlQueries['get_count_unit_kerja'], array($parent_id,'%'.$nama.'%'));	
	 
	    	 
     if (!$result)
       return 0;
     else
       return $result[0]['total'];
   }
   
   function GetUnitKerja($startRec,$itemViewed,$nama,$parent_id) {
      /*$objUserUnitKerja = new UserUnitKerja;
	  
      $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
	  $unitkerja= $objUserUnitKerja->GetUnitKerjaUser($userid); 
	  $userrole = $objUserUnitKerja->GetRoleUser($userid);	  
	  $parent_id = $unitkerja['unit_kerja_id'];	  
	  
	  if(isset($userrole['role_id']))
	    if(($userrole['role_id']==1) || ($userrole['role_id'] ==4)) //role_id  =1 (administrator) , role_id =2 (pusat) kalo administor ato pusat ditampilin semua unitkerjanya
		   $parent_id = '%%';  	 */
		   
	  //kemungkinan besar script diatas masih kepake
	  
		 
      $ret = $this->Open($this->mSqlQueries['get_unit_kerja'],array($parent_id,'%'.$nama.'%',$startRec,$itemViewed));	 	  	  
	  	  
	  return $ret;  
   }   
   
   function GetJenisKegiatan($detailkegiatan_id) {
      $ret = $this->Open($this->mSqlQueries['get_jenis_kegiatan'], array($id));									 	  
	  if($ret)
	     return $ret[0]['jenis_kegiatan'];
	  else
	     return $ret;  
   }
   
   
   function isNominalValid($kegiatandetail_id,$nominal,&$max_nominal,$realisasipencairan_id=null) {
      $rencana_nominal = $this->Open($this->mSqlQueries['get_rencana_nominal'], array($kegiatandetail_id));
	  
	  
	  if($realisasipencairan_id !=null) {
	     $realisasi_nominal = $this->Open($this->mSqlQueries['get_realisasi_nominal_edit'], array($kegiatandetail_id,Dispatcher::Instance()->Decrypt($realisasipencairan_id)));
		  
      } else
	     $realisasi_nominal = $this->Open($this->mSqlQueries['get_realisasi_nominal'], array($kegiatandetail_id));
	  
	  	  
	  if($rencana_nominal) 
	     $ret['rencana_nominal_approve'] = $rencana_nominal[0]['nominal_approve'];
	  else
	     $ret['rencana_nominal_approve'] =0;
		 
	  if($realisasi_nominal) {
	     $ret['realisasi_nominal'] = $realisasi_nominal[0]['nominal'];
		 $ret['realisasi_nominal_approve'] = $realisasi_nominal[0]['nominal_approve'];
		 
		 if($realisasipencairan_id !=null) 
		    $nominal_approve_self = $realisasi_nominal[1]['nominal'];			
		 else
		    $nominal_approve_self = 0 ;
		    
			
	  } else {
	     $ret['realisasi_nominal'] = 0;
		 $ret['realisasi_nominal_approve'] = 0;
	  }
	  
	  //kalo data belum pernah dimasukan ke realisasi lakukan pengecekan dari nominal yang boleh di approve
	  if(($ret['realisasi_nominal']==0) && ($ret['realisasi_nominal_approve']==0)) {
	     if($nominal > $ret['rencana_nominal_approve']) {
		    $max_nominal = $ret['rencana_nominal_approve'];
			return false;
		 } else return true;	     
	  } 
	  //kalo ini ngecek nya dari realisasi nominal karena sudah ada data yang dimasukan sebelumnya.
	  elseif($ret['realisasi_nominal'] !=0) {
	     //cek kalo sudah ada yang diapprove
		 if($ret['realisasi_nominal_approve'] !=0)
		   $sisa = $ret['rencana_nominal_approve'] - ($ret['realisasi_nominal'] - $ret['realisasi_nominal_approve']);	 
		 else 
		   $sisa = $ret['rencana_nominal_approve'] - $ret['realisasi_nominal'];	
      		
		$sisa += $nominal_approve_self;	   
		if($nominal > $sisa) {
		   $max_nominal = $sisa;		     
		   return false;		
		} else return true;					   
		    
	
	  } //end if ralisasi nominal !=0      
   }
   
   
   
 function DoAdd($data) {	
   $userid=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId(); 
   $ret = $this->Execute($this->mSqlQueries['do_add'], array($data['kegiatandetail_id'],
		                                                     $data['nomor_pengajuan'],
															 $data['nominal'],
															 $data['keterangan'],
															 $userid,
															 $data['tanggal']																	 
															));
		   
		  
	
    return $ret;
  }
   
   
   
   
	
	function DoUpdate($data) {
     $ret = $this->Execute($this->mSqlQueries['do_update'], array($data['kegiatandetail_id'],
		                                                          $data['nomor_pengajuan'],
															      $data['nominal'],
															      $data['keterangan'],
															      $userid,
															      $data['tanggal'],
																  $data['id']
															));         		  
		//$this->mdebug();  
      return $ret;
    }   
	
	function DoDelete($id) {
	   $ret = $this->Execute($this->mSqlQueries['do_delete'], array($id));		
       return $ret;
	}
	
	function date2string($date) {
	   $bln = array(
	                1  => 'Januari',
					2  => 'Februari',
					3  => 'Maret',
					4  => 'April',
					5  => 'Mei',
					6  => 'Juni',
					7  => 'Juli',
					8  => 'Agustus',
					9  => 'September',
					10 => 'Oktober',
					11 => 'November',
					12 => 'Desember'					
	               );
	   $arrtgl = explode('-',$date);
	   return $arrtgl[2].' '.$bln[(int) $arrtgl[1]].' '.$arrtgl[0];
	   
	}



   
}
?>
