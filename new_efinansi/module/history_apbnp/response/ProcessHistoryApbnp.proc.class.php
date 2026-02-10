<?php
/**
* ================= doc ====================
* FILENAME     : ProcessHistoryApbnp.php
* @package     : history_apbnp
* scope        : PUBLIC
* @Author      : noor hadi
* @Created     : 2015-12-23
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2015 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application','docroot').
'module/history_apbnp/business/HistoryApbnp.class.php';

    
class ProcessHistoryApbnp
{
   # internal variables
   #    internal variables
   public $obj;
   public $pageReturn;
   public $pageView;
   public $data;
   public $errKomp;
        
   public $cssDone = 'notebox-done';
   public $cssFail = 'notebox-warning';
   # Constructor
   function __construct()
   {
      $this->mObj       = new HistoryApbnp;
      $this->data       = $_POST->AsArray();
      $queryString      = $this->mObj->_getQueryString();
      $queryReturn      = (!empty($queryString)) ? '&search=1&'.$queryString : '';
	  
      $this->pageView = Dispatcher::Instance()->GetUrl(
         'history_apbnp',
         'HistoryApbnp',
         'view',
         'html'
      ). $queryReturn;
	  
    
      $this->pageReturn  = Dispatcher::Instance()->GetUrl(
         'history_apbnp',
         'UpdateHistoryApbnp',
         'view',
         'html'
      ).'&'.$queryString;

   }

   /**
    * 
    * proses update data history
    * 
    */ 

        
        public function check()
        {
            $mak_id             = $this->data['mak_id'];
            $mak_nama           = $this->data['mak_nama'];
            $kegRefIdAsal       = $this->data['kegrefId'];
            $kegRefNamaAsal     = $this->data['kegrefNama'];
            $kegRefNomorAsal    = $this->data['kegrefNomor'];
            $komponen_asal      = $this->data['KOMP'];
            $kegRefIdTujuan     = $this->data['kegrefIdTujuan'];
            $kegRefNamaTujuan   = $this->data['kegrefNamaTujuan'];
            $kegRefNomorTujuan  = $this->data['kegrefNomorTujuan'];
            $komponen_tujuan    = $this->data['KOMPTUJUAN'];
            
            
            if(isset($this->data['save'])){
                
                if(!empty($komponen_asal)){
                $errKomp = '';
                foreach($komponen_asal as $key => $v) {
                    $nominalKompAsal[$key]    = $v['nominal'];
                    $nominalKompAsalHid[$key]    = $v['nominal_hid'];
                    if($v['nominal'] > $v['nominal_hid']){
                        $errKomp .= 'Nilai yang akan dipindahkan belanja pada kode '.number_format($v['kodeKomponen'],0,',','.').' melebihi dari nilai yang tersedia. ';
                        $errKomp .= 'Nilai tersedia : '.number_format($v['nominal'],0,',','.').' Nilai yang akan dipindahkan : '.number_format($v['nominal_hid'],0,',','.').'</br>';
                    }else{
                        $errKomp ='';
                    }
                }}
                if(!empty($komponen_tujuan)){
                foreach($komponen_tujuan as $key => $v) {
                    $nominalKompTujuan[$key]  = $v['nominal'];
                    $nominalKompTujuanHid[$key]  = $v['nominal_hid'];
                }
                }
                $totalNominalKompAsal   = count($nominalKompAsal) <> 0 ? array_sum($nominalKompAsal) : 0;
                $totalNominalKompAsalHid   = count($nominalKompAsalHid) <> 0 ? array_sum($nominalKompAsalHid) : 0;
                $totalNominalKompTujuan = count($nominalKompTujuan) <> 0 ? array_sum($nominalKompTujuan) : 0;
                $this->data['nominal_movement'] = $totalNominalKompAsal;
                $this->data['nominal_movement_hid'] = $totalNominalKompAsalHid;
                $this->data['nominal_movement_tujuan'] = $totalNominalKompTujuan;
                $this->errKomp = $errKomp;
                
                /*if($mak_id == ''){
                    return 'emptyMak';
                }else*/
                if($kegRefIdAsal == ''){
                    return 'emptyKegAsal';
                }elseif($kegRefIdTujuan == ''){
                    return 'emptyKegTujuan';
                }elseif(count($komponen_asal) < 1 OR count($komponen_tujuan) < 1){
                    return 'emptyKomponen';
                }elseif(($errKomp != '')){
                    return 'errKomp';
                }elseif($totalNominalKompAsal > $totalNominalKompAsalHid){
                    return 'toOver';
                }elseif($totalNominalKompTujuan > $totalNominalKompAsal){
                    return 'toBig';
                }elseif($totalNominalKompTujuan < $totalNominalKompAsal){
                    return 'toSmall';
                }elseif($totalNominalKompTujuan == 0){
                    return 'toNol';
                }else{
                    return true;
                }
            }else{
                return $this->pageView;
            }
        }
        
        function Update()
        {
            $check              = $this->check();
            //print_r($this->data);
            if(isset($this->data['cancel']))
            {
                return $this->pageView;
            }
            if($check === true and isset($this->data['save'])){
                
                # proses menyimpan data apbnp > data master
                $add_apbnp  = $this->mObj->UpdateHistoryMovement($this->data);
                
                # jika proses apbnp berhasil
                if($add_apbnp){
                   
                        # jika semua proses berjalan dengan baik
                        $pesan  = 'Process Update Revisi Interen berhasil di laksanakan';
                        Messenger::Instance()->
                        Send(
                            'history_apbnp',
                            'historyApbnp',
                            'view',
                            'html',
                            array(
                                array(),
                                $pesan,
                                $this->cssDone
                            ),
                            Messenger::NextRequest
                        );
                        return $this->pageView;
                    
                }else{                    
                    $pesan  = 'Proses Revisi Interen gagal di laksanakan. ';
                    Messenger::Instance()->
                    Send(
                        'history_apbnp',
                        'UpdateHistoryApbnp',
                        'view',
                        'html',
                        array(
                             $this->data,
                             $pesan,
                             $this->cssFail
                        ),
                        Messenger::NextRequest
                    );
                    return $this->pageReturn;
                }
                
            }elseif($check == 'emptyMak'){
                $pesan  = "Pilih MAK";
        
                Messenger::Instance()->
                Send(
                    'history_apbnp',
                    'UpdateHistoryApbnp',
                    'view',
                    'html',
                    array(
                        $this->data,
                        $pesan,
                        $this->cssFail
                    ),
                    Messenger::NextRequest
                );
                return $this->pageReturn;
            }elseif($check == 'emptyKegAsal'){
                $pesan  = "Pilih kegiatan Asal";
        
                Messenger::Instance()->
                Send(
                    'history_apbnp',
                    'UpdateHistoryApbnp',
                    'view',
                    'html',
                    array(
                        $this->data,
                        $pesan,
                        $this->cssFail
                    ),
                    Messenger::NextRequest
                );
                return $this->pageReturn;
            }elseif($check == 'emptyKegTujuan'){
                $pesan  = "Pilih kegiatan Tujuan";
        
                Messenger::Instance()->
                Send(
                    'history_apbnp',
                    'UpdateHistoryApbnp',
                    'view',
                    'html',
                    array(
                        $this->data,
                        $pesan,
                        $this->cssFail
                    ),
                    Messenger::NextRequest
                );
                return $this->pageReturn;
            }elseif($check == 'emptyKomponen'){
                $pesan  = "Tidak ada komponen yang akan di pindahkan, pastikan Anda memilih kegiatan asal dan kegiatan tujuan yang mempunyai komponen anggaran";
        
                Messenger::Instance()->
                Send(
                    'history_apbnp',
                    'UpdateHistoryApbnp',
                    'view',
                    'html',
                    array(
                        $this->data,
                        $pesan,
                        $this->cssFail
                    ),
                    Messenger::NextRequest
                );
                return $this->pageReturn;
            }elseif($check == 'toBig'){
                $pesan  = "Nilai total dari komponen tujuan yang Anda masukkan melebihi nilai yang akan di pindahkan";
                $pesan  .= "<br />Nilai Total Anggaran yang akan di pindahkan Rp. ".number_format($this->data['nominal_movement'],0,',','.');
                $pesan  .= "<br />Nilai Total Anggaran Tujuan Rp. ".number_format($this->data['nominal_movement_tujuan'],0,',','.');
        
                Messenger::Instance()->
                Send(
                    'history_apbnp',
                    'UpdateHistoryApbnp',
                    'view',
                    'html',
                    array(
                        $this->data,
                        $pesan,
                        $this->cssFail
                    ),
                    Messenger::NextRequest
                );
                return $this->pageReturn;
            }elseif($check == 'toSmall'){
                $pesan  = "Nilai total dari komponen tujuan yang Anda masukkan lebih kecil dari nilai yang akan di pindahkan atau masih kosong";
                $pesan  .= "<br />Nilai Total Anggaran yang akan di pindahkan Rp. ".number_format($this->data['nominal_movement'],0,',','.');
                $pesan  .= "<br />Nilai Total Anggaran Tujuan Rp. ".number_format($this->data['nominal_movement_tujuan'],0,',','.');
        
                Messenger::Instance()->
                Send(
                    'history_apbnp',
                    'UpdateHistoryApbnp',
                    'view',
                    'html',
                    array(
                        $this->data,
                        $pesan,
                        $this->cssFail
                    ),
                    Messenger::NextRequest
                );
                return $this->pageReturn;
            }elseif($check == 'toOver'){
                $pesan  = "Nilai total dari Anggaran yang akan dipindahkan melebihi sisa anggaran yang tersedia";
                $pesan  .= "<br />Nilai Total Anggaran yang tersedia Rp. ".number_format($this->data['nominal_movement_hid'],0,',','.');
                $pesan  .= "<br />Nilai Total Anggaran yang akan dipindahkan Rp. ".number_format($this->data['nominal_movement'],0,',','.');
        
                Messenger::Instance()->
                Send(
                    'history_apbnp',
                    'UpdateHistoryApbnp',
                    'view',
                    'html',
                    array(
                        $this->data,
                        $pesan,
                        $this->cssFail
                    ),
                    Messenger::NextRequest
                );
                return $this->pageReturn;
            }elseif($check == 'toNol'){
                $pesan  = "Nilai Total dari Anggaran tujuan masih kosong";
        
                Messenger::Instance()->
                Send(
                    'history_apbnp',
                    'UpdateHistoryApbnp',
                    'view',
                    'html',
                    array(
                        $this->data,
                        $pesan,
                        $this->cssFail
                    ),
                    Messenger::NextRequest
                );
                return $this->pageReturn;
            }elseif($check == 'errKomp'){
                $pesan  = $this->errKomp;
        
                Messenger::Instance()->
                Send(
                    'history_apbnp',
                    'UpdateHistoryApbnp',
                    'view',
                    'html',
                    array(
                        $this->data,
                        $pesan,
                        $this->cssFail
                    ),
                    Messenger::NextRequest
                );
                return $this->pageReturn;
            }else{
                return $this->pageReturn;
            }
        }

   
   /**
    * proses hapus history
    */
   public function Delete()
   {

      $url        =  $this->pageView;
      $data       = NULL;      
      $process    = $this->mObj->DoDeleteApbnp($this->data['idDelete']);
      if($process === true)  {
        $message    = 'Proses penghapusan data berhasil';
        $style      = 'notebox-done';
      }else{
        $message    = 'Proses penghapusan data gagal';
        $style      = 'notebox-warning';
      }
         
      Messenger::Instance()->Send(
        'history_apbnp',
        'HistoryApbnp',
        'view',
        'html',
        array(
            '',
            $message,
            $style
        ),
        Messenger::NextRequest
      );
      return $url;
   }
   
}

?>