<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/rencana_penerimaan_adjust_history/business/RencanaPenerimaanAdjustHistory.class.php';

#doc
#    classname:    ProcessAdjustment
#    scope:        PUBLIC
#
#/doc

class ProcessAdjustment
{
    #    internal variables
    public $obj;
    public $urlReturn;
    public $post;
    public $urlInput;
    protected $userId;
    public $cssDone = "notebox-done";
    public $cssFail = "notebox-warning";
    
    #    Constructor
    function __construct ()
    {
        # code...
        $this->userId       = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        $this->obj          = new RencanaPenerimaanAdjustHistory();
        $this->post         = $_POST->AsArray();
        $this->urlReturn    = Dispatcher::Instance()->GetUrl(
            'rencana_penerimaan_adjust_history',
            'HistoryAdjustmentRencanaPenerimaan',
            'view',
            'html'
        ); 
        
        $this->urlInput     = Dispatcher::Instance()->GetUrl(
            'rencana_penerimaan_adjust_history',
            'FormRencanaPenerimaanAdjust',
            'view',
            'html'
        );
    }
    
    function Update()
    {
        $this->data['user_id']  = $this->userId;
        $this->data['data_id']  = $this->post['id_data'];
        $this->data['total']    = $this->post['total_nominal'];
        $this->data['tpersen']  = $this->post['total_persen'];
        $this->data['id_adjustment'] = $this->post['adjust_id'];
        # persen inputan
        for($i=0; $i < count($this->post['persenpenerimaan']); $i++){
            if(empty($this->post['persenpenerimaan'][$i])){
                $this->post['persenpenerimaan'][$i] = 0;
            }
        }
        
        $this->data['pjanuariadjust']   = $this->post['persenpenerimaan'][0];
        $this->data['pfebruariadjust']  = $this->post['persenpenerimaan'][1];
        $this->data['pmaretadjust']     = $this->post['persenpenerimaan'][2];
        $this->data['papriladjust']     = $this->post['persenpenerimaan'][3];
        $this->data['pmeiadjust']       = $this->post['persenpenerimaan'][4];
        $this->data['pjuniadjust']      = $this->post['persenpenerimaan'][5];
        $this->data['pjuliadjust']      = $this->post['persenpenerimaan'][6];
        $this->data['pagustusadjust']   = $this->post['persenpenerimaan'][7];
        $this->data['pseptemberadjust'] = $this->post['persenpenerimaan'][8];
        $this->data['poktoberadjust']   = $this->post['persenpenerimaan'][9];
        $this->data['pnovemberadjust']  = $this->post['persenpenerimaan'][10];
        $this->data['pdesemberadjust']  = $this->post['persenpenerimaan'][11];
        
        for($i=0;$i<count($this->post['penerimaanbln']);$i++){
            if(empty($this->post['penerimaanbln'][$i])){
                $this->post['penerimaanbln'][$i]    = 0;
            }
        }
            
        $this->data['penerimaan_januari']   = $this->post['penerimaanbln'][0];
        $this->data['penerimaan_februari']  = $this->post['penerimaanbln'][1];
        $this->data['penerimaan_maret']     = $this->post['penerimaanbln'][2];
        $this->data['penerimaan_april']     = $this->post['penerimaanbln'][3];
        $this->data['penerimaan_mei']       = $this->post['penerimaanbln'][4];
        $this->data['penerimaan_juni']      = $this->post['penerimaanbln'][5];
        $this->data['penerimaan_juli']      = $this->post['penerimaanbln'][6];
        $this->data['penerimaan_agustus']   = $this->post['penerimaanbln'][7];
        $this->data['penerimaan_september'] = $this->post['penerimaanbln'][8];
        $this->data['penerimaan_oktober']   = $this->post['penerimaanbln'][9];
        $this->data['penerimaan_nopember']  = $this->post['penerimaanbln'][10];
        $this->data['penerimaan_desember']  = $this->post['penerimaanbln'][11];
            
        for ($i = 0; $i < count($this->post['jmlpenerimaan']); $i++)
        {
            # code...
            if(empty($this->post['jmlpenerimaan'][$i])){
                $this->post['jmlpenerimaan'][$i]    = 0;
            }
        }
        $this->data['adjust_januari']       = $this->post['jmlpenerimaan'][0];
        $this->data['adjust_februari']      = $this->post['jmlpenerimaan'][1];
        $this->data['adjust_maret']         = $this->post['jmlpenerimaan'][2];
        $this->data['adjust_april']         = $this->post['jmlpenerimaan'][3];
        $this->data['adjust_mei']           = $this->post['jmlpenerimaan'][4];
        $this->data['adjust_juni']          = $this->post['jmlpenerimaan'][5];
        $this->data['adjust_juli']          = $this->post['jmlpenerimaan'][6];
        $this->data['adjust_agustus']       = $this->post['jmlpenerimaan'][7];
        $this->data['adjust_september']     = $this->post['jmlpenerimaan'][8];
        $this->data['adjust_oktober']       = $this->post['jmlpenerimaan'][9];
        $this->data['adjust_nopember']      = $this->post['jmlpenerimaan'][10];
        $this->data['adjust_desember']      = $this->post['jmlpenerimaan'][11];
        
        if(isset($this->post['btnsimpan'])){
            $update     = $this->obj->UpdateAdjustmentRencanaPenerimaan($this->data);
            if($update){
                $pesan  = 'Berhasil melakukan update adjustment rencana penerimaan';
                Messenger::Instance()->
				Send(
				    'rencana_penerimaan_adjust_history',
				    'HistoryAdjustmentRencanaPenerimaan',
				    'view',
				    'html',
				    array(
				        $this->data,
				        $pesan,
				        $this->cssDone
				    ),
				    Messenger::NextRequest
				);
				return $this->urlReturn;
            }else{
                $pesan  = 'Gagal melakukan update adjustment rencana penerimaan';
                Messenger::Instance()->
				Send(
				    'rencana_penerimaan_adjust_history',
				    'FormRencanaPenerimaanAdjust',
				    'view',
				    'html',
				    array(
				        $this->data,
				        $pesan,
				        $this->cssDone
				    ),
				    Messenger::NextRequest
				);
				return $this->urlInput.'&dataId='.$this->post['adjust_id'];
            }
        }else{
           return $this->urlReturn; 
        }
        
        return $this->urlReturn;
    }
    
    function Approval()
    {
        $data   = $_GET['data'];
        list($id,$id_rencana,$nominal)  = explode('|',$data);
        
        $status     = 'Approved';
        $user_id    = $this->userId;
        
        if(isset($data) AND $data != ''){
            $approve    = $this->obj->DoApprovalAdjustment($id,$user_id,$status,$nominal,$id_rencana);
            if($approve){
                $pesan  = 'Berhasil melaksanakan approval adjustment rencana penerimaan';
                Messenger::Instance()->
				Send(
				    'rencana_penerimaan_adjust_history',
				    'HistoryAdjustmentRencanaPenerimaan',
				    'view',
				    'html',
				    array(
				        $data,
				        $pesan,
				        $this->cssDone
				    ),
				    Messenger::NextRequest
				);
            }else{
                $pesan  = 'Gagal melaksanakan approval adjustment rencana penerimaan';
                Messenger::Instance()->
				Send(
				    'rencana_penerimaan_adjust_history',
				    'HistoryAdjustmentRencanaPenerimaan',
				    'view',
				    'html',
				    array(
				        $data,
				        $pesan,
				        $this->cssFail
				    ),
				    Messenger::NextRequest
				);
            }
        }else{
            $pesan      = 'Pilih data yang akan di approve';
            Messenger::Instance()->
		    Send(
				'rencana_penerimaan_adjust_history',
				'HistoryAdjustmentRencanaPenerimaan',
				'view',
				'html',
				array(
				    $data,
				    $pesan,
				    $this->cssFail
				),
				Messenger::NextRequest
		    );
        }
        return $this->urlReturn;
    }

}
?>
