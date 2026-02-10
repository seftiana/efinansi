<?php
/**
* ================= doc ====================
* FILENAME     : DoSavePagu.rest.class.php
* @package     : DoSavePagu
* scope        : PUBLIC
* @author      : Eko Susilo
* @created     : 2014-01-02
* @modified    : 2014-01-02
* @analysts    : Dyah Fajar
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/PaguAnggaranUnitPerMak.class.php';

class DoSavePagu extends RestResponse
{
   public function get()
   {
      # code...
   }

   public function post()
   {
      $mObj       = new PaguAnggaranUnitPerMak();
      $unitKode         = $mObj->_POST['unit_kode'];
      $tahunAnggaran    = $mObj->_POST['tahun_anggaran'];
      $programKode      = $mObj->_POST['program_kode'];
      $kegiatanKode     = $mObj->_POST['kegiatan_kode'];
      $outputKode       = $mObj->_POST['output_kode'];
      $komponenKode     = $mObj->_POST['komponen_kode'];
      $makKode          = $mObj->_POST['mak_kode'];
      $sumberDana       = $mObj->_POST['sumber_dana'];
      $nominal          = $mObj->_POST['nominal'];
      $requestData      = array();

      $dataUnitKerja       = $mObj->ChangeKeyName($mObj->GetReferensiUnitKerja($unitKode));
      $dataTahunAnggaran   = $mObj->ChangeKeyName($mObj->GetReferensiTahunAnggaran($tahunAnggaran));
      $dataProgram         = $mObj->ChangeKeyName($mObj->GetReferensiProgram($programKode));
      $dataOutput          = $mObj->ChangeKeyName($mObj->GetReferensiOutput($outputKode, $kegiatanKode));
      $dataKomponen        = $mObj->ChangeKeyName($mObj->GetReferensiKomponen($komponenKode));
      $dataMak             = $mObj->ChangeKeyName($mObj->GetReferensiMak($makKode));
      $dataSumberDana      = $mObj->ChangeKeyName($mObj->GetReferensiSumberDana($sumberDana));

      if($dataUnitKerja === NULL or $unitKode == ''){
         $err[]         = 'Data unit kerja tidak di temukan';
      }
      if($tahunAnggaran == '' OR $dataTahunAnggaran === NULL){
         $err[]         = 'Data tahun anggaran tidak di temukan';
      }
      if($programKode == '' OR $dataProgram === NULL){
         $err[]         = 'Data program tidak di temukan';
      }
      if($kegiatanKode == '' OR $dataOutput === NULL){
         $err[]         = 'Data kegiatan tidak di temukan';
      }
      if($outputKode == '' OR $dataOutput === NULL){
         $err[]         = 'Data Output tidak di temukan';
      }
      if($komponenKode == '' OR $dataKomponen === NULL){
         $err[]         = 'Data Komponen tidak di temukan';
      }
      if($makKode == '' OR $dataMak === NULL){
         $err[]         = 'Data Mata Anggaran Kegiatan tidak di temukan';
      }
      if($nominal == '' OR $nominal <= 0){
         $err[]         = 'Isikan nominal pagu';
      }
      if($sumberDana != '' AND $dataSumberDana === NULL){
         $err[]         = 'Data sumber dana tidak di temukan';
      }

      $requestData['unitkerja']           = $dataUnitKerja['unitkerja_id'];
      $requestData['unitkerja_label']     = $dataUnitKerja['unitkerja_nama'];
      $requestData['tahun_anggaran']      = $dataTahunAnggaran['id'];
      $requestData['program_id']          = $dataProgram['id'];
      $requestData['kegiatan_id']         = $dataOutput['kegiatan_id'];
      $requestData['kegiatan']            = $dataOutput['kegiatan_nama'];
      $requestData['output_id']           = $dataOutput['id'];
      $requestData['output']              = $dataOutput['name'];
      $requestData['komponen_id']         = $dataKomponen['id'];
      $requestData['komponen']            = $dataKomponen['name'];
      $requestData['mak_id']              = $dataMak['id'];
      $requestData['mak']                 = $dataMak['name'];
      $requestData['sumber_dana']         = (is_null($dataSumberDana)) ? '' : $dataSumberDana['id'];
      $requestData['sumber_dana_label']   = (is_null($dataSumberDana)) ? '' : $dataSumberDana['name'];
      $requestData['nominal']             = $nominal;
      $requestData['status']              = 'T';

      $checkPagu           = $mObj->CheckPaguService($requestData);
      $checkPaguAnggUnit   = $mObj->CheckPaguAnggaranUnit($requestData);

      /*if($checkPagu === false){
         $err[]      = 'Data Pagu Anggaran sudah ada di dalam sistem. Check kembali unit, program, kegiatan, output, kegiatan dan MAK yang Anda pakai';
      }*/
      // jika tidak ada pagu anggaran per unit tampilkan pesan error
      /*if((int)$checkPaguAnggUnit['count'] === 0){
         $err[]      = 'Anda belum mendefinisikan unit kerja atau Pagu anggaran belum di set untuk unit kerja yang Anda pilih.';
      }
      if((int)$checkPaguAnggUnit['count'] <> 0 AND $checkPaguAnggUnit['budget'] < $nominal){
         $err[]      = 'Nominal Pagu Anggaran Unit per MAK yang Anda set melebihi batas nilai Pagu Anggaran Unit untuk Unit Kerja yang Anda Definisikan. Uang Persediaan tersisa <br /><strong>Rp. '.number_format($checkPaguAnggUnit['budget'], 0, ',','.').',-</strong>';
      }*/

      if(isset($err)){
         return array(
            'status' => 204, 
            'message' => $err[0], 
            'data' => $mObj->_POST
         );
      }else{
         if((int)$checkPagu['count'] === 0){
            $process    = $mObj->DoAddPaguAnggaranUnit(
               $requestData['tahun_anggaran'], 
               $requestData['unitkerja'], 
               $requestData['nominal'], 
               $requestData['sumber_dana'], 
               $requestData['mak_id'], 
               $requestData['program_id'], 
               $requestData['kegiatan_id'], 
               $requestData['output_id'], 
               $requestData['komponen_id'], 
               $requestData['status']
            );
         }else{
            $idPagu     = $checkPagu['id'];
            $process    = $mObj->DoUpdatePaguUsulan(array(
               'id' => $idPagu, 
               'nominal' => $requestData['nominal']
            ));
         }

         if($process === true){
            return array(
               'status' => 201, 
               'message' => GTFWConfiguration::GetValue('message', 'msg201'), 
               'data' => $mData
            );
         }else{
            return array(
               'status' => 406, 
               'message' => GTFWConfiguration::GetValue('message', 'msg406'), 
               'data' => $mData
            );
         }
      }
   }

   public function put()
   {
      # code...
   }

   public function delete()
   {
      # code...
   }
}
?>