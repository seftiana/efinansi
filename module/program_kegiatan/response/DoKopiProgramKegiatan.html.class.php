<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/'.Dispatcher::Instance()->mModule.'/business/ProgramKegiatan.class.php';

class DoKopiProgramKegiatan extends HtmlResponse
{
   function TemplateModule ()
   {
   }
   
   function ProcessRequest ()
   {
      $Obj        = new ProgramKegiatan;
      $Obj->KopiProgramKegiatan($_GET['idTahun']->Integer()->Raw());
      $this->RedirectTo(Dispatcher::Instance()->GetUrl(
         'program_kegiatan', 
         'programKegiatan', 
         'view', 
         'html').'&ascomponent='.$_GET['ascomponent']
      );
   }

   function ParseTemplate ($data = NULL)
   {
   }
}
?>
