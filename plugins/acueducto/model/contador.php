<?php

class contador extends fs_model
{
   public $idcontador;
   public $codcliente;
   public $numero;
   public $ubicacion;
   public $idsector;
   public $alta;
   public $lectura;
   public $exonerado;
   public $usuario;
 
   public function __construct($g = FALSE)
   {
      parent::__construct('contadores', 'plugins/acueducto/');
      
      if($g)
      {
         $this->idcontador = $g['idcontador'];
         $this->codcliente = $g['codcliente'];
         $this->numero = $g['numero'];
         $this->ubicacion = $g['ubicacion'];
         $this->idsector = $g['idsector'];
         $this->alta = date('d-m-Y', strtotime($g['alta']));
         $this->lectura = date('d-m-Y', strtotime($g['lectura']));
         $this->exonerado = $this->str2bool($g['exonerado']);
         $this->usuario = $g['usuario'];
      }
      else
      {
         $this->idlectura = NULL;
         $this->codcliente = "";
         $this->numero = "";
         $this->ubicacion = "";
         $this->fecha = date('d-m-Y');
         $this->imputacion = date('d-m-Y');
         $this->exonerado = 0;
         $this->usuario = $this->user->nick;
         
      }
   }
   
   protected function install() {
      ;
   }
   
   public function get($id)
   {
      $data = $this->db->select("select * from contadores where idcontador = ".$this->var2str($id).";");
      if($data)
      {
         return new contador($data[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->idcontador) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("select * from contadores where idcontador = ".$this->var2str($this->idcontador).";");
      }
   }
   
   public function test() {
      ;
   }
   
   public function nuevo_numero()
   {
      $data = $this->db->select("select max(idcontador) as num from contadores;");
      if($data)
         return intval($data[0]['num']) + 1;
      else
         return 1;
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE contadores set codcliente = ".$this->var2str($this->codcliente).
                 ", numero = ".$this->var2str($this->numero).
                 ", ubicacion = ".$this->var2str($this->ubicacion).
                 ", idsector = ".$this->var2str($this->idsector).
                 ", alta = ".$this->var2str($this->alta).
                 ", lectura = ".$this->var2str($this->lectura).
                 ", exonerado = ".$this->var2str($this->exonerado).
                 ", usuario = ".$this->var2str($this->usuario).
                 " where idcontador = ".$this->var2str($this->idcontador).";";
                 
      }
      else
      {
         $sql = "INSERT into contadores (idcontador,codcliente,numero,ubicacion,idsector,alta,lectura,exonerado,usuario) VALUES ("
                 .$this->var2str($this->idcontador).","
                 .$this->var2str($this->codcliente).","
                 .$this->var2str($this->numero).","
                 .$this->var2str($this->ubicacion).","
                 .$this->var2str($this->idsector).","
                 .$this->var2str($this->fecha).","
                 .$this->var2str($this->lectura).","
                 .$this->var2str($this->exonerado).","
                 .$this->var2str($this->usuario).");";
      }
      
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("delete from contadores where idcontador = ".$this->var2str($this->idcontador).";");
   }
   
   public function listar()
   {
      $listag = array();
      
      $data = $this->db->select("select * from contadores;");
      if($data)
      {
         foreach($data as $d)
         {
            $listag[] = new contador($d);
         }
      }
      
      return $listag;
   }
   
   public function buscar($texto = '')
   {
      $listag = array();
      
      $data = $this->db->select("select * from contadores where codcliente like '%".$texto."%';");
      if($data)
      {
         foreach($data as $d)
         {
            $listag[] = new contador($d);
         }
      }
      
      return $listag;
   }

   public function all()
   {
      $todos = array();

      $data = $this->db->select("SELECT * FROM contadores");
      if($data)
      {
         foreach($data as $d)
             $todos[] = new contador($d);
      }

      return $todos;
   }
   
   public function all_cli($cliente)
   {
      $todos = array();

      $data = $this->db->select("SELECT * FROM contadores where codcliente=" . $cliente);
      if($data)
      {
         foreach($data as $d)
             $todos[] = new contador($d);
      }

      return $todos;
   }
 }