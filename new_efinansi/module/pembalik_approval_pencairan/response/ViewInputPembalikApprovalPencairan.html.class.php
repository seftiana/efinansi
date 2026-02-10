<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/pembalik_approval_pencairan/business/AppPembalikApprovalPencairan.class.php';

class ViewInputPembalikApprovalPencairan extends HtmlResponse {
   var $Data;
   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 'module/pembalik_approval_pencairan/template');
      $this->SetTemplateFile('input_pembalik_approval_pencairan.html');
   }

   function ProcessRequest() {
      $mObj       = new AppPembalikApprovalPencairan();
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $dataId           = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $queryString      = $mObj->_getQueryString();
      $queryRequest     = preg_replace('/(data_id=[\d]+)/', '', $queryString);
      $queryRequest     = preg_replace('/(search=[\d]+)/', '', $queryRequest);
      $queryRequest     = preg_replace('/[\&]$/', '', $queryRequest);
      $queryRequest     = preg_replace('/\&[\&]+/', '&', $queryRequest);
      $message          = $style = $messengerData = NULL;
      if($messenger){
         $messengerData = $messenger[0][0];
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
         $dataId        = $messengerData['dataId'];
      }

      $dataApproval     = $mObj->getDataDetail($dataId);
      $dataKomponen     = $mObj->getKomponenPencairan($dataId);

      $return['data_realisasi']     = $dataApproval;
      $return['komponen']['data']   = json_encode((array)$dataKomponen);
      $return['query_string']       = $queryString;
      $return['query_request']      = $queryRequest;
      $return['message']            = $message;
      $return['style']              = $style;

      // $idDec   = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      // $Obj     = new AppPembalikApprovalPencairan();
      // $msg     = Messenger::Instance()->Receive(__FILE__);
      // $this->Pesan = $msg[0][1];
      // $this->Data = $msg[0][0];

      // $dataPembalikApprovalPencairan = $Obj->GetDataById($idDec);
      // //print_r($dataPembalikApprovalPencairan);
      // $arr_status = array();
      // $arr_status[0] = array('id' => 'Ya', 'name' => 'Ya');
      // $arr_status[1] = array('id' => 'Tidak', 'name' => 'Tidak');
      // Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'status', array('status', $arr_status, '', '-', ' style="width:100px;" id="status" onchange="enableDisableNominalApprove(this.value)"'), Messenger::CurrentRequest);

      // $return['decDataId'] = $idDec;
      // $return['dataPembalikApprovalPencairan'] = $dataPembalikApprovalPencairan;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $message             = $data['message'];
      $style               = $data['style'];
      $queryString         = $data['query_string'];
      $queryRequest        = $data['query_request'];
      $dataRealisasi       = $data['data_realisasi'];
      $dataKomponen        = $data['komponen'];
      $urlReturn           = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_pencairan',
         'PembalikApprovalPencairan',
         'view',
         'html'
      ).'&search=1&'.$queryRequest;

      $urlAction           = Dispatcher::Instance()->GetUrl(
         'pembalik_approval_pencairan',
         'updatePembalikApprovalPencairan',
         'do',
         'json'
      ).'&'.$queryString;

      if($dataRealisasi['nominal'] < 0){
         $dataRealisasi['nominal_label']  = number_format(abs($dataRealisasi['nominal']), 2, ',','.');
      }else{
         $dataRealisasi['nominal_label']  = number_format($dataRealisasi['nominal'], 2, ',', '.');
      }

      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      $this->mrTemplate->AddVars('content', $dataRealisasi);
      $this->mrTemplate->AddVars('content', $dataKomponen, 'KOMPONEN_');

      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }

      /*if ($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
      }
      $dataPembalikApprovalPencairan = $data['dataPembalikApprovalPencairan'];

      $this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN_LABEL', $dataPembalikApprovalPencairan['tahun_anggaran_label']);
      $this->mrTemplate->AddVar('content', 'UNITKERJA_LABEL', $dataPembalikApprovalPencairan['unitkerja_label']);
      $this->mrTemplate->AddVar('content', 'PROGRAM_LABEL', $dataPembalikApprovalPencairan['program_label']);
      $this->mrTemplate->AddVar('content', 'KEGIATAN_LABEL', $dataPembalikApprovalPencairan['kegiatan_label']);
      $this->mrTemplate->AddVar('content', 'SUBKEGIATAN_LABEL', $dataPembalikApprovalPencairan['subkegiatan_label']);
      $this->mrTemplate->AddVar('content', 'KETERANGAN', $dataPembalikApprovalPencairan['keterangan']);
      $arr_tanggal = explode("-", $dataPembalikApprovalPencairan['tanggal']);
      $tanggal = date("d-m-Y", mktime(1, 1, 1, $arr_tanggal[1], $arr_tanggal[2], $arr_tanggal[0]));
      $this->mrTemplate->AddVar('content', 'TANGGAL', $tanggal);
      $this->mrTemplate->AddVar('content', 'NOMOR', $dataPembalikApprovalPencairan['nomor']);
      $this->mrTemplate->AddVar('content', 'NOMINAL_LABEL', number_format($dataPembalikApprovalPencairan['nominal'], 0, ',', '.'));

      if($dataPembalikApprovalPencairan['status'] == "Ya" || $dataPembalikApprovalPencairan['status'] == "Tidak") {
         $this->mrTemplate->AddVar('content', 'STATUS_APPROVAL', "SUDAH");
         $this->mrTemplate->AddVar('approve', 'IS_APPROVE', "YES_OR_NO");
         $this->mrTemplate->AddVar('approve', 'STATUS', $dataPembalikApprovalPencairan['status']);
         $this->mrTemplate->AddVar('approve', 'NOMINAL_APPROVE_LABEL', number_format($dataPembalikApprovalPencairan['nominal_approve'], 0, ',', '.'));
      } else {
         $this->mrTemplate->AddVar('content', 'STATUS_APPROVAL', "BELUM");
         $this->mrTemplate->AddVar('approve', 'IS_APPROVE', "NOT_YET");
         $this->mrTemplate->AddVar('approve', 'NOMINAL_APPROVE', $dataPembalikApprovalPencairan['nominal_approve']);
      }

      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('pembalik_approval_pencairan', 'updatePembalikApprovalPencairan', 'do', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));

      $this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
      $this->mrTemplate->AddVar('content', 'PAGE', Dispatcher::Instance()->Decrypt($_GET['page']));*/
   }
}
?>
