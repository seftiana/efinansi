<?php

class DoLogin extends HtmlResponse
{
	function TemplateModule()
	{
	}
	function ProcessRequest()
	{

		if (Security::Instance()->Login($_REQUEST['username'] . '', $_REQUEST['password'] . '', $_REQUEST['hashed'] . '' == 1))
		{

			// redirect to proper place
			$module = 'home';
			$submodule = 'home';
			$action = 'view';
			$type = 'html';
			Log::Instance()->SendLog('Proses Login Sukses');

			if($_REQUEST['back_to']!="")
			   $this->RedirectTo($_REQUEST['back_to']);
         else
   			$this->RedirectTo(Dispatcher::Instance()->GetUrl($module, $submodule, $action, $type));

			return;
		}
		else
		{
			Log::Instance()->SendLog('Proses Login Gagal');
			$this->RedirectTo(Dispatcher::Instance()->GetUrl('login_default', 'login', 'view', 'html') . '&fail=1');

			return;
		}

		return NULL;
	}
	function ParseTemplate($data = NULL)
	{
	}
}
?>