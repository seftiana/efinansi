<?php

class SkenarioDetail extends Database {

   protected $mSqlFile= 'module/skenario/business/skenariodetail.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);     
   }
   
   
//==GET==      
   function GetUnitKerja(){
		return $this->open($this->mSqlQueries['get_unit_kerja'],array());
	}
   
   

//===DO==

   function DoAdd($data) {
      $ret = $this->Execute($this->mSqlQueries['do_add'], array($data['skenario_id'],
	                                                            $data['debet_coa_id'],
																$data['kredit_coa_id'],
																$data['prosentase']));	  
	  return $ret;
																	
   }
   
   function DoDelete($id){
      return $this->Execute($this->mSqlQueries['do_delete'], array($id));
   }
   
   function DoDeleteAllSkenario($id){
      return $this->Execute($this->mSqlQueries['do_delete_all_skenario'], array($id));
   }
   
   
}
?>
