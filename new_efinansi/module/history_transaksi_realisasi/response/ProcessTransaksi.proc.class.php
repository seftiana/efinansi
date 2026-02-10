<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/history_transaksi_realisasi/business/AppTransaksi.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/generate_number/business/GenerateNumber.class.php';

class ProcessTransaksi {

    protected $_POST;
    protected $Obj;
    protected $pageView;
    protected $pageDetil;
    protected $pageInputDetil;
    protected $arrData;
    //css hanya dipake di view
    protected $cssDone = "notebox-done";
    protected $cssFail = "notebox-warning";
    protected $cssAlert = "notebox-alert";
    protected $return;
    protected $decId;
    protected $encId;
    protected $userId;
    protected $generateNumber;
    
    //limit kas besar dan kas kecil
    private $mLimitKasKecil = 500000;
       
    public function __construct() {
        $this->Obj = new AppTransaksi();
        $this->_POST = $_POST->AsArray();
        $this->decId = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $this->encId = Dispatcher::Instance()->Encrypt($this->decId);
        $this->userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $this->pageView = Dispatcher::Instance()->GetUrl(
                'history_transaksi_realisasi', 'HTFormRealisasiPencairan', 'view', 'html');
        $this->pageInputDetil = Dispatcher::Instance()->GetUrl(
                'history_transaksi_realisasi', 'HTFormRealisasiPencairanDetil', 'view', 'html');
        $this->pageDetil = Dispatcher::Instance()->GetUrl(
                'history_transaksi_realisasi', 'HTRealisasiPencairan', 'view', 'html');

        /**
         * untuk proses generate number bukti transaksi
         */
        $this->generateNumber = new GenerateNumber();

        /**
         * end
         */
    }

    public function setData($status = 'add') {
        $this->arrData['transUnitkerjaId'] = $this->_POST['unitkerja'];
        $this->arrData['transTransjenId'] = $this->_POST['jenis_transaksi'];
        $this->arrData['transTtId'] = $this->_POST['tipe_transaksi'];

        $this->arrData['transUserId'] = $this->userId;
        $this->arrData['transDueDate'] = $this->_POST['due_date_year'] . "-" .
                $this->_POST['due_date_mon'] . "-" .
                $this->_POST['due_date_day'];

        $this->arrData['transTanggal'] = $this->_POST['tanggal_transaksi_year'] . "-" .
                $this->_POST['tanggal_transaksi_mon'] . "-" .
                $this->_POST['tanggal_transaksi_day'];

        $this->arrData['transTanggal_lama'] = $this->_POST['transTanggal_lama'];
        $this->arrData['transCatatan'] = $this->_POST['catatan_transaksi'];
        $this->arrData['transPenanggungJawabNama'] = $this->_POST['penanggung_jawab'];
        $this->arrData['transPenerimaNama'] = $this->_POST['penerima'];
        $this->arrData['transNilai'] = $this->_POST['nominal'];
        
        //added
        $this->arrData['kegiatan_id'] = $this->_POST['kegiatan_id'];
        $this->arrData['realisasi_id'] = $this->_POST['realisasi_id'];
        
        $this->arrData['komponen']  = array();

         if(!empty($this->_POST['KOMP'])){
               $index            = 0;
               foreach ($this->_POST['KOMP'] as $komponen) {
                  $this->arrData['komponen'][$index]['pd_id']         = $komponen['pd_id'];
                  $this->arrData['komponen'][$index]['kegdet_id']     = $komponen['kegdet_id'];
                  $this->arrData['komponen'][$index]['p_id']          = $komponen['p_id'];
                  $this->arrData['komponen'][$index]['komp_kode']     = $komponen['komp_kode'];
                  $this->arrData['komponen'][$index]['komp_nama']     = $komponen['komp_nama'];
                  $this->arrData['komponen'][$index]['coa_kode']      = $komponen['coa_kode'];
                  $this->arrData['komponen'][$index]['deskripsi']     = $komponen['deskripsi'];
                  $this->arrData['komponen'][$index]['nominal']       = $komponen['nominal'];            
                  $index++;
               }
         }                
        
        if ($status == 'add') {
            
            //$noBuktiTransaksi = $this->generateNumber->GetNoBuktiTransaksi(
            //        $this->_POST['tipe_transaksi'], $this->_POST['unitkerja']);
      
            if(  $data['nominal'] > $this->mLimitKasKecil){
              //no ref kas besar
               $noBuktiTransaksi   =$this->generateNumber->getTransReferenceCP(
                 date('Y-m-d', strtotime($this->arrData['transTanggal']))
              );
            } else {
              //no ref kas kecil  
               $noBuktiTransaksi  =$this->generateNumber->getTransReferenceCPKasKecil(
                 date('Y-m-d', strtotime($this->arrData['transTanggal']))
              );
            }             
        } elseif ($status == 'update') {
            /**
             * cek apakah tanggal berbeda
             * jika berubah naka generate no bukti transaksi lagi sesui dengan
             * unitkerja id  yang baru
             */
            if ( date('Y-m', strtotime($this->arrData['transTanggal'])) ==  date('Y-m', strtotime($this->arrData['transTanggal_lama']))) {
                $noBuktiTransaksi = $this->_POST['no_kkb'];
            } else {
                //$noBuktiTransaksi = $this->generateNumber->GetNoBuktiTransaksi(
                //        $this->_POST['tipe_transaksi'], $this->_POST['unitkerja']);
      
                    if(   $this->arrData['transNilai']  > $this->mLimitKasKecil){
                      //no ref kas besar
                       $noBuktiTransaksi   =$this->generateNumber->getTransReferenceCP(
                         date('Y-m-d', strtotime($this->arrData['transTanggal']))
                      );
                    } else {
                      //no ref kas kecil  
                       $noBuktiTransaksi  = $this->generateNumber->getTransReferenceCPKasKecil(
                         date('Y-m-d', strtotime($this->arrData['transTanggal']))
                      );
                    }                      
            }
            
            /**
             * end
             */
        }
        $this->arrData['transReferensi'] = $noBuktiTransaksi;
        if ($this->_POST['skenario'] == "auto") {
            $this->arrData['transIsJurnal'] = "Y";
        } else {
            $this->arrData['transIsJurnal'] = "T";
        }
        if ($this->decId != "")
            $this->arrData['transId'] = $this->decId;
        return $this->arrData;
    }


    
    public function Check() {
        if (isset($_POST['btnsimpan'])) {
            /**
              if($this->decId != '') {
              $cek = $this->Obj->CekTransaksiUpdate($this->_POST['no_kkb'], $this->decId);
              } else {
              $cek = $this->Obj->CekTransaksi($this->_POST['no_kkb']);
              }
             */
            #if ($cek === false) {
            #    return "exists";
            #}
            $requestData      =  $this->arrData;
            if(empty($requestData)){
               $err[]      = 'Tidak ada data yang akan di submit';
            }

            if($requestData['transUnitkerjaId'] == ''){
               $err[]      = 'Definisikan unit kerja';
            }

            if($requestData['transNilai'] <= 0){
               $err[]      = 'Isikan nominal realisasi';
            }
            
            if($requestData['transPenanggungJawabNama'] == ''){
               $err[]      = 'Isikan nama penanggung jawab realisasi';
            }

            if($requestData['transPenerimaNama'] == ''){
               $err[]      = 'Isikan nama penerima realisasi';
            }

            if(isset($err)){
               $return['message']   = $err[0];
               $return['result']    = false;
            }else{
               $return['message']   = NULL;
               $return['result']    = true;
            }
            
           return $return;
        }
        return $return['result']    = false;
    }

    public function Update() {
        $this->setData('update');
        $cek = $this->Check();
        //$cek = true;
        if (($cek['result'] === true)) {
        //    $this->setData('update');
            $upd_transaksi =  $this->Obj->DoUpdateTransaksi($this->arrData);
            //var_dump($upd_transaksi);
            if ($upd_transaksi === TRUE) {
                Messenger::Instance()->Send(
                        'history_transaksi_realisasi', 'HTFormRealisasiPencairanDetil', 'view', 'html', array(
                    $this->_POST,
                    'Perubahan Data Transaksi Berhasil Dilakukan',
                    $this->cssDone), Messenger::NextRequest);
                return $this->pageInputDetil . '&dataId=' . $this->decId;
            } else {
                //gagal masukin data
                Messenger::Instance()->Send(
                    'history_transaksi_realisasi', 'HTFormRealisasiPencairan', 'view', 'html', array(
                    $this->_POST,
                    'Gagal Merubah Data Transaksi',
                    $this->cssFail), Messenger::NextRequest);
                return $this->pageView . '&dataId=' . $this->encId;
            }
            //}
        } elseif ($cek == "exists") {
            Messenger::Instance()->Send(
                    'history_transaksi_realisasi', 'HTFormRealisasiPencairan', 'view', 'html', array(
                $this->_POST,
                'Transaksi Dengan Nomor <b>' .
                ($this->_POST['no_kkb']) . '</b> Sudah Dibuat',
                $this->cssFail), Messenger::NextRequest);

            return $this->pageView . '&dataId=' . $this->encId;
        } else {
            //gagal masukin data
            Messenger::Instance()->Send(
                'history_transaksi_realisasi', 'HTFormRealisasiPencairan', 'view', 'html', array(
                $this->_POST,
               $cek['message'],
                $this->cssFail), Messenger::NextRequest);
            return $this->pageView . '&dataId=' . $this->encId;
        }
        return $this->pageDetil;
    }

    public function Delete() {
        $arrId = $this->_POST['idDelete'];
        $deleteArrData = $this->Obj->DoDeleteDataById($arrId);
        if ($deleteArrData === true) {
            Messenger::Instance()->Send(
                    'history_transaksi_realisasi', 'HTRealisasiPencairan', 'view', 'html', array(
                $this->_POST,
                'Penghapusan Data Berhasil Dilakukan',
                $this->cssDone), Messenger::NextRequest);
        } else {
            Messenger::Instance()->Send(
                    'history_transaksi_realisasi', 'HTRealisasiPencairan', 'view', 'html', array(
                $this->_POST,
                $gagal . ' Data Tidak Dapat Dihapus.',
                $this->cssFail), Messenger::NextRequest);
        }
        return $this->pageDetil;
    }

}

?>