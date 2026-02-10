<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user/business/AppUser.class.php';

class ProcessUpdatePassword
{
	function UpdatePassword() 
	{
		$userObj = new AppUser();
		
		if (isset($_POST['btnganti'])) 
		{
			
			if (($_POST['passbaru1'] == "") and ($_POST['passbaru2'] == "")) 
			{
				$urlRedirect = Dispatcher::Instance()->GetUrl('user', 'changePassword', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('kosong') . '&usr=' . Dispatcher::Instance()->Encrypt($_POST['usr']);
			}
			elseif ($_POST['passbaru1'] != $_POST['passbaru2']) 
			{
				$urlRedirect = Dispatcher::Instance()->GetUrl('user', 'changePassword', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('tidak sama') . '&usr=' . Dispatcher::Instance()->Encrypt($_POST['usr']);
			}
			else
			{
				$update = $userObj->DoUpdatePasswordUser($_POST['passbaru1'], $_POST['usr']);
				
				if ($update == true) 
				{
					$urlRedirect = Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('upass_berhasil');
					Log::Instance()->SendLog('Proses Pengubahan Password Sukses');
				}
				else
				{
					$urlRedirect = Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('upass_gagal');
					Log::Instance()->SendLog('Proses Pengubahan Password Gagal');
				}
			}
		}
		else
		{
			$urlRedirect = Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html');
		}
		
		return $urlRedirect;
	}
}
?>
