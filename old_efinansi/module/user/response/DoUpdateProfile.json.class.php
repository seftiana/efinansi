<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user/business/AppUser.class.php';

class DoUpdateProfile extends JsonResponse
{
	function TemplateModule() 
	{
	}
	function ProcessRequest() 
	{
		$idDec = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$userObj = new AppUser();
		
		if (isset($_POST['btnsimpan'])) 
		{
			
			if ($_POST['realname'] != "") 
			{
				$dataUser = $userObj->GetDataUserById($idDec);
				$update = $userObj->DoUpdateProfile($_POST['realname'], $_POST['deskripsi'], $dataUser[0]['user_id']);
				
				if ($update === true) 
				{
					
					return array(
						'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " ' . Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('up_berhasil') . '&ascomponent=1")'
					);
				}
				else
				{
					
					return array(
						'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " ' . Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('up_gagal') . '&ascomponent=1")'
					);
				}
			}
			else
			{
				
				return array(
					'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " ' . Dispatcher::Instance()->GetUrl('user', 'inputProfile', 'view', 'html') . '&usr=' . $_REQUEST['usr'] . '&err=' . Dispatcher::Instance()->Encrypt('realname') . '&ascomponent=1")'
				);
			}
		}
		elseif (isset($_POST['btnganti'])) 
		{
			
			if ($_POST['passlama'] != "" and $_POST['passbaru1'] != "" and $_POST['passbaru2'] != "") 
			{
				
				if ($_POST['passbaru1'] == $_POST['passbaru2']) 
				{
					$dataUser = $userObj->GetDataUserById($idDec);
					$passlama = md5($_POST['passlama']);
					
					if ($dataUser[0]['password'] == $passlama) 
					{
						$update = $userObj->DoUpdatePasswordUser($_POST['passbaru1'], $idDec);
						
						if ($update == true) 
						{
							Log::Instance()->SendLog('Perubahan Password Pribadi Sukses');
							return array(
								'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " ' . Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('berhasil') . '&ascomponent=1")'
							);
						}
						else
						{
							Log::Instance()->SendLog('Perubahan Password Pribadi Gagal');
							return array(
								'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " ' . Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('gagal') . '&ascomponent=1")'
							);
						}
					}
					else
					{
						Log::Instance()->SendLog('Password Tidak sama');
						return array(
							'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
                  " ' . Dispatcher::Instance()->GetUrl('user', 'updatePassword', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('password') . '&ascomponent=1")'
						);
					}
				}
				else
				{
					
					return array(
						'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " ' . Dispatcher::Instance()->GetUrl('user', 'updatePassword', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('tidak sama') . '&ascomponent=1")'
					);
				}
			}
			else
			{
				Log::Instance()->SendLog('Password yang dimasukkan kosong');
				return array(
					'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " ' . Dispatcher::Instance()->GetUrl('user', 'updatePassword', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('kosong') . '&ascomponent=1")'
				);
			}
		}
		else
		{
			
			return array(
				'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element",
               " ' . Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html') . '&ascomponent=1")'
			);
		}
		
		return NULL;
	}
	function ParseTemplate($data = NULL) 
	{
	}
}
?>
