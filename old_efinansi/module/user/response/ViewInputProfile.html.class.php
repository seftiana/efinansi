<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user/business/AppUser.class.php';

class ViewInputProfile extends HtmlResponse
{
	function TemplateModule() 
	{
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/user/template');
		$this->SetTemplateFile('input_profile.html');
	}
	function ProcessRequest() 
	{
		$userObj = new AppUser();
		$userId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$dataUser = $userObj->GetDataUserById($userId);
		$return['dataUser'] = $dataUser;
		
		return $return;
	}
	function ParseTemplate($data = NULL) 
	{
		
		if (isset($_GET['err'])) 
		{
			$error = Dispatcher::Instance()->Decrypt($_GET['err']);
			
			if ($error == 'realname') 
			{
				$this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
				$this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', 'Data Nama Lengkap tidak boleh kosong');
			}
		}
		$dataUser = $data['dataUser'];
		$this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('user', 'updateProfile', 'do', 'html'));
		$this->mrTemplate->AddVar('content', 'REALNAME', $dataUser[0]['real_name']);
		$this->mrTemplate->AddVar('content', 'DESKRIPSI', $dataUser[0]['description']);
	}
}
?>
