<?php

class AppPopupKegiatanRef extends Database {

   protected $mSqlFile= 'module/usulan_kegiatan/business/apppopupkegiatanref.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);  
	  //$this->SetDebugOn();	  	  
   }
      
   function GetDataKegiatanRef ($offset, $limit, $kegiatanref='',$programId,$ik = '') 
   {
      $idSatker = Dispatcher::Instance()->Decrypt($_GET['idSatker']->Raw());
	  $idUnitKerja = Dispatcher::Instance()->Decrypt($_GET['idUnitKerja']->Raw());
	  //$programId =Dispatcher::Instance()->Decrypt($_GET['idProgram']->Raw());
	  if($idUnitKerja == "-"){
	       $unitId = $idSatker;
	  } else {
	       $unitId = $idUnitKerja;
      }
      if($ik != ''){
        $sql_ik =" kik.`ikNama` LIKE '%".$ik."%' AND ";
      } else {
        $sql_ik ='';
      }
      
      $sql = sprintf($this->mSqlQueries['get_data_kegiatanref'],
                                        $sql_ik,
                                        '%'.$kegiatanref.'%',
                                        $unitId, 
                                        $programId,
                                        $offset, 
                                        $limit);
      
      //$result = $this->Open($sql, array('%'.$kegiatanref.'%',$unitId, $programId,$offset, $limit));
	  //$debug = sprintf($sql, $subprogram,'%'.$kegiatanref.'%', $offset, $limit);
	  //echo $debug;
      $result = $this->Open($sql,array());
      return $result;
   }

   function GetCountDataKegiatanRef ($kegiatanref='') {
	  
	  $idSatker = Dispatcher::Instance()->Decrypt($_GET['idSatker']->Raw());
	  $idUnitKerja = Dispatcher::Instance()->Decrypt($_GET['idUnitKerja']->Raw());
	  $programId =Dispatcher::Instance()->Decrypt($_GET['idProgram']->Raw());
	  if($idUnitKerja == "-"){
		$unitId = $idSatker;
	  }
	  else{$unitId = $idUnitKerja;}
	  //echo $unitId;
	  
      $result = $this->Open($this->mSqlQueries['get_count_data_kegiatanref'], 
	  	array('%'.$kegiatanref.'%',$unitId,$programId));      
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }
   
    
}
?>
