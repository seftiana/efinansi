<?php

class AppAlokasiCoaAkademik extends Database {

	protected $mSqlFile= 'module/pengaturan_alokasi_coa_akademik/business/appalokasicoaakademik.sql.php';

	function __construct($connectionNumber=0) {
		parent::__construct($connectionNumber);
		//$this->mrDbEngine->debug = 1;
        //$this->SetDebugOn();
	}



   //detil
   function GetCountCoa() {
      $result = $this->Open($this->mSqlQueries['get_count_coa'], array());
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }

   function GetCoa($kode,$nama,$offset, $limit) {
      //$this->SetDebugOn();
      $result = $this->Open($this->mSqlQueries['get_data_coa'], array( '%'.$kode.'%','%'.$nama.'%',$offset, $limit));
      return $result;
   }

   // do 
   function DoAddCoa($id_coa) {
      $result = $this->Execute($this->mSqlQueries['do_add_coa'], array( $id_coa));
      return $result;
   }

   function DoDeleteCoaById($id) {
      $result=$this->Execute($this->mSqlQueries['do_delete_coa_by_id'], array($id));
      return $result;
   }

   function DoDeleteCoaDataByArrayId($arrId) {
      $id_coa = implode("', '", $arrId);
      $result=$this->Execute($this->mSqlQueries['do_delete_coa_by_array_id'], array($id_coa));
      return $result;
   }
   
}

?>