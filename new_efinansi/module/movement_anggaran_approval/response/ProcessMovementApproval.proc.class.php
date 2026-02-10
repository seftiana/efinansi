<?php

require_once GTFWConfiguration::GetValue('application','docroot').
    'module/movement_anggaran_approval/business/MovementAnggaranApproval.class.php';
    
#doc
#    classname:    Movement
#    scope:        PUBLIC
# extends
# construct: 
#/doc
    
class ProcessMovementAnggaranApproval 
{
    #    internal variables
    public $obj;
    public $pageReturn;
    public $pageView;
    public $data;
        
    public $cssDone = 'notebox-done';
    public $cssFail = 'notebox-warning';
    #    Constructor
    
    public function __construct ()
    {
    
        $this->obj          = new MovementAnggaranApproval;
        $this->data         = $_POST->AsArray();
        $this->pageReturn   = Dispatcher::Instance()->GetUrl(
                'movement_anggaran_approval',
                'movementAnggaranApproval',
                'view',
                'html'
            );
            
        $this->pageView     = Dispatcher::Instance()->GetUrl(
                'movement_anggaran_approval',
                'UpdateMovementAnggaranApproval',
                'view',
                'html'
            ).'&id='.$this->data['movement_id'];
    }
       
    public function Approval()
    {
        if($this->data['btnsimpan']){
            
            if(!empty($this->data['status_approval']) || ($this->data['status_approval'] !=='')) {
                $processApproval =  $this->obj->Approve(
                    $this->data['status_approval'], 
                    $this->data['movement_id']
                );
            
                if($processApproval === true){
                    Messenger::Instance()->Send('movement_anggaran_approval', 'MovementAnggaranApproval', 'view', 'html', 
                    array($this->data,'Proses Approval berhasil dilakukan.',$this->cssDone),Messenger::NextRequest);
                    return $this->pageReturn;
                } else {
                    Messenger::Instance()->Send('movement_anggaran_approval', 'UpdateMovementAnggaranApproval', 'view', 'html', 
                    array($this->data,'Proses Approval gagal dilakukan.',$this->cssFail),Messenger::NextRequest);
                    return $this->pageView;
                }
                
                return $this->pageView;        
            } else {
                Messenger::Instance()->Send('movement_anggaran_approval', 'UpdateMovementAnggaranApproval', 'view', 'html', 
                    array($this->data,'Status Approval belum dipilih.',$this->cssFail),Messenger::NextRequest);
                return $this->pageView;
            }
        }            
        return $this->pageReturn;       
    }

}

?>