<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user/business/AppUser.class.php';

class DoUpdateProfile extends HtmlResponse
{
	function TemplateModule() 
	{
	}
	function ProcessRequest() 
	{
		$idDec = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$userObj = new AppUser();

		/*$dataUser = $userObj->GetDataUserById($_SESSION['userid']);*/

		//print_r($_POST);exit;
		
		if (isset($_POST['btnsimpan'])) 
		{
			
			if ($_POST['realname'] != "") 
			{
				$dataUser = $userObj->GetDataUserById($idDec);
				$update = $userObj->DoUpdateProfile($_POST['realname'], $_POST['deskripsi'], $dataUser[0]['user_id']);
				
				if ($update == true) 
				{
					$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('up_berhasil'));
				}
				else
				{
					$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('up_gagal'));
				}
			}
			else
			{
				
				if (($_POST['returnPage']) != "") 
				{
					$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . '&usr=' . $_REQUEST['usr'] . '&err=' . Dispatcher::Instance()->Encrypt('realname'));
				}
				else
				{
					$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'inputProfile', 'view', 'html') . '&usr=' . $_REQUEST['usr'] . '&err=' . Dispatcher::Instance()->Encrypt('realname'));
				}
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
							
							if (($_POST['returnPage']) != "") 
							{
								$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . '&usr=' . $_REQUEST['usr'] . '&err=' . Dispatcher::Instance()->Encrypt('realname'));
							}
							else
							{
								$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('berhasil'));
							}
						}
						else
						{
							$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('gagal'));
						}
					}
					else
					{
						$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'updatePassword', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('password'));
					}
				}
				else
				{
					$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'updatePassword', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('tidak sama'));
				}
			}
			else
			{
				$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'updatePassword', 'view', 'html') . '&err=' . Dispatcher::Instance()->Encrypt('kosong'));
			}
		}
		else
		{
			
			if (($_POST['returnPage']) != "") 
			{
				$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'user', 'view', 'html') . '&usr=' . $_REQUEST['usr'] . '&err=' . Dispatcher::Instance()->Encrypt('realname'));
			}
			else
			{
				$this->RedirectTo(Dispatcher::Instance()->GetUrl('user', 'profile', 'view', 'html'));
			}
		}
		
		return NULL;
	}
	function ParseTemplate($data = NULL) 
	{
	}
}
?>
