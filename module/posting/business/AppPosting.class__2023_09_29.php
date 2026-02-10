<?php
class AppPosting extends Database {

   protected $mSqlFile;
   protected $mUserId = NULL;
   public $_POST;
   public $_GET;
   public $method;

   function __construct($connectionNumber=0) {
      $this->mSqlFile   = 'module/posting/business/appposting.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);
   }

   private function setUserId()
   {
      if(class_exists('Security')){
         $this->mUserId    = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      }
   }

   public function getUser()
   {
      $this->setUserId();
      return (int)$this->mUserId;
   }

   /**
    * getCountJurnalPosting
    * untuk mengetahui jumlah jurnal yang sudah terposting
    * @return int
    */
   public function getCountJurnalPosting(){
        $total = 0;
        $return = $this->Open($this->mSqlQueries['get_count_jurnal_terposting'], array());
        if(!empty($return)) {
            $total = $return[0]['total_rows'];
        }
        return $total;
   }
   
   /**
    * getStatePosting
    * untuk mengetahui apakah sudah pernah melakukan posting
    * @return int
    */
   public function getStatePosting(){
        $statePosting = $this->Open($this->mSqlQueries['get_last_posting'], array());
        if($statePosting[0]['lastPosting'] === NULL){
            $statePosting = 0;
        }else{
            $statePosting = 1;
        }        
        return $statePosting;
   }
   /**
    * @description Get date last_posting; last_transaksi
    */
   public function getParameterDate()
   {
      $getDate       = getdate();
      $currmon       = (int)$getDate['mon'];
      $currYear      = (int)$getDate['year'];
      $currday       = (int)$getDate['mday'];
      $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
      $posting       = $this->Open($this->mSqlQueries['get_last_posting'], array());
      $jurnal        = $this->Open($this->mSqlQueries['get_last_transaksi'], array());

      
      if(empty($jurnal) OR $jurnal[0]['lastTransaksi'] === NULL){
         $return['last_transaksi']  = $return['firstTransaksi'] = date('Y-m-d', mktime(0,0,0, $currmon, $currday, $currYear));
      } else {
         $return['last_transaksi']  = date('Y-m-d', strtotime($jurnal[0]['lastTransaksi']));
         $return['first_transaksi']  = date('Y-m-d', strtotime($jurnal[0]['firstTransaksi']));
      }

      if($posting[0]['lastPosting'] === NULL){
         // $lastPosting      = date('Y-m-d', mktime(0,0,0, $currmon, $currday, $currYear));
         $lastPosting      = $return['first_transaksi'];
      }else{
         $lastPosting      = date('Y-m-d', strtotime($posting[0]['lastPosting']));
      }
      $return['last_posting']    = $lastPosting;

      
      return $return;
   }

   public function getJurnalDueDate($last_posting, $curr_date)
   {
      // pastikan format tanggal sesuai
      $last_posting     = date('Y-m-d', strtotime($last_posting));
      $curr_date        = date('Y-m-d', strtotime($curr_date));
      // set default tahun periode dan tahun pembukuan
      $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      // $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      // $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());

      $return     = $this->Open($this->mSqlQueries['get_data_jurnal_due_date'], array(
         $last_posting,
         $curr_date,
         $last_posting
      ));

      return $return;
   }

   public function countJurnalDueDate($last_posting, $curr_date)
   {
      // pastikan format tanggal sesuai
      $last_posting     = date('Y-m-d', strtotime($last_posting));
      $curr_date        = date('Y-m-d', strtotime($curr_date));
      // set default tahun periode dan tahun pembukuan
      $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      // $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      // $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());

      $return     = $this->Open($this->mSqlQueries['count_jurnal_due_date'], array(
         $last_posting,
         $curr_date,
         $last_posting
      ));

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getJurnalDetail($transId, $pembukuanId)
   {
      $return     = $this->Open($this->mSqlQueries['get_jurnal_detail'], array(
         $transId,
         $pembukuanId
      ));

      return $return[0];
   }

   public function doUpdateJurnal($param = array())
   {
      $userId     = $this->getUser();
      $ipAddress  = $this->GetRealIP();
      $queryLog   = array();
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      $result     &= $this->Execute($this->mSqlQueries['do_update_pembukuan'], array(
         date('Y-m-d', strtotime($param['tanggal'])),
         $param['pembukuan_id']
      ));
      $result     &= $this->Execute($this->mSqlQueries['do_update_transaksi'], array(
         date('Y-m-d', strtotime($param['tanggal'])),
         $param['transaksi_id']
      ));

      $queryLog[] = sprintf($this->mSqlQueries['do_update_pembukuan'], date('Y-m-d', strtotime($param['tanggal'])), $param['pembukuan_id']);
      $queryLog[] = sprintf($this->mSqlQueries['do_update_transaksi'], date('Y-m-d', strtotime($param['tanggal'])), $param['transaksi_id']);

      $result  &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Update jurnal - posting'
      ));
      $loggerId   = $this->LastInsertId();
      foreach ($queryLog as $query) {
         $result  &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
            $loggerId,
            addslashes($query)
         ));
      }

      return $this->EndTrans($result);
   }

   public function getJurnalPembukuan($last_posting, $curr_date)
   {
      // pastikan format tanggal sesuai
      $last_posting     = date('Y-m-d', strtotime($last_posting));
      $curr_date        = date('Y-m-d', strtotime($curr_date));
      // set default tahun periode dan tahun pembukuan
      $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
      
      /**
       * get data yang berada dalam tahun pembukuan aktif saja
       */
      $return           = $this->Open($this->mSqlQueries['get_pembukuan_jurnal_coa'], array(
         $last_posting,
         $curr_date,
         $last_posting
      ));

      return self::ChangeKeyName($return, 'lower');
   }

   public function getDataPembukuanJurnal($last_posting, $curr_date)
   {
      // pastikan format tanggal sesuai
      $last_posting     = date('Y-m-d', strtotime($last_posting));
      $curr_date        = date('Y-m-d', strtotime($curr_date));
      // set default tahun periode dan tahun pembukuan
      $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
      $return           = $this->Open($this->mSqlQueries['get_data_pembukuan_jurnal'], array(
         $last_posting,
         $curr_date
      ));

      return self::ChangeKeyName($return);
   }

   public function getCoaLabaRugi()
   {
      $this->Execute($this->mSqlQueries['set_coa_laba_rugi'], array());
      $this->Execute($this->mSqlQueries['do_set_coa_laba_rugi'], array());
      $dataCoa       = $this->Open($this->mSqlQueries['get_set_coa_laba_rugi'], array());
      if($dataCoa && !empty($dataCoa)){
         if(is_null($dataCoa[0]['coa_id'])){
            return false;
         }else{
            return true;
         }
      }else{
         return false;
      }
   }

   public function getPembukuanLabaRugi()
   {
      $this->Execute($this->mSqlQueries['set_coa_laba_rugi'], array());
      $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      $this->Execute($this->mSqlQueries['do_set_coa_laba_rugi'], array());
      $dataBukuBesar       = $this->Open($this->mSqlQueries['get_pembukuan_laba_rugi'], array());

      if(empty($dataBukuBesar)){
         $return['id']           = null;
         $return['saldo_awal']   = 0;
         $return['saldo_akhir']  = 0;
      }else{
         $return['id']           = $dataBukuBesar[0]['bb_id'];
         $return['saldo_awal']   = $dataBukuBesar[0]['bb_id'];
         $return['saldo_akhir']  = $dataBukuBesar[0]['saldo_akhir'];
      }

      return $return;
   }

   public function doPosting($curr_date)
   {
      $date             = date('Y-m-d', strtotime($curr_date));
      $dateParameter    = $this->getParameterDate();
      $last_posting     = $dateParameter['last_posting'];
      $ipAddress        = $this->GetRealIP();
      $userId           = $this->getUser();
      $queryLog         = array();
      $result           = true;
      $this->StartTrans();

      // generate data buku besar
      $dataJurnal       = $this->getJurnalPembukuan(
         $last_posting,
         $date
      );
      // $dataPembukuan    = $this->getDataPembukuanJurnal(
      //    $last_posting,
      //    $date
      // );

      $pembukuanLabaRugi   = $this->getPembukuanLabaRugi();
      $dataJurnalCoa       = array();
      $dataJurnalPembukuan = array();
      $dataLabaRugi        = array();
      $dataJurnalLabaRugi  = array();
      $dataPembukuanLabaRugi  = array();
      
      if(!empty($dataJurnal)){
         $coaId            = '';
         $index            = 0;
         $saldo            = array();
         $saldoAwal        = 0;
         $saldoAkhir       = 0;
         $index            = 0;
         for ($i=0; $i < count($dataJurnal);) {
            if((int)$coaId === (int)$dataJurnal[$i]['akun_id']){
                
               $debet      = $dataJurnal[$i]['nominal_debet'];
               $kredit     = $dataJurnal[$i]['nominal_kredit'];
               
               //$saldo      = ($dataJurnal[$i]['nominal_debet']-$dataJurnal[$i]['nominal_kredit']);
               /**
                * Untuk mengaktif kan status debet dan kredit dari coa table coa
                */
               /*
                if(strtoupper($dataJurnal[$i]['akun_kelompok']) == 'PENDAPATAN'){ 
                    //default saldo kredit
                    //jika pendapatan retur maka minus pendapatan
                    $saldo   = ($dataJurnal[$i]['nominal_kredit'] - $dataJurnal[$i]['nominal_debet']);
                } else {
               */
                    if($dataJurnal[$i]['status_debet'] == 0){
                       $saldo   = ($dataJurnal[$i]['nominal_kredit'] - $dataJurnal[$i]['nominal_debet']);
                    }else{
                       $saldo   = ($dataJurnal[$i]['nominal_debet'] - $dataJurnal[$i]['nominal_kredit']);
                    }
               
               /*
               }
               */
               $dataJurnalCoa[$coaId]['saldo_awal']   = $saldoAwalTahun;
               $dataJurnalCoa[$coaId]['debet']        += $debet;
               $dataJurnalCoa[$coaId]['kredit']       += $kredit;
               $dataJurnalCoa[$coaId]['saldo']        = $saldo;
               $dataJurnalCoa[$coaId]['saldo_akhir']  = $saldoAwal+$saldo;

               // set data jurnal untuk buku besar history
               $dataJurnalPembukuan[$i]['id']            = $dataJurnal[$i]['pembukuan_id'];
               $dataJurnalPembukuan[$i]['buku_id']       = $dataJurnal[$i]['pembukuan_detail_id'];
               $dataJurnalPembukuan[$i]['coa_id']        = $dataJurnal[$i]['akun_id'];
               $dataJurnalPembukuan[$i]['coa_kode']      = $dataJurnal[$i]['akun_kode'];
               $dataJurnalPembukuan[$i]['coa_nama']      = $dataJurnal[$i]['akun_nama'];
               $dataJurnalPembukuan[$i]['sub_account']   = $dataJurnal[$i]['sub_account'];
               // pengelolaan nominal
               $dataJurnalPembukuan[$i]['saldo_awal']    = $saldoAwal;
               $dataJurnalPembukuan[$i]['debet']         = $debet;
               $dataJurnalPembukuan[$i]['kredit']        = $kredit;
               $dataJurnalPembukuan[$i]['saldo']         = $saldo;
               $dataJurnalPembukuan[$i]['saldo_akhir']   = $saldoAwal+$saldo;

               if(strtoupper($dataJurnal[$i]['akun_kelompok']) == 'PENDAPATAN'
                  OR strtoupper($dataJurnal[$i]['akun_kelompok']) == 'BIAYA'){
                  $dataLabaRugi[$index]   = $dataJurnal[$i];
                  $index++;
               }
               $i++;
               $saldoAwal     += $saldo;
            }else{
               $coaId         = (int)$dataJurnal[$i]['akun_id'];
               // $bukuBesar     = $this->getDatabukuBesarCoa($coaId);
               $saldoAwal     = 0;
               $saldoAwalTahun = 0;
               $saldoAkhir    = 0;
               $bbId          = $dataJurnal[$i]['bb_id'];

               if($bbId !== NULL){
                  $saldoAwal  = $dataJurnal[$i]['saldo_akhir'];
                  $saldoAwalTahun = $dataJurnal[$i]['saldo_akhir'];
                  $saldoAkhir = 0;
               }
               $dataJurnalCoa[$coaId]['bb_id']        = $bbId;
               $dataJurnalCoa[$coaId]['akun_id']      = $dataJurnal[$i]['akun_id'];
               $dataJurnalCoa[$coaId]['akun_kode']    = $dataJurnal[$i]['akun_kode'];
               $dataJurnalCoa[$coaId]['akun_nama']    = $dataJurnal[$i]['akun_nama'];
               $dataJurnalCoa[$coaId]['sub_account']  = $dataJurnal[$i]['sub_account'];
               $dataJurnalCoa[$coaId]['debet']        = 0;
               $dataJurnalCoa[$coaId]['kredit']       = 0;
               $dataJurnalCoa[$coaId]['saldo']        = 0;
               $dataJurnalCoa[$coaId]['saldo_awal']   = 0;
               $dataJurnalCoa[$coaId]['saldo_akhir']  = 0;
            }
         }
      }

      /*if(!empty($dataPembukuan)){
         $refId      = '';
         $index      = 0;
         for ($i=0; $i < count($dataPembukuan);) {
            if((int)$refId === (int)$dataPembukuan[$i]['pembukuan_id']){
               $debet      = $dataPembukuan[$i]['nominal_debet'];
               $kredit     = $dataPembukuan[$i]['nominal_kredit'];
               $saldo      = ($debet-$kredit);
               $dataJurnalPembukuan[$i]['id']            = $dataPembukuan[$i]['pembukuan_id'];
               $dataJurnalPembukuan[$i]['buku_id']       = $dataPembukuan[$i]['pembukuan_detail_id'];
               $dataJurnalPembukuan[$i]['coa_id']        = $dataPembukuan[$i]['akun_id'];
               $dataJurnalPembukuan[$i]['coa_kode']      = $dataPembukuan[$i]['akun_kode'];
               $dataJurnalPembukuan[$i]['coa_nama']      = $dataPembukuan[$i]['akun_nama'];
               $dataJurnalPembukuan[$i]['sub_account']   = $dataPembukuan[$i]['sub_account'];
               // pengelolaan nominal
               $dataJurnalPembukuan[$i]['saldo_awal']    = $saldoAwal;
               $dataJurnalPembukuan[$i]['debet']         = $debet;
               $dataJurnalPembukuan[$i]['kredit']        = $kredit;
               $dataJurnalPembukuan[$i]['saldo']         = $saldo;
               $dataJurnalPembukuan[$i]['saldo_akhir']   = $saldoAwal+$saldo;
               $i++;
               $saldoAwal     += $saldo;
            }else{
               $refId         = (int)$dataPembukuan[$i]['pembukuan_id'];
               $saldoAwal     = 0;
               $saldoAkhir    = 0;
            }
         }
      }*/
      /**
       * perhitungan akumulasi rugi laba tahun berjalan
       */
      if(!empty($dataLabaRugi)){
         $saldoAwal     = $saldoAwalLabaRugi   = $pembukuanLabaRugi['saldo_akhir'];
         $saldoAkhir    = 0;
         $index         = 0;
         $dataJurnalLabaRugi['id']           = $pembukuanLabaRugi['id'];
         $dataJurnalLabaRugi['saldo_awal']   = $pembukuanLabaRugi['saldo_akhir'];
         $dataJurnalLabaRugi['debet']        = 0;
         $dataJurnalLabaRugi['kredit']       = 0;
         $dataJurnalLabaRugi['saldo']        = 0;
         $dataJurnalLabaRugi['saldo_akhir']  = 0;
        // var_dump($dataLabaRugi);
         //exit();
         foreach ($dataLabaRugi as $laba_rugi) {
            /*
             * debet = pendapatan
             * kredit = biaya (mengurangi)
             */
             /*
            if(strtoupper($laba_rugi['akun_kelompok']) == 'PENDAPATAN'){
                //$laba_rugi['status_debet'] = 1;
                if($laba_rugi['status'] == 'D') {
                    $debet  = $laba_rugi['nominal'];//pendapatan
                    $kredit = 0; //biaya
                } else {
                    $debet  = 0;
                    $kredit = $laba_rugi['nominal'];//pendapatan
                }
            } else {
                //$laba_rugi['status_debet'] = 0;
                if($laba_rugi['status'] == 'K') {
                    $debet  = 0;
                    $kredit = $laba_rugi['nominal'];//pendapatan
                } else {
                    $debet  = $laba_rugi['nominal'];//pendapatan
                    $kredit = 0; //biaya
                }
            }
            */
            $debet  = $laba_rugi['nominal_debet'];
            $kredit = $laba_rugi['nominal_kredit'];
            /*
             * 
            if($laba_rugi['status_debet'] == 0){
               $saldo   = ($kredit - $debit);
            }else{
               $saldo   = ($debit - $kredit);
            }
            */
            
            //saldo normal coa 3 (LR) adalah K
            //sehingga formula saldo = K - D
            //
            $saldo      = $kredit - $debet ;
            //end
            $dataJurnalLabaRugi['sub_account']  = $laba_rugi['sub_account'];
            $dataJurnalLabaRugi['saldo_awal']   = $saldoAwal;
            $dataJurnalLabaRugi['debet']        += $debet;
            $dataJurnalLabaRugi['kredit']       += $kredit;
            $dataJurnalLabaRugi['saldo']        += $saldo;
            $dataJurnalLabaRugi['saldo_akhir']  += $saldoAwal + $saldo;
            
            $dataPembukuanLabaRugi[$index]['sub_account']   = $laba_rugi['sub_account'];
            $dataPembukuanLabaRugi[$index]['pembukuan_id']  = $laba_rugi['pembukuan_id'];
            $dataPembukuanLabaRugi[$index]['data_id']       = $laba_rugi['pembukuan_detail_id'];
            $dataPembukuanLabaRugi[$index]['saldo_awal']    = $saldoAwalLabaRugi;
            $dataPembukuanLabaRugi[$index]['debet']         = $debet;
            $dataPembukuanLabaRugi[$index]['kredit']        = $kredit;
            $dataPembukuanLabaRugi[$index]['saldo']         = $saldo;
            $dataPembukuanLabaRugi[$index]['saldo_akhir']   = $saldoAwalLabaRugi + $saldo;
            $index++;
            $saldoAwalLabaRugi   += $saldo;
         }
      }

      // set proses
      $result        &= $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      $result        &= $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $result        &= $this->Execute($this->mSqlQueries['set_coa_laba_rugi'], array());
      $result        &= $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      $result        &= $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
      $result        &= $this->Execute($this->mSqlQueries['do_set_coa_laba_rugi'], array());

      // log query
      $queryLog[]    = sprintf($this->mSqlQueries['set_tahun_pembukuan']);
      $queryLog[]    = sprintf($this->mSqlQueries['set_tahun_anggaran']);
      $queryLog[]    = sprintf($this->mSqlQueries['set_coa_laba_rugi']);
      $queryLog[]    = sprintf($this->mSqlQueries['do_set_tahun_pembukuan']);
      $queryLog[]    = sprintf($this->mSqlQueries['do_set_tahun_anggaran']);
      $queryLog[]    = sprintf($this->mSqlQueries['do_set_tahun_anggaran']);
      $queryLog[]    = sprintf($this->mSqlQueries['do_set_coa_laba_rugi']);
      
      if(empty($dataJurnalCoa)){
         $result     &= false;
      }else{
         foreach ($dataJurnalCoa as $jurnalCoa) {
            // extract sub account
            list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7)   = explode('-', $jurnalCoa['sub_account']);
            if($jurnalCoa['bb_id'] === NULL OR $jurnalCoa['bb_id'] == ''){
               $result     &= $this->Execute($this->mSqlQueries['do_insert_buku_besar_sub_account'], array(
                  $date,
                  $jurnalCoa['akun_id'],
                  $subacc_1,
                  $subacc_2,
                  $subacc_3,
                  $subacc_4,
                  $subacc_5,
                  $subacc_6,
                  $subacc_7,
                  $jurnalCoa['saldo_awal'],
                  $jurnalCoa['saldo_awal'],
                  $jurnalCoa['debet'],
                  $jurnalCoa['debet'],
                  $jurnalCoa['kredit'],
                  $jurnalCoa['kredit'],
                  $jurnalCoa['saldo'],
                  $jurnalCoa['saldo'],
                  $jurnalCoa['saldo_akhir'],
                  $jurnalCoa['saldo_akhir'],
                  $userId
               ));

               // log
               $queryLog[] = sprintf($this->mSqlQueries['do_insert_buku_besar_sub_account'], $date, $jurnalCoa['akun_id'], $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7, $jurnalCoa['saldo_awal'], $jurnalCoa['saldo_awal'], $jurnalCoa['debet'], $jurnalCoa['debet'], $jurnalCoa['kredit'], $jurnalCoa['kredit'], $jurnalCoa['saldo'], $jurnalCoa['saldo'], $jurnalCoa['saldo_akhir'], $jurnalCoa['saldo_akhir'], $userId);
            }else{
               $result     &= $this->Execute($this->mSqlQueries['do_update_buku_besar_sub_account'], array(
                  $date,
                  $jurnalCoa['akun_id'],
                  $subacc_1,
                  $subacc_2,
                  $subacc_3,
                  $subacc_4,
                  $subacc_5,
                  $subacc_6,
                  $subacc_7,
                  $jurnalCoa['saldo_awal'],
                  $jurnalCoa['saldo_awal'],
                  $jurnalCoa['debet'],
                  $jurnalCoa['debet'],
                  $jurnalCoa['kredit'],
                  $jurnalCoa['kredit'],
                  $jurnalCoa['saldo'],
                  $jurnalCoa['saldo'],
                  $jurnalCoa['saldo_akhir'],
                  $jurnalCoa['saldo_akhir'],
                  $userId,
                  $jurnalCoa['bb_id']
               ));

               $queryLog[] = sprintf($this->mSqlQueries['do_insert_buku_besar_sub_account'], $date, $jurnalCoa['akun_id'], $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7, $jurnalCoa['saldo_awal'], $jurnalCoa['saldo_awal'], $jurnalCoa['debet'], $jurnalCoa['debet'], $jurnalCoa['kredit'], $jurnalCoa['kredit'], $jurnalCoa['saldo'], $jurnalCoa['saldo'], $jurnalCoa['saldo_akhir'], $jurnalCoa['saldo_akhir'], $userId, $jurnalCoa['bb_id']);
            }
         }
      }

      if(empty($dataJurnalPembukuan)){
         $result  &= false;
      }else{
         foreach ($dataJurnalPembukuan as $jurnalPembukuan) {
            list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7)   = explode('-', $jurnalPembukuan['sub_account']);
            $result  &= $this->Execute($this->mSqlQueries['do_insert_buku_besar_his_sub_account'], array(
               $jurnalPembukuan['id'],
               $jurnalPembukuan['buku_id'],
               $date,
               $jurnalPembukuan['coa_id'],
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7,
               $jurnalPembukuan['saldo_awal'],
               $jurnalPembukuan['saldo_awal'],
               $jurnalPembukuan['debet'],
               $jurnalPembukuan['debet'],
               $jurnalPembukuan['kredit'],
               $jurnalPembukuan['kredit'],
               $jurnalPembukuan['saldo'],
               $jurnalPembukuan['saldo'],
               $jurnalPembukuan['saldo_akhir'],
               $jurnalPembukuan['saldo_akhir'],
               $userId
            ));

            // log query
            $queryLog[]    = sprintf($this->mSqlQueries['do_insert_buku_besar_his_sub_account'], $jurnalPembukuan['id'], $jurnalPembukuan['buku_id'], $date, $jurnalPembukuan['coa_id'], $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7, $jurnalPembukuan['saldo_awal'], $jurnalPembukuan['saldo_awal'], $jurnalPembukuan['debet'], $jurnalPembukuan['debet'], $jurnalPembukuan['kredit'], $jurnalPembukuan['kredit'], $jurnalPembukuan['saldo'], $jurnalPembukuan['saldo'], $jurnalPembukuan['saldo_akhir'], $jurnalPembukuan['saldo_akhir'], $userId);

            // update status posting pembukuan referensi
            $result  &= $this->Execute($this->mSqlQueries['update_status_posting_pembukuan_ref'], array(
               $jurnalPembukuan['id']
            ));

            // log query
            $queryLog[]    = sprintf($this->mSqlQueries['update_status_posting_pembukuan_ref'], $jurnalPembukuan['id']);
         }
      }

      if(!empty($dataJurnalLabaRugi)){
         // extract sub account
         list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7)   = explode('-', $dataJurnalLabaRugi['sub_account']);
         if($dataJurnalLabaRugi['id'] === NULL OR $dataJurnalLabaRugi['id'] == ''){
            // insert buku besar laba rugi
            $result     &= $this->Execute($this->mSqlQueries['do_insert_buku_besar_lr_sub_account'], array(
               $date,
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7,
               $dataJurnalLabaRugi['saldo_awal'],
               $dataJurnalLabaRugi['saldo_awal'],
               $dataJurnalLabaRugi['debet'],
               $dataJurnalLabaRugi['debet'],
               $dataJurnalLabaRugi['kredit'],
               $dataJurnalLabaRugi['kredit'],
               $dataJurnalLabaRugi['saldo'],
               $dataJurnalLabaRugi['saldo'],
               $dataJurnalLabaRugi['saldo_akhir'],
               $dataJurnalLabaRugi['saldo_akhir'],
               $userId
            ));
            $queryLog[]    = sprintf($this->mSqlQueries['do_insert_buku_besar_lr_sub_account'], $date, $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7, $dataJurnalLabaRugi['saldo_awal'], $dataJurnalLabaRugi['saldo_awal'], $dataJurnalLabaRugi['debet'], $dataJurnalLabaRugi['debet'], $dataJurnalLabaRugi['kredit'], $dataJurnalLabaRugi['kredit'], $dataJurnalLabaRugi['saldo'], $dataJurnalLabaRugi['saldo'], $dataJurnalLabaRugi['saldo_akhir'], $dataJurnalLabaRugi['saldo_akhir'], $userId);
         }else{
            $result     &= $this->Execute($this->mSqlQueries['do_update_buku_besar_lr_sub_account'], array(
               $date,
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7,
               $dataJurnalLabaRugi['saldo_awal'],
               $dataJurnalLabaRugi['saldo_awal'],
               $dataJurnalLabaRugi['debet'],
               $dataJurnalLabaRugi['debet'],
               $dataJurnalLabaRugi['kredit'],
               $dataJurnalLabaRugi['kredit'],
               $dataJurnalLabaRugi['saldo'],
               $dataJurnalLabaRugi['saldo'],
               $dataJurnalLabaRugi['saldo_akhir'],
               $dataJurnalLabaRugi['saldo_akhir'],
               $userId,
               $dataJurnalLabaRugi['id']
            ));
            $queryLog[]    = sprintf($this->mSqlQueries['do_insert_buku_besar_lr_sub_account'], $date, $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7, $dataJurnalLabaRugi['saldo_awal'], $dataJurnalLabaRugi['saldo_awal'], $dataJurnalLabaRugi['debet'], $dataJurnalLabaRugi['debet'], $dataJurnalLabaRugi['kredit'], $dataJurnalLabaRugi['kredit'], $dataJurnalLabaRugi['saldo'], $dataJurnalLabaRugi['saldo'], $dataJurnalLabaRugi['saldo_akhir'], $dataJurnalLabaRugi['saldo_akhir'], $userId, $dataJurnalLabaRugi['id']);
         }

         foreach ($dataPembukuanLabaRugi as $lr) {
            list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7)   = explode('-', $lr['sub_account']);
            $result     &= $this->Execute($this->mSqlQueries['do_insert_buku_besar_his_lr_sub_account'], array(                            
               $lr['pembukuan_id'],
               $lr['data_id'],
               $date,
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7,
               $lr['saldo_awal'],
               $lr['saldo_awal'],
               $lr['debet'],
               $lr['debet'],
               $lr['kredit'],
               $lr['kredit'],
               $lr['saldo'],
               $lr['saldo'],
               $lr['saldo_akhir'],
               $lr['saldo_akhir'],
               $userId
            ));
          

            $queryLog[] = sprintf($this->mSqlQueries['do_insert_buku_besar_his_lr_sub_account'], $jurnalPembukuan['id'], $jurnalPembukuan['buku_id'], $date, $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7, $jurnalPembukuan['saldo_awal'], $jurnalPembukuan['saldo_awal'], $jurnalPembukuan['debet'], $jurnalPembukuan['debet'], $jurnalPembukuan['kredit'], $jurnalPembukuan['kredit'], $jurnalPembukuan['saldo'], $jurnalPembukuan['saldo'], $jurnalPembukuan['saldo_akhir'], $jurnalPembukuan['saldo_akhir'], $userId);
         }
      }

      if(!empty($queryLog)){
         // log query
         $result     &= $this->Execute($this->mSqlQueries['do_add_log'], array(
            $userId,
            $ipAddress,
            'Posting Buku Besar'
         ));
         $loggerId   = $this->LastInsertId();
         foreach ($queryLog as $query) {
            $result  &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
               $loggerId,
               addslashes($query)
            ));
         }
      }

      return $this->EndTrans($result);
   }

   function GetMinMaxThnTrans() {
      $ret = $this->open($this->mSqlQueries['get_minmax_tahun_transaksi'],array($start , $count));
      if($ret){
         return $ret[0];
      }else {
         $now_thn = date('Y');
         $thn['minTahun'] = $now_thn - 5 ;
         $thn['maxTahun'] = $now_thn + 5 ;
         return $thn;
      }
   }

   function GetDataPembukuan($tgl) {
      $result = $this->Open($this->mSqlQueries['get_data_pembukuan'], array($tgl));
      return $result;
   }

   function CekAkunBukuBesar($coa_id) {
      $result = $this->Open($this->mSqlQueries['cek_akun_buku_besar'], array($coa_id));
      return $result[0];
   }

   function CekAkunLabaRugiBukuBesar() {
      $result = $this->Open($this->mSqlQueries['cek_akun_laba_rugi_buku_besar'], array());
      return $result[0];
   }

   function CekSaldoLabaRugi() {
      $result = $this->Open($this->mSqlQueries['cek_saldo_laba_rugi'], array());
      return $result[0];
   }

   function CekCoaIsDebet($coa_id) {
      $result = $this->Open($this->mSqlQueries['cek_coa_is_debet'], array($coa_id));
      return $result[0];
   }

   function DoInsertBukuBesar($tgl, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_buku_besar'], array($tgl, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId));
      $sql = sprintf($this->mSqlQueries['do_insert_buku_besar'], $tgl, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);
      if($result)
         $this->DoAddLog('Insert Buku Besar', $sql);
      #echo $sql;
      return $result;
   }

   function DoUpdateBukuBesar($tgl, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $bb_id) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_update_buku_besar'], array($tgl, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id));
      $sql = sprintf($this->mSqlQueries['do_update_buku_besar'], $tgl, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id);
      if($result)
         $this->DoAddLog('Update Buku Besar', $sql);
      return $result;
   }

   function DoInsertBukuBesarHis($pemb_ref_id, $pemb_detail_id, $tgl, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_buku_besar_his'], array($pemb_ref_id, $pemb_detail_id, $tgl, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId));
      $sql = sprintf($this->mSqlQueries['do_insert_buku_besar_his'], $pemb_ref_id, $pemb_detail_id, $tgl, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);
      if($result)
         $this->DoAddLog('Insert Buku Besar History', $sql);
      return $result;
   }

   function DoInsertLabaRugiBukuBesar($tgl, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_laba_rugi_buku_besar'], array($tgl, $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId));
      $sql = sprintf($this->mSqlQueries['do_insert_laba_rugi_buku_besar'], $tgl, $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);
      if($result)
         $this->DoAddLog('Insert Labarugi Buku Besar', $sql);
      #echo $sql;
      return $result;
   }

   function DoUpdateLabaRugiBukuBesar($tgl, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $bb_id) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_update_laba_rugi_buku_besar'], array($tgl, $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id));
      $sql = sprintf($this->mSqlQueries['do_update_laba_rugi_buku_besar'], $tgl, $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id);
      if($result)
         $this->DoAddLog('Update Labarugi Buku Besar', $sql);
      return $result;
   }

   function DoInsertLabaRugiBukuBesarHis($pemb_ref_id, $pemb_detail_id, $tgl, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_laba_rugi_buku_besar_his'], array($pemb_ref_id, $pemb_detail_id, $tgl, $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId));
      $sql = sprintf($this->mSqlQueries['do_insert_laba_rugi_buku_besar_his'], $pemb_ref_id, $pemb_detail_id, $tgl, $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);
      if($result)
         $this->DoAddLog('Insert Labarugi Buku Besar History', $sql);
      return $result;
   }

   function GetUserId($username) {
      $result = $this->Open($this->mSqlQueries['get_user_id'], array($username));
      return $result[0];
   }

   function UpdateStatusPostingPembukuanRef($pr_id) {
      $result = $this->Execute($this->mSqlQueries['update_status_posting_pembukuan_ref'], array($pr_id));
      return $result;
   }

   #logger
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

   function _GetCoaLabaRugi() {
      $result = $this->Open($this->mSqlQueries['get_coa_laba_rugi'], array());
      return $result;
   }

   public function GetRealIP(){
      if ($_ENV["HTTP_CLIENT_IP"]) :
         $ip_address    = $_ENV["HTTP_CLIENT_IP"];
      elseif ($_ENV["HTTP_X_FORWARDED_FOR"]) :
         $ip_address    = $_ENV["HTTP_X_FORWARDED_FOR"];
      elseif ($_ENV["HTTP_X_FORWARDED"]) :
         $ip_address    = $_ENV["HTTP_X_FORWARDED"];
      elseif ($_ENV["HTTP_FORWARDED_FOR"]) :
         $ip_address    = $_ENV["HTTP_FORWARDED_FOR"];
      elseif ($_ENV["HTTP_FORWARDED"]) :
         $ip_address    = $_ENV["HTTP_FORWARDED"];
      elseif ($_SERVER['REMOTE_ADDR']) :
         $ip_address    = $_SERVER['REMOTE_ADDR'];
      endif;

      return $ip_address;
   }

   /*
    * @param string $camelCasedWord Camel-cased word to be "underscorized"
    * @param string $case case type, uppercase, lowercase
    * @return string Underscore-syntaxed version of the $camelCasedWord
    */
   public static function humanize($camelCasedWord, $case = 'upper')
   {
      switch ($case) {
         case 'upper':
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'lower':
            $return     = strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'title':
            $return     = ucwords(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'sentences':
            $return     = ucfirst(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         default:
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
      }
      return $return;
   }

   /*
    * @desc change key name from input data
    * @param array $input
    * @param string $case based on humanize method
    * @return array
    */
   public function ChangeKeyName($input = array(), $case = 'lower')
   {
      if(!is_array($input)){
         return $input;
      }

      foreach ($input as $key => $value) {
         if(is_array($value)){
            foreach ($value as $k => $v) {
               $array[$key][self::humanize($k, $case)] = $v;
            }
         }
         else{
            $array[self::humanize($key, $case)]  = $value;
         }
      }

      return (array)$array;
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function _getQueryString($pathInfo = null)
   {
      $parseUrl            = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
      $explodedUrl         = explode('&', $parseUrl['path']);
      $requestData         = '';
      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^ascomponent=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/uniqid=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }

         list($key, $value)   = explode('=', $path);
         $requestData[$key]   = Dispatcher::Instance()->Decrypt($value);
      }
      if(method_exists(Dispatcher::Instance(), 'getQueryString') === true){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }
      return $queryString;
   }

   public function getModule($pathInfo = null)
   {
      $module              = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $module        = $value;
         }
      }

      return $module;
   }

   public function getSubModule($pathInfo = null)
   {
      $subModule           = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $subModule     = $value;
         }
      }

      return $subModule;
   }

   public function getAction($pathInfo = null)
   {
      $action           = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $action        = $value;
         }
      }

      return $action;
   }

   public function getType($pathInfo = null)
   {
      $type                = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $type          = $value;
         }
      }

      return $type;
   }
}
?>