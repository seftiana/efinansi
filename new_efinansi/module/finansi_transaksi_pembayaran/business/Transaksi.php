<?php
/**
* ================= doc ====================
* FILENAME     : Transaksi.php
* @package     : Transaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-23
* @Modified    : 2015-04-23
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pembayaran/business/TransaksiPembayaran.class.php';

class Transaksi
{
   # internal variables
   private $mObj;
   protected $mData = array();
   private $urlReturn;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mObj       = new TransaksiPembayaran($connectionNumber);
      $queryString      = $this->mObj->_getQueryString();
      $queryReturn      = preg_replace('/(search=[a-zA-Z0-9\s\w]+)/', '', $queryString);
      $queryReturn      = preg_replace('/^[\&]+/', '', $queryReturn);
      $queryReturn      = preg_replace('/[\&]+$/', '', $queryReturn);
      $queryReturn      = preg_replace('/\&[\&]+/', '&', $queryReturn);
      $queryReturn      = ($queryReturn != '') ? '&search=1&'.$queryReturn : '';
      $this->urlReturn  = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_pembayaran',
         'TransaksiPembayaran',
         'view',
         'html'
      ).$queryReturn;
   }

   private function setData()
   {
      $requestData   = array();
      if($this->mObj->method == 'post'){
         $requestData['param']['tanggal_awal']  = date('Y-m-d', strtotime($this->mObj->_POST['tanggal_awal']));
         $requestData['param']['tanggal_akhir'] = date('Y-m-d', strtotime($this->mObj->_POST['tanggal_akhir']));
         $requestData['param']['type']          = strtoupper($this->mObj->_POST['type']);
         if(isset($this->mObj->_POST['data'])){
            if(!empty($this->mObj->_POST['data'])){
               $index      = 0;
               foreach ($this->mObj->_POST['data'] as $data) {
                  $requestData['data'][$index]['jenis_biaya']  = $data['jenis_biaya'];
                  $requestData['data'][$index]['nama']         = $data['penanggung_jawab'];
                  $requestData['data'][$index]['nominal']      = $data['nominal'];
                  $requestData['data'][$index]['type']         = strtoupper($this->mObj->_POST['type']);
                  $index++;
               }
            }
         }
      }

      $this->mData   = (array)$requestData;
   }

   public function getData()
   {
      $this->setData();
      return $this->mData;
   }

   public function Save()
   {
      $requestData      = $this->getData();
      $return['url']    = $this->urlReturn;
      $return['data']   = NULL;
      if(empty($requestData['data'])){
         $return['message']   = GTFWConfiguration::GetValue('language', 'data_kosong');
         $return['style']     = 'notebox-warning';
      }else{
         $process    = $this->mObj->doInsertTransaksiPembayaran($requestData['data']);
         if($process === true){
            $return['message']   = 'Proses sukses';
            $return['style']     = 'notebox-done';
            $return['data']      = $this->mObj->doUpdateStatusPembayaran($requestData['param'], 1);
         }else{
            $return['message']   = 'Proses gagal';
            $return['style']     = 'notebox-warning';
         }
      }
      return $return;
   }
}
?>