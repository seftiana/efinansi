<?php
/**
* @module jurnal_penerimaan
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal_penerimaan/business/AppReferensiTransaksi.class.php';

class PopupReferensiTransaksi extends HtmlResponse {

   protected $data;
   protected $search;

   protected $Ref;


   function PopupReferensiTransaksi () { //constructor
      $this->Ref = new AppReferensiTransaksi;
   }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/jurnal_penerimaan/template');
      $this->SetTemplateFile('popup_referensi_transaksi.html');
   }

    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }



   function ProcessRequest() {


	  $this->data['referensi'] = '';

	  $search='';
	  if(isset($_POST['data'])) {
	     if(is_object($_POST['data']))
	        $this->data=$_POST['data']->AsArray();
		 else
		    $this->data = $_POST['data'];



	  }elseif(isset($_GET['data'])) {
	     if(is_object($_GET['data']))
	        $this->data=$_GET['data']->AsArray();
		 else
		    $this->data = $_GET['data'];
	  }

      $itemViewed = 20;
      $currPage = 1;
      $startRec = 0 ;
      if(isset($_GET['page'])) {
         $currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $startRec =($currPage-1) * $itemViewed;
      }

	  $dataGrid  = $this->Ref->GetData($startRec,$itemViewed,$this->data['referensi']);
	  $totalData = $this->Ref->GetCount($this->data['referensi']);






	  //$dataProgram = $ProgramObj->GetDataProgram($startRec,$itemViewed, $this->data['program'],$is_cari);

      $url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule,
               Dispatcher::Instance()->mSubModule,
               Dispatcher::Instance()->mAction,
               Dispatcher::Instance()->mType).'&data[referensi]='.$this->data['referensi'];
	  $dest = "popup-subcontent";
      Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top',
         array($itemViewed,$totalData, $url, $currPage, $dest),
         Messenger::CurrentRequest);

      $return['dataGrid'] = $dataGrid;
      $return['start'] = $startRec+1;

	return $return;
   }

   function ParseTemplate($data = NULL) {
      $this->mrTemplate->AddVar('content', 'SEARCH_REFERENSI', $this->data['referensi']);
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('jurnal_penerimaan', 'referensiTransaksi', 'popup', 'html').'&tipe='.$data['tipe'] );

	  if (isset ($data['msg'])) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
		 if($data['msg']['action']=='msg')
		   $class='notebox-done';
		 else
		   $class = 'notebox-warning';

		 $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);

      }



      if (empty($data['dataGrid'])) {
         $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'YES');
      } else {


         $this->mrTemplate->AddVar('data_grid', 'IS_EMPTY', 'NO');
         $dataGrid = $data['dataGrid'];
		 $i=0;
		 $no=$data['start'];
         for ($i=0; $i<sizeof($dataGrid); $i++) {
			   $dataGrid[$i]['no']=$no;
			   $no++;

               if(!$dataGrid[$i]['isParent']) {
                  $dataGrid[$i]['set_parent'] ='';
				  $dataGrid[$i]['class_name'] = 'table-common-even';
			   } else {
			      $dataGrid[$i]['class_name'] = '';
			   }

            $dataGrid[$i]['catatan_js'] = str_replace(array("\r\n","\r","\n"),'\n',htmlentities($dataGrid[$i]['catatan']));
			   $dataGrid[$i]['catatan'] = str_replace(array("\r\n","\r","\n"),'<br/>',htmlentities($dataGrid[$i]['catatan']));
			   $dataGrid[$i]['tanggal_view'] = $this->Ref->date2string($dataGrid[$i]['tanggal']);
			   $dataGrid[$i]['nilai_view'] = number_format($dataGrid[$i]['nilai'],2,',','.');


			   //$idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['program_id']);

               $this->mrTemplate->AddVars('data_item', $dataGrid[$i], 'DATA_');
               $this->mrTemplate->parseTemplate('data_item', 'a');
		}
	}

   }
}
?>