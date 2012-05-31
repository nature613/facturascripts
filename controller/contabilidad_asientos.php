<?php

require_once 'model/asiento.php';

class contabilidad_asientos extends fs_controller
{
   public $asiento;
   public $resultados;
   public $offset;
   
   public function __construct()
   {
      parent::__construct('contabilidad_asientos', 'Asientos', 'contabilidad', FALSE, TRUE);
   }
   
   protected function process()
   {
      $this->asiento = new asiento();
      $this->custom_search = TRUE;
      
      if( isset($_GET['offset']) )
         $this->offset = intval($_GET['offset']);
      else
         $this->offset = 0;
      
      if($this->query != '')
         $this->resultados = $this->asiento->search($this->query, $this->offset);
      else
         $this->resultados = $this->asiento->all($this->offset);
   }
   
   public function anterior_url()
   {
      $url = '';
      if($this->query!='' AND $this->offset>'0')
         $url = $this->url()."&query=".$this->query."&offset=".($this->offset-FS_ITEM_LIMIT);
      else if($this->query=='' AND $this->offset>'0')
         $url = $this->url()."&offset=".($this->offset-FS_ITEM_LIMIT);
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      if($this->query!='' AND count($this->resultados)==FS_ITEM_LIMIT)
         $url = $this->url()."&query=".$this->query."&offset=".($this->offset+FS_ITEM_LIMIT);
      else if($this->query=='' AND count($this->resultados)==FS_ITEM_LIMIT)
         $url = $this->url()."&offset=".($this->offset+FS_ITEM_LIMIT);
      return $url;
   }
}

?>
