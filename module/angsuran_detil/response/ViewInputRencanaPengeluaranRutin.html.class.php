<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/angsuran_detil/business/RencanaPengeluaran.class.php';

class ViewinputRencanaPengeluaranRutin extends HtmlResponse
{
   function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/angsuran_detil/template');
      $this->SetTemplateFile('input_rencana_pengeluaran_rutin.html');
   }

   function ProcessRequest()
   {
      $messenger     = Messenger::Instance()->Receive(__FILE__);
      $mObj          = new RencanaPengeluaran();
      $queryString   = $mObj->_getQueryString();
      $parameter     = array();
      $par           = explode('|', $mObj->_GET['par']);
      $parameter['kegiatan']['kegiatandetail_id']     = $par[0];
      $parameter['kegiatan']['subkegiatan_id']        = $par[1];
      $parameter['kegiatan']['subkegiatan_nama']      = $par[2];
      $parameter['kegiatan']['jenis_kegiatan_nama']   = $par[3];
      $parameter['kegiatan']['tahun_anggaran']        = $par[4];
      $parameter['kegiatan']['unit_kerja']            = $par[5];
      $parameter['action']                            = $par[6];
      $dataSubKegiatan     = $mObj->GetSubKegiatanDetail($parameter['kegiatan']['kegiatandetail_id']);
      $dataSubKegiatan['action']    = $parameter['action'];
      $dataKomponen        = $mObj->GetDataDetailBelanja($parameter['kegiatan']['kegiatandetail_id']);

      if($messenger){
         $messengerData    = $messenger[0][0];
         $messengerMsg     = $messenger[0][1];
         $messengerStyle   = $messenger[0][2];
         $detailBelanja    = $messengerData['data']['komponen'];

         if(!empty($detailBelanja)){
            $index         = 0;
            unset($dataKomponen);
            foreach ($detailBelanja as $komp) {
               if(isset($komp['data_id'])){
                  $dataKomponen[$index]['dataId']     = $komp['data_id'];
               }
               $dataKomponen[$index]['id']            = $komp['id'];
               $dataKomponen[$index]['kode']          = $komp['kode'];
               $dataKomponen[$index]['basIdKomponen'] = $komp['bas_id_komponen'];
               $dataKomponen[$index]['nama']          = $komp['nama'];
               $dataKomponen[$index]['jumlah']        = $komp['jumlah'];
               $dataKomponen[$index]['formula']       = $komp['formula'];
               $dataKomponen[$index]['hasilFormula']  = $komp['hasil_formula'];
               $dataKomponen[$index]['satuan']        = $komp['satuan'];
               $dataKomponen[$index]['biayaMax']      = $komp['biaya_max'];
               $dataKomponen[$index]['isSbu']         = $komp['is_sbu'];
               $dataKomponen[$index]['biaya']         = $komp['biaya'];
               $dataKomponen[$index]['deskripsi']     = $komp['deskripsi'];
               $dataKomponen[$index]['rencanaPengeluaranId'] = $komp['rencanapengeluaran_id'];
               $dataKomponen[$index]['basId']         = $komp['bas_id'];
               $dataKomponen[$index]['basKode']       = $komp['bas_kode'];
               $dataKomponen[$index]['basNama']       = $komp['bas_nama'];
               $dataKomponen[$index]['makId']         = $komp['mak_id'];
               $dataKomponen[$index]['makKode']       = $komp['mak_kode'];
               $dataKomponen[$index]['makNama']       = $komp['mak_nama'];
               $dataKomponen[$index]['akunId']        = $komp['akun_id'];
               $dataKomponen[$index]['akunKode']      = $komp['akun_kode'];
               $dataKomponen[$index]['akunNama']      = $komp['akun_nama'];
               $dataKomponen[$index]['komponenNominal']  = $komp['komponen_nominal'];
               $dataKomponen[$index]['totalBiaya']       = $komp['total_biaya'];
               $dataKomponen[$index]['sumberDanaId']     = $komp['sumber_dana_id'];
               $dataKomponen[$index]['status']           = $komp['status'];
               $dataKomponen[$index]['satuanApprove']    = $komp['satuan_approve'];
               $dataKomponen[$index]['nominalApprove']   = $komp['nominal_approve'];
               $dataKomponen[$index]['totalApprove']     = $komp['total_approve'];
               // if(isset($detailBelanja[$komp['id']])){
               //    $dataKomponen[$index]['biaya']      = $detailBelanja[$komp['id']]['biaya'];
               //    $dataKomponen[$index]['jumlah']     = $detailBelanja[$komp['id']]['jumlah'];
               //    $dataKomponen[$index]['deskripsi']  = $detailBelanja[$komp['id']]['deskripsi'];
               //    $dataKomponen[$index]['checked']    = 'checked';
               // }

               $index++;
            }
         }
      }


      $return['data_subkegiatan']      = $mObj->ChangeKeyName($dataSubKegiatan);
      $return['data_komponen']         = $mObj->ChangeKeyName($dataKomponen);
      $return['query_string']          = $queryString;
      $return['message']               = $messengerMsg;
      $return['style']                 = $messengerStyle;
      $return['komponen']['data']      = json_encode($mObj->ChangeKeyName($dataKomponen));
      return $return;
   }

   function ParseTemplate($data = NULL)
   {
	  echo"<pre>";var_dump('tes');echo"</pre>";
	   
      $komponen               = $data['komponen'];
      $dataSubKegiatan        = $data['data_subkegiatan'];
      $dataKomponen           = $data['data_komponen'];
      $queryString            = $data['query_string'];
      $message                = $data['message'];
      $style                  = $data['style'];

      $urlReturn              = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'AngsuranDetil',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $popupkomponen          = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         'PopupKomponen',
         'view',
         'html'
      ).'&kegiatan='.Dispatcher::Instance()->Encrypt($dataSubKegiatan['sub_kegiatan_id']).'&unit_id='.Dispatcher::Instance()->Encrypt($dataSubKegiatan['unit_id']);

      if ($dataSubKegiatan['action']=='edit') {
         $url        = "updateRencanaPengeluaran";
         $tambah     = "Ubah";
      } else {
         $url        = "addRencanaPengeluaran";
         $tambah     = "Tambah";
      }
      $urlAction     = Dispatcher::Instance()->GetUrl(
         Dispatcher::Instance()->mModule,
         $url,
         'do',
         'json'
      ).'&'.$queryString;

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVar('content', 'POPUP_KOMPONEN', $popupkomponen);
      $this->mrTemplate->AddVars('DATA_RKAT', $dataSubKegiatan);
      $this->mrTemplate->AddVars('content', $komponen, 'KOMPONEN_');

      if (isset ($message)) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS', $style);
      }
   }
}
?>