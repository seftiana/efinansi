<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/finansi_sppu/business/Sppu.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/generate_number/business/GenerateNumber.class.php';

class ProcessSppu {

  public $Obj;
  public $url_return;
  public $url_view;
  public $url_cetak;
  public $css_warning  = 'notebox-warning';
  public $css_done     = 'notebox-done';
  protected $generateNumber;
  protected $userId;
  protected $_POST;
   
  function __construct()
  {
    $this->Obj              = new Sppu();
    $queryString            = $this->Obj->_getQueryString();
    $this->generateNumber   = new GenerateNumber();
    $this->userId           = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
    $this->url_return       = Dispatcher::Instance()->GetUrl(
      'finansi_sppu',
      'KonfirmasiCetak',
      'view',
      'html'
    ).'&'.$queryString;

    $this->url_view       = Dispatcher::Instance()->GetUrl(
      'finansi_sppu',
      'ListSppu',
      'view',
      'html'
    );

    $this->url_cetak      = Dispatcher::Instance()->GetUrl(
      'finansi_sppu',
      'ExportExcelBp',
      'view',
      'xlsx'
    ).'&'.$queryString;
      
  }

  function setData(){

      $data        = array();
      $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
      
      $dataSppu    = $this->Obj->getDataTransaksiBank($this->_POST['id']);
      $tanggalDay    = (int)$this->Obj->_POST['tanggal_day'];
      $tanggalMon    = (int)$this->Obj->_POST['tanggal_mon'];
      $tanggalYear   = (int)$this->Obj->_POST['tanggal_year'];

      $tanggal        = date('Y-m-d', mktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear));
      $tanggal_sppu   = date('Y-m-d', strtotime($this->Obj->_POST['tanggal_sppu_hide']));
      $tanggal_bank   = date('Y-m-d', strtotime($this->Obj->_POST['tanggal_bank']));
      
      $nomorBP        = $this->Obj->getNomorBp($tanggal);
      $nomorReferensi = $this->generateNumber->getNomorBpBank($tanggal);

      if($this->Obj->method == 'post'){

        $data['transaksiBankNomor']            = $nomorReferensi;
        $data['transaksiBankBpkb']             = $nomorBP;
        $data['transaksiBankTanggal']          = $tanggal;
        $data['transaksiBankSppuId']           = $this->_POST['id'];
        $data['transaksiBankCoaIdPenerima']    = NULL;
        $data['transaksiBankPenerima']         = NULL;
        $data['transaksiBankRekeningPenerima'] = NULL;
        $data['transaksiBankCoaIdTujuan']      = NULL;
        $data['transaksiBankTujuan']           = NULL;
        $data['transaksiBankRekeningTujuan']   = NULL;
        $data['transaksiBankNominal']          = $this->_POST['nominal'];
        $data['transaksiBankTipe']             = 'pengeluaran';
        $data['transaksiBankUserId']           = $this->userId;

      }
         
        $this->data = $data;
   }

  function Check()
   {
      $requestData      = $this->data;
      $setDate          = $this->Obj->setDate();
      $thanggar_awal    = $setDate['tanggal_awal'];
      $thanggar_akhir   = $setDate['tanggal_akhir'];

      if($requestData['transaksiBankTanggal'] < $thanggar_awal || $requestData['transaksiBankTanggal'] > $thanggar_akhir){
        $err[]          = 'Tanggal Cetak BP tidak sesuai dengan Tahun Anggaran aktif';
      }

      if(empty($requestData)){
         $err[]         = 'Tidak ada data yang akan di cetak';
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

    public function Add(){

      $this->setData();
      $check      = $this->Check();
      $dataSppu    = $this->Obj->getDataTransaksiBank($this->_POST['id']);

        if($check['result'] === false){
          Messenger::Instance()->Send(
                        'finansi_sppu', 'KonfirmasiCetak', 'view', 'html', array(
                    $this->_POST,
                    $check['message'],
                    $this->css_warning), Messenger::NextRequest);
                return $this->url_return;

        }elseif(empty($dataSppu) && $check['result'] == true){
          $process       = $this->Obj->DoInsertTransaksiBank($this->data);   

          // if($process['result'] === true){
            Messenger::Instance()->Send(
                        'finansi_sppu', 'ExportExcelBp', 'view', 'xlsx', array(
                    $this->_POST,
                    'Proses Cetak SPPU Berhasil Dilakukan',
                    $this->css_done), Messenger::NextRequest);
                return $this->url_cetak;
          // }else{
          //   Messenger::Instance()->Send(
          //               'finansi_sppu', 'KonfirmasiCetak', 'view', 'html', array(
          //           $this->_POST,
          //           'Proses Cetak SPPU Gagal Dilakukan',
          //           $this->css_warning), Messenger::NextRequest);
          //       return $this->url_return;
          // }
        }elseif(!empty($dataSppu)){
            Messenger::Instance()->Send(
                        'finansi_sppu', 'ExportExcelBp', 'view', 'xlsx', array(
                    $this->_POST,
                    'Proses Cetak SPPU Berhasil Dilakukan',
                    $this->css_done), Messenger::NextRequest);
                return $this->url_cetak;
        }else{
            Messenger::Instance()->Send(
                        'finansi_sppu', 'KonfirmasiCetak', 'view', 'html', array(
                    $this->_POST,
                    'Proses Cetak SPPU Gagal Dilakukan',
                    $this->css_warning), Messenger::NextRequest);
                return $this->url_return;
        }
    }
}

?>