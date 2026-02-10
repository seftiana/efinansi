<?php
class JurnalPengeluaran extends Database {
   protected $mSqlFile     = 'module/jurnal_pengeluaran/business/jurnalpengeluaran.sql.php';
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);
   }
   //==GET==
   function GetMinMaxTahunPencatatan(){
      $ret = $this->open($this->mSqlQueries['get_min_max_tahun_pencatatan'],array($start , $count));
      if($ret){
         return $ret[0];
      }else {
         $now_thn = date('Y');
         $thn['minTahun']  = $now_thn - 5 ;
         $thn['maxTahun']  = $now_thn + 5 ;
         return $thn;
     }
   }

   function GetData($start , $count, $tgl_awal, $tgl_akhir, $referensi, $posting="") {
      //echo sprintf($this->mSqlQueries['get_pembukuan_referensi'], $tgl_awal, $tgl_akhir, $start , $count);
      if($posting == 'all') $posting='';
      //
      $pembukuan_referensi = $this->open($this->mSqlQueries['get_pembukuan_referensi'], array($tgl_awal, $tgl_akhir, "%".$referensi."%", "%".$posting."%", $start , $count));
      //echo $this->GetLastError();
      for($i=0;$i<sizeof($pembukuan_referensi);$i++) {
         $arr_id[] = $pembukuan_referensi[$i]['id'];
      }
      $str_id = @implode("', '", $arr_id);
      //echo sprintf($this->mSqlQueries['get_data'], $str_id);

     $result = $this->open($this->mSqlQueries['get_data'],array($str_id));
     //echo $this->GetLastError();
     return $result;
   }

   function GetDataAll($start , $count) {
      //echo sprintf($this->mSqlQueries['get_pembukuan_referensi'], $tgl_awal, $tgl_akhir, $start , $count);
      $pembukuan_referensi = $this->open($this->mSqlQueries['get_pembukuan_referensi_all'], array($start , $count));//echo $this->GetLastError();
      for($i=0;$i<sizeof($pembukuan_referensi);$i++) {
         $arr_id[] = $pembukuan_referensi[$i]['id'];
      }
      $str_id = @implode("', '", $arr_id);
      //echo sprintf($this->mSqlQueries['get_data'], $str_id);
     return $this->open($this->mSqlQueries['get_data'],array($str_id));
   }

   function GetCount($tgl_awal, $tgl_akhir, $referensi, $posting="") {
      //echo sprintf($this->mSqlQueries['get_count'], $tgl_awal, $tgl_akhir);
      if($posting == 'all') $posting='';
     $tot = $this->open($this->mSqlQueries['get_count'],array($tgl_awal, $tgl_akhir, "%".$referensi."%", "%".$posting."%"));
     if(!empty($tot)) {
       return sizeof($tot);
     } else {
       return false;
     }
   }

   function GetCountAll() {
     $tot = $this->open($this->mSqlQueries['get_count_all'],array($tgl_awal, $tgl_akhir));
     if(!empty($tot)) {
       return sizeof($tot);
     } else {
       return false;
     }
   }

   function GetDataById($id){
      return $this->open($this->mSqlQueries['get_data_by_id'],array($id));
   }

   function GetComboCoa($type='all'){

     switch($type) {
        case 'kredit':
          $type ='0';
         break;

       case 'debet':
          $type ='1';
         break;

       case 'all':
          $type ='%%';
         break;
     }

      $ret=$this->open($this->mSqlQueries['get_combo_coa'],array($type));
      return $ret;
   }

   function GetBentukTransaksi(){
      return $this->open($this->mSqlQueries['get_bentuk_transaksi'],array());
   }

//===DO==
   function DoApproveJurnal($iskas, $bentuk_trans, $id_pr) {
         $exec = $this->Execute($this->mSqlQueries['do_approve'], array($iskas, $bentuk_trans, $id_pr));
      if($exec) {
         $sql = sprintf($this->mSqlQueries['do_approve'], $iskas, $bentuk_trans, $id_pr);
         $this->DoAddLog("Approve Jurnal", $sql);
      }
      return $exec;
   }

   function DoAddDetail($pembukuan_id,$coa_id, $nilai , $keterangan, $tipe , &$sql = ''){
     $ret =  $this->Execute($this->mSqlQueries['do_add_pembukuan_detail'], array($pembukuan_id,
                                                                                  $coa_id,
                                                                                    $nilai,
                                                                                  $keterangan,
                                                                                 $tipe
                                                                                 ));
    if($ret) {
       $sql = sprintf($this->mSqlQueries['do_add_pembukuan_detail'], $pembukuan_id,
                                                                     $coa_id,
                                                                     $nilai,
                                                                     $keterangan,
                                                                     $tipe
                                                                     );
    }


    return $ret;

   }

   function DoAdd($data,&$msgerr){
      $user_id       = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->StartTrans();
      $ok            = $this->Execute($this->mSqlQueries['do_add_pembukuan_referensi'], array(
         $data['referensi_id'],
         $user_id,
         date('Y-m-d'),
         $data['referensi_nama']
      ));
      $sql[]          = sprintf(
         $this->mSqlQueries['do_add_pembukuan_referensi'],
         $data['referensi_id'],
         $user_id,
         date('Y-m-d'),
         $data['referensi_nama']
      );

      $pembukuan_id  = $this->Insert_ID();
      if($ok) {
         $this->Execute($this->mSqlQueries['update_status_is_jurnal'], array($data['referensi_id']));
         $ok         = $this->DoAddDetail(
            $pembukuan_id,
            $data['kredit']['coa_id'],
            $data['kredit']['nilai'],
            $data['referensi_keterangan'],
            'K' ,
            $sqlret
         );
         $sql[] = $sqlret;
      }
      //else
      //  $ok = false;

      if($ok){
         if ((GTFWConfiguration::GetValue('application', 'auto_approve'))){
         $approve = $this->DoApproveJurnal($data['status_iskas'],$data['bentuk_transaksi'],$pembukuan_id);
            if ($approve){
               $ok = true;
            } else {
               $ok = false;
            }
         }
      }

      if($ok){
         //tambah data akun detail debet
         //print_r($data['debet']);
         if(!empty($data['debet']['tambah'])) {
            foreach($data['debet']['tambah'] as $val){
               $ok = $this->DoAddDetail(
                  $pembukuan_id,
                  $val['id'],
                  $val['nilai'],
                  $val['keterangan'],
                  'D',
                  $sqlret
               );
               $sql[] = $sqlret;
               if(!$ok) {
                  $msgerr['debet']['id'][] = $val['id'];
                  $msgerr['debet']['msg'] .= $val['nama'].', ';
                  //$ok = false;
                  break;
               }
            }//end foreach
         } //end if array debet
      } else{
         $msgerr  = " jurnal ";
      }

     //$ok=true;
     $this->EndTrans($ok);

      if($ok){
         $this->DoAddLog('Tambah Jurnal Pengeluaran', $sql);
      }
      return $ok;
   }

   function DoUpdate($data,&$msgerr){
      $user_id=Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->StartTrans();

     $ok = $this->Execute($this->mSqlQueries['do_update_pembukuan_referensi'], array($data['referensi_id'],
                                                                                     $user_id,
                                                                                     $data['referensi_nama'],
                                                                                      $data['pembukuan_referensi_id']
                                                                                    ));

      $sql[] = sprintf($this->mSqlQueries['do_update_pembukuan_referensi'],$data['referensi_id'],
                                                                          $user_id,
                                                                          $data['referensi_nama'],
                                                                           $data['pembukuan_referensi_id']);
      //print_r($sql);
      //echo $ok;
     //update kreditnya
     if($ok) {
        //echo "update kredit";
        $ok = $this->DoUpdateDetail($data['kredit']['coa_id'],
                                   $data['kredit']['nilai'],
                            $data['referensi_keterangan'],
                            $data['kredit']['detail_id'] , $sqlret
                                  );
        $sql[] = $sqlret;
     } else {
        $ok = false;
     }
     //echo $ok;
     //print_r($data);

     if($ok) {
       //hapus
       if(is_array($data['akun_list_delete']['id'])) {
          $str_id = implode("', '", $data['akun_list_delete']['id']);
          $ok = $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail_by_array_id'], array($str_id));
          if($ok) {
            $sql[] = sprintf($this->mSqlQueries['do_delete_pembukuan_detail_by_array_id'], $str_id);
          }
       }

       //update data akun detail debet
       if(is_array($data['debet']['tambah_edit'])) {
          foreach($data['debet']['tambah_edit'] as $val){
            $detok = $this->DoUpdateDetail($val['id'],
                                    $val['nilai'],
                                    $val['keterangan'],
                                   $val['detail_id'], $sqlret
                                          );
             $sql[] = $sqlret;

            if(!$detok) {
               $msgerr['debet']['id'][] = $val['id'];
              $msgerr['debet']['msg'] .= $val['keterangan'].', ';
                  $ok = false;
                  break;
            }
         }//end foreach
       } //end if array debet

       //tambah
       if(is_array($data['debet']['tambah'])) {
          foreach($data['debet']['tambah'] as $val){
            $detok = $this->DoAddDetail($data['pembukuan_referensi_id'],
                                        $val['id'],
                                 $val['nilai'],
                                 $val['keterangan'],
                                 'D',$sqlret
                                       );
            $sql[] = $sqlret;
            if(!$detok) {
               $msgerr['debet']['id'][] = $val['id'];
              $msgerr['debet']['msg'] .= $val['nama'].', ';
                  $ok = false;
                  break;
            }
         }//end foreach
       } //end if array debet

     } else {
       $msgerr = " jurnal ";
     }

     //print_r($sql);
     //$ok=true;
     $this->EndTrans($ok);
     if($ok)
        $this->DoAddLog('Update Jurnal Pengeluaran',$sql);
     return $ok;
   }

   function DoUpdateDetail($coa_id, $nilai , $keterangan, $detail_id , &$sql =''){
     $ret=  $this->Execute($this->mSqlQueries['do_update_pembukuan_detail'], array($coa_id,
                                                               $nilai,
                                                                                  $keterangan,
                                                               $detail_id
                                                                                 ));
     if($ret) {
      $sql = sprintf($this->mSqlQueries['do_update_pembukuan_detail'],$coa_id,
                                                      $nilai,
                                                                      $keterangan,
                                                      $detail_id);
    }
    //echo $sql;
    return $ret;
   }

   function DoDelete($id){
    $this->StartTrans();
   $ok=$this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array($id));

   $sql[] = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $id);

   if($ok)
      $ok = $this->Execute($this->mSqlQueries['do_delete_pembukuan_referensi'], array($id));

    $sql[] = sprintf($this->mSqlQueries['do_delete_pembukuan_referensi'], $id);

   $this->EndTrans($ok);
   if($ok)
      $this->DoAddLog('Delete Jurnal Pengeluaran',$sql);


   return $ok;
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

   //LOGGER LOGGER LOGGER

   function DoAddLog($keterangan, $query) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $ip = $this->GetRealIP();
      $result = $this->Execute($this->mSqlQueries['do_add_log'], array($userId, $ip, $keterangan));

     $id_logger = $this->LastInsertId();

      if(is_array($query)) {
       foreach($query as $val) {
        $this->DoAddLogDetil($id_logger, $val);
      }

     } else
       $this->DoAddLogDetil($id_logger, $query);
      return $result;
   }

   function DoAddLogDetil($id, $query) {
      $result = $this->Execute($this->mSqlQueries['do_add_log_detil'], array($id, addslashes($query)));
      return $result;
   }

   function GetRealIP(){
      if ($_ENV["HTTP_CLIENT_IP"]) $ip = $_ENV["HTTP_CLIENT_IP"];
      elseif ($_ENV["HTTP_X_FORWARDED_FOR"]) $ip = $_ENV["HTTP_X_FORWARDED_FOR"];
      elseif ($_ENV["HTTP_X_FORWARDED"]) $ip = $_ENV["HTTP_X_FORWARDED"];
      elseif ($_ENV["HTTP_FORWARDED_FOR"]) $ip = $_ENV["HTTP_FORWARDED_FOR"];
      elseif ($_ENV["HTTP_FORWARDED"]) $ip = $_ENV["HTTP_FORWARDED"];
      elseif ($_SERVER['REMOTE_ADDR']) $ip = $_SERVER['REMOTE_ADDR'];
      return $ip;
   }

   function GetMaxIdPembukuanRef(){
     $ret = $this->open($this->mSqlQueries['get_max_pembukuan_referensi_id'],array());
     return $ret[0]['max_id'];
   }

   function UpdateStatusJurnal($id_trans) {
      return $this->Execute($this->mSqlQueries['update_status_jurnal'],array($id_trans));
   }

   function GetDataJurnalBalik($id_pemb) {
      return $this->open($this->mSqlQueries['get_data_jurnal_balik'],array($id_pemb));
   }

   function CekAkunBukuBesar($coa_id) {
      $result = $this->Open($this->mSqlQueries['cek_akun_buku_besar'], array($coa_id));
      return $result[0];
   }

   function DoInsertBukuBesar($coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_buku_besar'], array($coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId));
      $sql = sprintf($this->mSqlQueries['do_insert_buku_besar'], $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);
      if($result)
         $this->DoAddLog('Insert Buku Besar', $sql);
      #echo $sql;
      return $result;
   }

   function DoUpdateBukuBesar($coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $bb_id) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_update_buku_besar'], array($coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id));
      $sql = sprintf($this->mSqlQueries['do_update_buku_besar'], $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id);
      if($result)
         $this->DoAddLog('Update Buku Besar', $sql);
      return $result;
   }

   function DoInsertBukuBesarHis($pemb_ref_id, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_buku_besar_his'], array($pemb_ref_id, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId));
      $sql = sprintf($this->mSqlQueries['do_insert_buku_besar_his'], $pemb_ref_id, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);
      if($result)
         $this->DoAddLog('Insert Buku Besar History', $sql);
      return $result;
   }

   function DoInsertLabaRugiBukuBesar($saldo_awal, $debet, $kredit, $saldo, $saldo_akhir) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_laba_rugi_buku_besar'], array($userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId));
      $sql = sprintf($this->mSqlQueries['do_insert_laba_rugi_buku_besar'], $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);
      if($result)
         $this->DoAddLog('Insert Labarugi Buku Besar', $sql);
      #echo $sql;
      return $result;
   }

   function DoUpdateLabaRugiBukuBesar($saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $bb_id) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_update_laba_rugi_buku_besar'], array($userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id));
      $sql = sprintf($this->mSqlQueries['do_update_laba_rugi_buku_besar'], $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id);
      if($result)
         $this->DoAddLog('Update Labarugi Buku Besar', $sql);
      return $result;
   }

   function DoInsertLabaRugiBukuBesarHis($pemb_ref_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_laba_rugi_buku_besar_his'], array($pemb_ref_id, $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId));
      $sql = sprintf($this->mSqlQueries['do_insert_laba_rugi_buku_besar_his'], $pemb_ref_id, $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);
      if($result)
         $this->DoAddLog('Insert Labarugi Buku Besar History', $sql);
      return $result;
   }

   function GetCoaLabaRugi() {
      $result = $this->Open($this->mSqlQueries['get_coa_laba_rugi'], array());
      return $result;
   }

   function CekAkunLabaRugiBukuBesar() {
      $result = $this->Open($this->mSqlQueries['cek_akun_laba_rugi_buku_besar'], array());
      return $result[0];
   }

   function UpdateStatusPostingBalikPembukuanRef($pr_id) {
      $result = $this->Execute($this->mSqlQueries['update_status_posting_balik_pembukuan_ref'], array($pr_id));
      return $result;
   }

   function BalikJurnal($id_pembukuan_ref) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $this->StartTrans();
      $jurnal_lama = $this->GetDataById($id_pembukuan_ref);

      //pemb ref
      $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_referensi'], array($jurnal_lama[0]['referensi_id'], $userId, date('Y-m-d'), $jurnal_lama[0]['referensi_nama']));

      $sql[] = sprintf($this->mSqlQueries['do_add_pembukuan_referensi'], $jurnal_lama[0]['referensi_id'], $userId, date('Y-m-d'), $jurnal_lama[0]['referensi_nama']);

      $pembukuan_id = $this->GetMaxIdPembukuanRef();
      #echo 'pembukuan id = '.$pembukuan_id; print_r($sql); exit;
      //pemb detil
      if($ok) {
         $this->Execute($this->mSqlQueries['update_status_posting_saat_jurnal_balik'], array($id_pembukuan_ref));
         $this->UpdateStatusJurnal($jurnal_lama[0]['referensi_id']);
         for($i=0; $i<sizeof($jurnal_lama); $i++) {
            if($jurnal_lama[$i]['detail_status'] == 'D')
               $tipe_balik = 'K';
            elseif($jurnal_lama[$i]['detail_status'] == 'K')
               $tipe_balik = 'D';

            $ret =  $this->Execute($this->mSqlQueries['do_add_pembukuan_detail'], array($pembukuan_id, $jurnal_lama[$i]['coa_id'], $jurnal_lama[$i]['detail_nilai'], $jurnal_lama[$i]['detail_keterangan'], $tipe_balik));
            if($ret) {
               $sql_detil[$i] = sprintf($this->mSqlQueries['do_add_pembukuan_detail'], $pembukuan_id, $jurnal_lama[$i]['coa_id'], $jurnal_lama[$i]['detail_nilai'], $jurnal_lama[$i]['detail_keterangan'], $tipe_balik);
               $this->DoAddLog('Insert Detil Jurnal Balik Penerimaan',$sql_detil[$i]);
            }

         }

         #get data pembukuan hasil jurnal balik untuk langusng di posting
         $data_jurnal_balik = $this->GetDataJurnalBalik($pembukuan_id);
         //proses posting jurnal balik
         if(!empty($data_jurnal_balik)) {
            for($i=0; $i<count($data_jurnal_balik); $i++) {
               if(strtoupper($data_jurnal_balik[$i]['status_pembukuan']) == 'D') {
                  $debet = $data_jurnal_balik[$i]['nilai'];
                  $kredit = 0;
                  $kredit_lr = -$data_jurnal_balik[$i]['nilai'];
               }elseif(strtoupper($data_jurnal_balik[$i]['status_pembukuan']) == 'K') {
                  $debet = 0;
                  $kredit = $data_jurnal_balik[$i]['nilai'];
                  $kredit_lr = $data_jurnal_balik[$i]['nilai'];
               }

               $cek_akun_from_bb = $this->CekAkunBukuBesar($data_jurnal_balik[$i]['coa_id']);

               if(!empty($cek_akun_from_bb['bb_id'])) { #echo 'tes'; exit;
                  if($data_jurnal_balik[$i]['coa_status_debet'] == 1)
                     $saldo = $debet - $kredit;
                  elseif($data_jurnal_balik[$i]['coa_status_debet'] == 0)
                     $saldo = $kredit - $debet;

                  $saldo_awal = $cek_akun_from_bb['saldo_akhir'];
                  $saldo_akhir = $saldo_awal + $saldo;

                  //update buku besar, karena akun coa nya sudah ada
                  $update_bb = $this->DoUpdateBukuBesar($data_jurnal_balik[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $cek_akun_from_bb['bb_id']);
                  //insert buku besar hystory
                  $insert_bb_his = $this->DoInsertBukuBesarHis($data_jurnal_balik[$i]['pembukuan_ref_id'], $data_jurnal_balik[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir);
               } else {
                  //insert bb disini
                  if($data_jurnal_balik[$i]['coa_status_debet'] == 1)
                     $saldo = $debet - $kredit;
                  elseif($data_jurnal_balik[$i]['coa_status_debet'] == 0)
                     $saldo = $kredit - $debet;

                  $saldo_awal = 0;
                  $saldo_akhir = $saldo_awal + $saldo;
                  $insert_bb = $this->DoInsertBukuBesar($data_jurnal_balik[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir);
                  //insert buku besar hystory
                  $insert_bb_his = $this->DoInsertBukuBesarHis($data_jurnal_balik[$i]['pembukuan_ref_id'], $data_jurnal_balik[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir);
               }
               //update status is posting di pembukuan_referensi
               $this->UpdateStatusPostingBalikPembukuanRef($data_jurnal_balik[$i]['pembukuan_ref_id']);

               //cek coa laba ditahan
               $list_coa_laba_rugi = $this->GetCoaLabaRugi(); #print_r($list_coa_laba_rugi); exit;
               for($l=0; $l<sizeof($list_coa_laba_rugi); $l++) {
                  $arr_coa[] = $list_coa_laba_rugi[$l]['coaKelompokId'];
               }
               if(in_array($data_jurnal_balik[$i]['coa_kelompok'], $arr_coa)) {
                  $cek_akun_laba_rugi_from_bb = $this->CekAkunLabaRugiBukuBesar();
                  if(!empty($cek_akun_laba_rugi_from_bb['bb_id'])) {
                     $saldo_awal_lr = $cek_akun_laba_rugi_from_bb['saldo_akhir'];
                     $saldo_akhir_lr = $saldo_awal_lr + ($kredit_lr-0);
                     //proses insert bukubesar untuk coa laba rugi
                     $update_labarugi_bb = $this->DoUpdateLabaRugiBukuBesar($saldo_awal_lr, 0, $kredit_lr, $kredit_lr-0, $saldo_akhir_lr, $cek_akun_laba_rugi_from_bb['bb_id']);
                     $insert_labarugi_bb_his = $this->DoInsertLabaRugiBukuBesarHis($data_jurnal_balik[$i]['pembukuan_ref_id'], $saldo_awal_lr, 0, $kredit_lr, $kredit_lr-0, $saldo_akhir_lr);
                  }else{
                     $saldo_awal_lr = 0;
                     $saldo_akhir_lr = $saldo_awal_lr + ($kredit_lr-0);
                     $insert_labarugi_bb = $this->DoInsertLabaRugiBukuBesar($saldo_awal_lr, 0, $kredit_lr, $kredit_lr-0, $saldo_akhir_lr);
                     //insert buku besar hystory
                     $insert_labarugi_bb_his = $this->DoInsertLabaRugiBukuBesarHis($data_jurnal_balik[$i]['pembukuan_ref_id'], $saldo_awal_lr, 0, $kredit_lr, $kredit_lr-0, $saldo_akhir_lr);
                  }


               }
            }
         }
         //end of posting jurnal balik
      }else
         $ok = false;

      $this->EndTrans($ok);
      if($ok)
         $this->DoAddLog('Insert Jurnal Balik Penerimaan',$sql);
      return $ok;
   }

   function UpdateStatusJurnalSetelahDelete($status, $prId) {
      $result = $this->Execute($this->mSqlQueries['update_status_is_jurnal_ketika_delete'], array($status, $prId));
      return $result;
   }
}
?>
