<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user/business/AppUser.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user/business/AppGroup.class.php';

class ViewProfile extends HtmlResponse
{
	function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/user/template');
		$this->SetTemplateFile('view_profile.html');
	}
	function ProcessRequest() 
	{
		$userObj = new AppUser();
		$groupObj = new AppGroup();
		$userId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$dataUser = $userObj->GetDataUserById($userId);
		$dataGroup = $groupObj->GetDataGroupById($dataUser[0]['group_id']);
		$return['dataUser'] = $dataUser;
		$return['dataGroup'] = $dataGroup;
		
		return $return;
	}
	function ParseTemplate($data = NULL) 
	{
		$dataUser = $data['dataUser'];
		$dataGroup = $data['dataGroup'];
		$instUser = $data['instUser'];
		$this->mrTemplate->AddVar('content', 'PENGGUNA', $dataUser[0]['real_name']);
		$this->mrTemplate->AddVar('content', 'NO_PEGAWAI', $dataUser[0]['no_pegawai']);
		$this->mrTemplate->AddVar('content', 'URL_UPDATE_PASSWORD', Dispatcher::Instance()->GetUrl('user', 'updatePassword', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'PROFILE_URL_EDIT', Dispatcher::Instance()->GetUrl('user', 'inputProfile', 'view', 'html'));
		$this->mrTemplate->AddVar('content', 'USERNAME', $dataUser[0]['user_name']);
		$this->mrTemplate->AddVar('content', 'DESKRIPSI', $dataUser['0']['description']);
		
		if ($dataUser[0]['is_active'] == 'Yes') 
		{
			$dataUser[0]['status'] = 'aktif';
		}
		else
		{
			$dataUser[0]['status'] = 'tidak aktif';
		}
		$this->mrTemplate->AddVar('content', 'STATUS', $dataUser[0]['status']);
		$this->mrTemplate->AddVar('content', 'NAMA_GROUP', $dataUser[0]['group_name']);
		$dataGroup = $data['dataGroup'];
		$len = sizeof($dataGroup);
		$menuName = '';
		$idGroup = '';
		$no = 0;

		#$menuName=$dataGroup[0]['menu_name'];
		
		for ($i = 0;$i < $len;$i++) 
		{
			$no++;
			
			if ($dataGroup[$i]['menu_name'] != $menuName) 
			{
				$menuBaru[$no].= '<strong>' . $dataGroup[$i]['menu_name'] . '</strong><br>' . '&nbsp;&nbsp;' . $dataGroup[$i]['sub_menu'] . '<br>';
				$menuName = $dataGroup[$i]['menu_name'];
			}
			else $menuBaru[$no].= '&nbsp;&nbsp;' . $dataGroup[$i]['sub_menu'] . '<br>';
		}
		
		for ($k = 0;$k < count($menuBaru);$k++) 
		{
			$this->mrTemplate->AddVar('list_hak_akses', 'HAK_AKSES', $menuBaru[$k]);
			$this->mrTemplate->parseTemplate('list_hak_akses', 'a');
		}
		
		if (isset($_REQUEST['err'])) 
		{
			$error = Dispatcher::Instance()->Decrypt($_REQUEST['err']);
			$classWarning = "notebox-done";
			
			if ($error == 'gagal') 
			{
				$pesan = 'Penggantian password gagal';
				$classWarning = "notebox-warning";
			}
			else 
			if ($error == 'berhasil') 
			{
				$pesan = 'Penggantian password berhasil';
			}
			else 
			if ($error == 'up_gagal') 
			{
				$pesan = 'Ubah Profile gagal';
				$classWarning = "notebox-warning";
			}
			else 
			if ($error == 'up_berhasil') 
			{
				$pesan = 'Ubah Profile berhasil';
			}
			$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
			$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $pesan);
			$this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $classWarning);
		}
	}
}
?>
