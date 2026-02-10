<?php
/**
* ================= doc ====================
* FILENAME     : ProcessApproval.php
* @package     : ProcessApproval
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-25
* @Modified    : 2015-03-25
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/approval/business/AppDetilApproval.class.php';

class ProcessApproval
{
   # internal variables
   private $mObj;
   protected $mData  = array();
   private $urlHome;
   private $urlApproval;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mObj    = new AppDetilApproval($connectionNumber);
      $queryString   = $this->mObj->_getQueryString();
      $requestQuery  = ($queryString == '') ? '' : '&search=1&'.$queryString;
      $this->urlApproval   = Dispatcher::Instance()->GetUrl(
         'approval',
         'DetilApproval',
         'view',
         'html'
      ).'&'.$queryString;
      $this->urlHome       = Dispatcher::Instance()->GetUrl(
         'approval',
         'approval',
         'view',
         'html'
      ) . $requestQuery;
   }

   private function setData()
   {
      $requestData   = array();
      if($this->mObj->method == 'post'){
         $requestData['id']      = $this->mObj->_POST['data_id'];
         $requestData['status']  = array();
         if($this->mObj->_POST['data'] && !empty($this->mObj->_POST['data'])){
            $index   = 0;
            foreach ($this->mObj->_POST['data'] as $data) {
               $requestData['status'][$index]         = $data['status'];
               $requestData['data'][$index]['id']     = $data['id'];
               $requestData['data'][$index]['kode']   = $data['komponen_kode'];
               $requestData['data'][$index]['kegiatan_id']  = $data['kegiatan_id'];
               $requestData['data'][$index]['status']       = $data['status'];
               $requestData['data'][$index]['status_awal']       = $data['status_awal'];
               $requestData['data'][$index]['keterangan']   = $data['deskripsi'];
               $requestData['data'][$index]['satuan']       = $data['satuan'];
               $requestData['data'][$index]['nominal_satuan']  = $data['nominal_satuan'];
               $requestData['data'][$index]['satuan_approve']  = $data['satuan_approve'];
               $requestData['data'][$index]['nominal_satuan_approve']   = $data['nominal_satuan_approve'];
               $requestData['data'][$index]['nominal_approve']          = $data['nominal_approve'];
               $index+=1;
            }
         }
      }
      $this->mData   = (array)$requestData;
   }

   public function getData()
   {
      $this->setData();

      return (array)$this->mData;
   }

   private function checkData()
   {
      $requestData      = (array)$this->mData;
      if(empty($requestData)){
         $err[]         = 'Tidak ada data yang akan di proses';
      }

      if(empty($requestData['data'])){
         $err[]         = 'Tidak ada data komponen yang akan di proses';
      }

      foreach ($requestData['data'] as $data) {
         if($data['satuan_approve'] <= 0 && ($data['status'] == 'Ya')){
            $err[]      = 'Isikan satuan '.GTFWConfiguration::GetValue('language', 'komponen').' : '.$data['kode'].' yang akan disetujui';
         }
         if($data['nominal_satuan_approve'] > $data['nominal_satuan']){
            $err[]      = 'Nominal satuan approve untuk '.GTFWConfiguration::GetValue('language', 'komponen').' : '.$data['kode'].' Melebihi nominal usulan yang di rencanakan sebesar <strong>Rp. '.number_format($data['nominal_satuan'], 2, ',','.').'</strong>';
         }
      }

      if(isset($err)){
         $return['message']   = implode('<br />', $err);
         $return['result']    = false;
      }else{
         $return['message']   = NULL;
         $return['result']    = true;
      }

      return (array)$return;
   }

   public function Appove()
   {
      $this->setData();
      $check      = $this->checkData();
      if($check['result'] === false){
         $return['url']       = $this->urlApproval;
         $return['dest']      = 'popup-subcontent'; // subcontent-element
         $return['data']      = $this->mObj->_POST;
         $return['message']   = $check['message'];
         $return['style']     = 'notebox-warning';
      }else{
         $process             = $this->mObj->doApproval($this->mData);
         if($process === true){
            $return['url']       = $this->urlApproval;
            $return['dest']      = 'popup-subcontent'; // subcontent-element
            $return['data']      = $this->mObj->_POST;
            $return['message']   = 'Proses Berhasil';
            $return['style']     = 'notebox-done';
         }else{
            $return['url']       = $this->urlApproval;
            $return['dest']      = 'popup-subcontent'; // subcontent-element
            $return['data']      = $this->mObj->_POST;
            $return['message']   = 'Proses Gagal';
            $return['style']     = 'notebox-warning';
         }
      }

      return (array)$return;
   }
}
?>