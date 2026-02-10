<?php

class BukuBesar extends Database {

   protected $mSqlFile= 'module/tahun_pembukuan/business/buku_besar.sql.php';
   
   # subaccount
   var $subAccName;
   var $subAccJml;
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);   
      $this->subAccName    = array('Pertama','Kedua','Ketiga','Keempat','Kelima','Keenam','Ketujuh');
      $this->subAccJml     = ((GTFWConfiguration::GetValue('application','subAccJml') == NULL) ? 7 : GTFWConfiguration::GetValue('application','subAccJml'));
      //$this->mrDbEngine->debug = 1;
      //$this->SetDebugOn();
   }

   function InsertBukuBesar($params,$subAcc='') {
   	
   		$sql = $this->mSqlQueries['insert_buku_besar'];
   		
	   	if(!empty($subAcc)){
	   		$arrSubAcc = explode('-',$subAcc); $i=0;
	   		foreach($arrSubAcc as $val){
	   			$addSql .= ',`bbSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
	   			$i++;
	   		}
	   		$sql = str_replace('[INSERT_SUBACC]', $addSql, $sql);
	   	}else{$sql = str_replace('[INSERT_SUBACC]','', $sql);}
   	
      	return $this->Execute($sql, $params);
   }

   function UpdateBukuBesar($params,$subAcc='') {
   	$sql = $this->mSqlQueries['update_buku_besar_where_coa'];
   	if(!empty($subAcc)){
   		$arrSubAcc = explode('-',$subAcc); $i=0;
   		foreach($arrSubAcc as $val){
   			$addSql .= ' AND `bbSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
   			$i++;
   		}
   		$sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
   	}else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}
   	
      $result = $this->Execute($sql, $params);
      
      return $result;
   }

   	function InsertBukuBesarHistory($params,$subAcc='') {
      
   		$sql = $this->mSqlQueries['insert_buku_besar_history'];
   		
	   	if(!empty($subAcc)){
	   		$arrSubAcc = explode('-',$subAcc); $i=0;
	   		foreach($arrSubAcc as $val){
	   			$addSql .= ',`bbhisSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
	   			$i++;
	   		}
	   		$sql = str_replace('[INSERT_SUBACC]', $addSql, $sql);
	   	}else{$sql = str_replace('[INSERT_SUBACC]','', $sql);}
   	
      	$result = $this->Execute($sql, $params);
      
      	return $result;
  	}
   
   function UpdateBukuBesarHistory($params,$subAcc) {
      
   		$sql = $this->mSqlQueries['update_buku_besar_hist_where_coa'];
	   	if(!empty($subAcc)){
	   		$arrSubAcc = explode('-',$subAcc); $i=0;
	   		foreach($arrSubAcc as $val){
	   			$addSql .= ' AND `bbhisSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
	   			$i++;
	   		}
	   		$sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
	   	}else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}
   	
      $result = $this->Execute($sql, $params);

      return $result;
   }

   function GetBukuBesarHistoriAkhirFromCoa($params,$subAcc='') {
   	    $sql = $this->mSqlQueries['get_buku_besar_histori_akhir_from_coa'];
   		
   		if(!empty($subAcc)){
			$arrSubAcc = explode('-',$subAcc); $i=0;
			foreach($arrSubAcc as $val){
				$addSql .= ' AND `bbhisSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
				$i++;
			}
			$sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
		}else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}
   	
      return $this->Open($sql, array($params));
   }

   function GetBukuBesarFromCoa($params,$subAcc = '') {
   	
   		$sql = $this->mSqlQueries['get_buku_besar_from_coa'];
   		if(!empty($subAcc)){
			$arrSubAcc = explode('-',$subAcc); $i=0;
			foreach($arrSubAcc as $val){
				$addSql .= ' AND `bbSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
				$i++;
			}
			$sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
		}else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}
		
      return $this->Open($sql, array($params));
   }

   function UpdateTahunPeriodeBukuBesar($params) {
      return $this->Execute($this->mSqlQueries['update_tahun_periode_buku_besar'], array($params));
   }

   function UpdateTahunPeriodeBukuBesarHistoriIsNull($params) {
      return $this->Execute($this->mSqlQueries['update_tahun_periode_buku_besar_histori_is_null'], array($params));
   }
   
   function DeleteBukuBesarByCoaSubAccount($coaId,$subAcc){
   		$sql = $this->mSqlQueries['delete_buku_besar_by_coa_sub_account'];
   		
   		if(!empty($subAcc)){
   			$arrSubAcc = explode('-',trim($subAcc));
            //$i=0;
            for($i=0;$i<=($this->subAccJml-1);$i++){
			//foreach($arrSubAcc as $val){
                $subAccV = ( (empty($arrSubAcc[$i]) || !isset($arrSubAcc[$i])) ? '00' : $arrSubAcc[$i]);
				$addSql .= ' AND `bbSubacc'.$this->subAccName[$i]."Kode` = '".$subAccV."'";
			//	$i++;
			}
			$sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
		}else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}
   		
		return $this->Execute($sql,array($coaId));
   }
   
   function DeleteBukuBesarHistoryByCoaSubAccount($coaId,$subAcc){
   	$sql = $this->mSqlQueries['delete_buku_besar_history_by_coa_sub_account'];
   	 
   	if(!empty($subAcc)){
   		$arrSubAcc = explode('-',$subAcc);$i=0;
   		foreach($arrSubAcc as $val){
   			$addSql .= ' AND `bbhisSubacc'.$this->subAccName[$i]."Kode` = '".$val."'";
   			$i++;
   		}
   		$sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
   	}else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}
   	 
   	return $this->Execute($sql,array($coaId));
   }
   
   function GetPengali($coaId){
      $result = $this->Open($this->mSqlQueries['coa_pengali'],array($coaId));
      return $result[0];
   }
   
   function GetTanggal($coaId,$subAcc='')
   {
   		$sql = $this->mSqlQueries['get_tanggal'];
   		if(!empty($subAcc)){
   			$arrSubAcc = explode('-',$subAcc);$i=0;
   			foreach($arrSubAcc as $val){
   				$addSql .= ' AND `bbSubacc'.$this->subAccName[$i].'Kode` = "'.$val.'"';
   				$i++;
   			}
   			$sql = str_replace('[FILTER_SUBACC]', $addSql, $sql);
   		}else{$sql = str_replace('[FILTER_SUBACC]','', $sql);}
   		
        $tanggal = $this->Open($sql,array($coaId));
        
        if(empty($tanggal[0]['tanggal'])){
            $result = date('Y').'-01-01';
        } else {
            $result = $tanggal[0]['tanggal'];
        }
        return $result;
   }
}
?>
