<?php

/**
 * ViewRkaklSumberDana.html.class.php
 * @copyright 2011 gamatechno
 */

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
	'module/rkakl_sumber_dana/business/RkaklSumberDana.class.php';

/**
 * class ViewRkaklSumberDana
 * untuk menangani tampilan view data / list data
 */
class ViewRkaklSumberDana extends HtmlResponse
{

	/**
	 * variabel
	 */

	protected  $Pesan;

	public function TemplateModule()
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
			'module/rkakl_sumber_dana/template');
		$this->SetTemplateFile('view_rkakl_sumber_dana.html');
	}

	public function ProcessRequest()
	{
		$Obj = new RkaklSumberDana();

		if($_POST || isset($_GET['cari'])) {
			if(isset($_POST['sumber_dana_nama'])) {
				$sumber_dana_nama = $_POST['sumber_dana_nama'];
			} elseif(isset($_GET['sumber_dana_nama'])) {
				$sumber_dana_nama = Dispatcher::Instance()->Decrypt($_GET['sumber_dana_nama']);
			} else {
				$sumber_dana_nama = '';
			}
		}

		/**
		 * sistem paging
		 */
		$totalData = $Obj->GetCountRkaklSumberDana($sumber_dana_nama);
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}
		$data = $Obj->GetRkaklSumberDana($sumber_dana_nama, $startRec, $itemViewed);

		$url = Dispatcher::Instance()->GetUrl(
					Dispatcher::Instance()->mModule,
					Dispatcher::Instance()->mSubModule,
					Dispatcher::Instance()->mAction,
					Dispatcher::Instance()->mType .
					'&sumber_dana_nama=' . Dispatcher::Instance()->Encrypt($sumber_dana_nama) .
					'&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top',
			array($itemViewed, $totalData, $url, $currPage), Messenger::CurrentRequest);

		/**
		 * end sistem paging
		 */

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan =(!empty($msg[0][1])) ? $msg[0][1] : '';
		$this->css = (!empty($msg[0][2])) ? $msg[0][2] :'';

		$return['data'] = $data;
		$return['start'] = $startRec+1;
		$return['search']['sumber_dana_nama'] = $sumber_dana_nama;

		return $return;
	}

	public function ParseTemplate($data = NULL)
	{
		$search = $data['search'];
		$this->mrTemplate->AddVar('content', 'SUMBER_DANA_NAMA', $search['sumber_dana_nama']);

		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
			Dispatcher::Instance()->GetUrl('rkakl_sumber_dana',
			'RkaklSumberDana', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'URL_ADD',
			Dispatcher::Instance()->GetUrl('rkakl_sumber_dana',
			'inputRkaklSumberDana', 'view', 'html'));

		if($this->Pesan) {
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
		}

		if (empty($data['data'])) {
			$this->mrTemplate->AddVar('data_sumber_dana', 'IS_EMPTY', 'YES');
		} else {
			//$decPage = Dispatcher::Instance()->Decrypt($_REQUEST['page']);
			//$encPage = Dispatcher::Instance()->Encrypt($decPage);
			$this->mrTemplate->AddVar('data_sumber_dana', 'IS_EMPTY', 'NO');
			$dataK = $data['data'];

			/**
			 * property untuk proses hapus data
			 */

			$label = "Sumber Dana";
			$urlDelete = Dispatcher::Instance()->GetUrl('rkakl_sumber_dana',
					'deleteRkaklSumberDana', 'do', 'html');
			$urlReturn = Dispatcher::Instance()->GetUrl('rkakl_sumber_dana',
					'RkaklSumberDana','view', 'html');

			Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html',
			array($label, $urlDelete, $urlReturn),Messenger::NextRequest);

			$this->mrTemplate->AddVar('content', 'URL_DELETE',
		 	Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));

			for ($i=0; $i<sizeof($dataK); $i++) {
				$no = $i+$data['start'];
				$dataK[$i]['number'] = $no;
				if ($no % 2 != 0) $dataK[$i]['class_name'] = 'table-common-even';
				else $dataK[$i]['class_name'] = '';

				if($i == 0) $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
				if($i == sizeof($dataK)-1) $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
				$idEnc = Dispatcher::Instance()->Encrypt($dataK[$i]['sumber_dana_id']);

				$dataK[$i]['url_edit'] = Dispatcher::Instance()->GetUrl('rkakl_sumber_dana',
					'inputRkaklSumberDana', 'view', 'html') .
					'&dataId=' .$idEnc;

				$this->mrTemplate->AddVars('data_sumber_dana_item', $dataK[$i], 'DATA_');
				$this->mrTemplate->parseTemplate('data_sumber_dana_item', 'a');
			}
		}
	}
}