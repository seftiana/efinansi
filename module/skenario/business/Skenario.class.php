<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/skenario/business/SkenarioDetail.class.php';

class Skenario extends Database {

   protected $mSqlFile= 'module/skenario/business/skenario.sql.php';
   
   public $SkenarioDetail;
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);   
      $this->SkenarioDetail = new SkenarioDetail;	  
   }
   
   
   
   
//==GET==      
   function GetData($start,$end){
		return $this->open($this->mSqlQueries['get_data'],array($start,$end));
   }
   function GetCount(){
        $ret = $this->open($this->mSqlQueries['get_count'],array());
		if($ret)
		   return $ret[0]['total'];
		else
		   return false;
   }
   
   function GetDataDetail($id){        
		return $this->open($this->mSqlQueries['get_data_detail'],array($id));
   }
   
   
	
   
   

//===DO==
   function DoAdd($data,&$msgerr){   
      $this->StartTrans();
	  $this->SkenarioDetail->StartTrans();
	  
	  if(isset($data['skenario']['id']) && trim($data['skenario']['id']) !='') {
	     $ok = $this->Execute($this->mSqlQueries['do_update'], array($data['skenario']['nama'],$data['skenario']['id']));
		 
			  //
			 $tes= $this->Execute($this->mSqlQueries['select_delete_dobel_detail'], array($data['skenario']['id']));
			   //echo $this->GetLastError();
		 $skenario_id = $data['skenario']['id'];
	  } else {
	     $ok = $this->Execute($this->mSqlQueries['do_add'], array($data['skenario']['nama']));
		 $skenario_id=$this->Insert_ID();
	  }
	  
	  
	  if($ok){	     		 
		 
		 //tambah data akun detail debet
		 if(is_array($data['debet']['tambah'])) {
		    
		    foreach($data['debet']['tambah'] as $val){
			   $datadetail['skenario_id']= $skenario_id;
			   $datadetail['debet_coa_id'] = $val['id'];
			   $datadetail['kredit_coa_id'] = null;
			   $datadetail['prosentase'] = $val['prosentase'];			   
			   $detok = $this->SkenarioDetail->DoAdd($datadetail);
			   
			   if(!$detok) {
			      $msgerr['debet']['id'][] = $val['id'];
				  $msgerr['debet']['msg'] .= $val['nama'].', ';     
                  $ok = false;
                  break;
			   }
			}//end foreach 
		 } //end if array debet
		 
		 //tambah data akun detail kredit
		 if(is_array($data['kredit']['tambah']) && $ok) {
		    
		    foreach($data['kredit']['tambah'] as $val){
			   $datadetail['skenario_id']= $skenario_id;
			   $datadetail['debet_coa_id'] = null;
			   $datadetail['kredit_coa_id'] = $val['id'];
			   $datadetail['prosentase'] = $val['prosentase'];			   
			   $detok = $this->SkenarioDetail->DoAdd($datadetail);
			  
			   
			   if(!$detok) {
			      $msgerr['kredit']['id'][] = $val['id'];
				  $msgerr['kredit']['msg'] .= $val['nama'].', ';     
                  $ok = false;		
                  break;				  
			   }
			}//end foreach 
		 } //end if array kredit
		 
		 
	  } else
	    $msgerr = " skenario ";
	  
	  $this->SkenarioDetail->EndTrans($ok);
	  $this->EndTrans($ok);

	  return $ok;	
		return $tes;  
   }
   
   function DoDelete($id){
    $ok = $this->SkenarioDetail->DoDeleteAllSkenario($id);	
	if($ok)
      return $this->Execute($this->mSqlQueries['do_delete'], array($id));	
	else
	  return false;
	
   }
   
   
}
?>
