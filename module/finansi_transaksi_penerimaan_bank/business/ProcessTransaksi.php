<?php
/**
* ================= doc ====================
* FILENAME     : ProcessTransaksi.php
* @package     : ProcessTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-19
* @Modified    : 2015-05-19
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_penerimaan_bank/business/TransaksiPenerimaanBank.class.php';

class ProcessTransaksi
{
   # internal variables
   private $mObj;
   protected $mData = array();
   private $url_input;
   private $url_return;
   private $url_edit;
   private $url_detail;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mObj       = new TransaksiPenerimaanBank($connectionNumber);
      $queryString      = $this->mObj->_getQueryString();
      $queryReturn      = (!empty($queryString)) ? '&search=1&'.$queryString : '';
      $this->url_return = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_bank',
         'TransaksiPenerimaanBank',
         'view',
         'html'
      ). $queryReturn;
      $this->url_input  = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_bank',
         'Transaksi',
         'view',
         'html'
      ).'&'.$queryString;
      $this->url_edit   = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_bank',
         'EditTransaksi',
         'view',
         'html'
      ).'&'.$queryString;
      $this->url_detail    = Dispatcher::Instance()->GetUrl(
         'finansi_transaksi_penerimaan_bank',
         'BuktiTransaksi',
         'view',
         'html'
      ).'&'.$queryString;

   }

   private function setData()
   {
      $request_data     = array();
      if(strtoupper($this->mObj->method) == 'POST'){
         $tanggal_mon   = (int)$this->mObj->_POST['tanggal_mon'];
         $tanggal_day   = (int)$this->mObj->_POST['tanggal_day'];
         $tanggal_year  = (int)$this->mObj->_POST['tanggal_year'];

         $request_data['id']     = $this->mObj->_POST['data_id'];
         $request_data['bpkb']   = $this->mObj->_POST['bpkb'];
         $request_data['coa_id_penyetor']      = $this->mObj->_POST['coa_id_penyetor'];
         $request_data['nama_penyetor']      = $this->mObj->_POST['nama_penyetor'];
         $request_data['rekening_penyetor']  = $this->mObj->_POST['rekening_penyetor'];
         $request_data['coa_id_penerima']      = $this->mObj->_POST['coa_id_penerima'];
         $request_data['nama_penerima']      = $this->mObj->_POST['nama_penerima'];
         $request_data['rekening_penerima']  = $this->mObj->_POST['rekening_penerima'];
         $request_data['tanggal']            = date('Y-m-d', mktime(0,0,0, $tanggal_mon, $tanggal_day, $tanggal_year));
         $request_data['check_date']         = checkdate($tanggal_mon, $tanggal_day, $tanggal_year);
         $request_data['komponen']           = array();
         $request_data['nominal']            = $this->mObj->_POST['nominal_komponen'];
         if(!empty($this->mObj->_POST['komponen'])){
            $index      = 0;
            foreach ($this->mObj->_POST['komponen'] as $komp) {
               $request_data['komponen'][$index]['id']        = $komp['id'];
               $request_data['komponen'][$index]['nama']      = $komp['keterangan'];
               $request_data['komponen'][$index]['nominal']   = $komp['nominal'];
               $index++;
            }
         }
      }

      $this->mData      = (array)$request_data;
   }

   public function getData()
   {
      $this->setData();
      return (array)$this->mData;
   }

   private function checkData()
   {
      unset($err);
      $request_data     = $this->mData;
      if(empty($request_data)){
         $err[]         = 'Tidak ada data yang akan diproses';
      }
      /*
      if($request_data['bpkb'] == ''){
         $err[]         = 'Isikan nomor BPKB';
      }
      */
      if($request_data['check_date'] === false){
         $err[]         = 'Pendefinisian Tanggal Tidak sesuai';
      }
      if($request_data['nama_penyetor'] == ''){
         $err[]         = 'Isikan Nama Rekening Asal';
      }
      if($request_data['nama_penerima'] == ''){
         $err[]         = 'Isikan Nama Penerima';
      }

      if(empty($request_data['komponen'])){
         $err[]         = 'Definisikan Keterangan';
      }

      if($request_data['nominal'] <= 0){
         $err[]         = 'Nominal transaksi tidak sesuai';
      }

      if(isset($err)){
         $message       = $err[0];
         $result        = false;
      }else{
         $message       = null;
         $result        = true;
      }

      return compact('message', 'result');
   }

   public function Save()
   {
      $this->setData();
      $check      = $this->checkData();
      if($check['result'] === false){
         $url        = $this->url_input;
         $message    = $check['message'];
         $style      = 'notebox-warning';
         $data       = $this->mObj->_POST;
      }else{
         $process    = $this->mObj->doInsertTransaksi($this->mData);
         if($process['result'] === true){
            $url        = $this->url_detail.'&transaksi_id='.Dispatcher::Instance()->Encrypt($process['transaksi_bank_id']);
            $message    = 'Proses penyimpanan data berhasil';
            $style      = 'notebox-done';
            $data       = NULL;
         }else{
            $url        = $this->url_input;
            $message    = 'Proses penyimpanan data gagal';
            $style      = 'notebox-warning';
            $data       = $this->mObj->_POST;
         }
      }

      return compact('url', 'message', 'style', 'data');
   }

   public function Update()
   {
      $this->setData();
      $check      = $this->checkData();
      if($check['result'] === false){
         $url        = $this->url_edit;
         $message    = $check['message'];
         $style      = 'notebox-warning';
         $data       = $this->mObj->_POST;
      }else{
         $process    = $this->mObj->doUpdateTransaksi($this->mData);
         if($process['result'] === true){
            $url        = $this->url_detail.'&transaksi_id='.Dispatcher::Instance()->Encrypt($process['transaksi_id']);
            $message    = 'Proses update data berhasil';
            $style      = 'notebox-done';
            $data       = NULL;
         }else{
            $url        = $this->url_edit;
            $message    = 'Proses update data gagal';
            $style      = 'notebox-warning';
            $data       = $this->mObj->_POST;
         }
      }

      return compact('url', 'message', 'style', 'data');
   }

   public function Delete()
   {

      $url        = $this->url_return;
      $data       = NULL;

      if($this->mObj->method == 'post'){
         $process    = $this->mObj->doDeleteTransaksi($this->mObj->_POST['idDelete']);
         if($process === true){
            $message    = 'Proses penghapusan data berhasil';
            $style      = 'notebox-done';
         }else{
            $message    = 'Proses penghapusan data gagal';
            $style      = 'notebox-warning';
         }
      }else{
         $message    = 'You don\'t have permission to access this page';
         $style      = 'notebox-warning';
      }

      return compact('url', 'message', 'style', 'data');
   }
}
?>