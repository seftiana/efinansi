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

   function UpdateBukuBesarTutupBuku($params,$subAcc='') {
   	$sql = $this->mSqlQueries['update_buku_besar_where_coa_tutup_buku'];
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

	/**
	 * untuk membuat tutup buku RL
	 */
   	function InsertBukuBesarHistoryTutupBuku($params,$subAcc='') {
      
   		$sql = $this->mSqlQueries['insert_buku_besar_history_tutup_buku'];
   		
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

   function DoSaveJurnalTutupBukuRL($params, $subAcc, $no) {
	    // get user loggin
		$getUser = $this->Open($this->mSqlQueries['get_user'],array($params['user_id']));
		// get tahun anggaran aktif		
		$getTa = $this->Open($this->mSqlQueries['get_ta_aktif'], array());
		//get coa 
		$getCoaDebetId = $this->Open($this->mSqlQueries['get_coa'], array($params['coa_debet_id']));
		$getCoaKreditId = $this->Open($this->mSqlQueries['get_coa'], array($params['coa_kredit_id']));
		$noref = date('Ymd',strtotime($params['tgl_trans'])).'.'. $getCoaDebetId[0]['coaKodeAkun'].'.'.$getCoaKreditId[0]['coaKodeAkun'].'.'.strtotime("now").$no;

		$catatanTrans = 'Tutup Buku - '.$getCoaDebetId['0']['coaNamaAkun'].' '.$subAcc;
		// create transaction   			
		$result = true;		
		$this->StartTrans();
      	$result = $this->Execute($this->mSqlQueries['do_insert_transaksi'], array( 
			'3', /* tipe umum */
			'1', /* jenis umum */
			'1', /* unit id */
			$params['tp_id'],
			$getTa[0]['thanggarId'],
			$noref,
			$params['user_id'],
			$params['tgl_trans'],
			$params['tgl_trans'],
			$params['tgl_trans'],
			$catatanTrans,
			$params['nominal'],
			$getUser[0]['RealName'],
			''
		));
		$transaksiId   = $this->LastInsertId();

		// memindahkan coa berjalan(penampung rl) ke coa aktiva bersih
		$result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_referensi'], array(
			$transaksiId ,
			$params['user_id'],
			$params['tgl_trans'],
			$catatanTrans,
		));

		list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7)   = explode('-', $subAcc);

		$pembukuanId   = $this->LastInsertId();
		// debet
		$result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
			$pembukuanId,
			$params['coa_debet_id'],
			$params['nominal'],
			$catatanTrans,
			'',
			'D',
			$subacc_1,
			$subacc_2,
			$subacc_3,
			$subacc_4,
			$subacc_5,
			$subacc_6,
			$subacc_7
		));

		$pdDebetId   = $this->LastInsertId();
		$param_lr_berjalan_his = $params['param_lr_berjalan_his'];
		array_push($param_lr_berjalan_his, $pembukuanId,$pdDebetId);
		$result &= $this->InsertBukuBesarHistoryTutupBuku($param_lr_berjalan_his,$subAcc);
		// kredit
		$result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
			$pembukuanId,
			$params['coa_kredit_id'],
			$params['nominal'],
			$catatanTrans,
			'',
			'K',
            $subacc_1,
            $subacc_2,
            $subacc_3,
            $subacc_4,
            $subacc_5,
            $subacc_6,
            $subacc_7
		));
		$pdKreditId   = $this->LastInsertId();
		$param_lr_awal_tahun_his = $params['param_lr_awal_tahun_his'];
		array_push($param_lr_awal_tahun_his, $pembukuanId,$pdKreditId);
		$result &= $this->InsertBukuBesarHistoryTutupBuku($param_lr_awal_tahun_his,$subAcc);
		$result &= $this->UpdateBukuBesarTutupBuku($params['param_lr_awal_tahun'], $subAcc);
		$result &= $this->UpdateBukuBesarTutupBuku($params['param_lr_berjalan'],  $subAcc);
		// end 
		return $this->EndTrans($result);
   }
}
?>
