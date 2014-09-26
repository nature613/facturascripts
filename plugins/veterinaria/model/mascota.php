<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2014  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'base/fs_model.php';
require_model('cliente.php');
require_model('raza.php');

class mascota extends fs_model
{
   public $nombre;
   public $idmascota; /// pkey
   public $chip;
   public $pasaporte;
   public $fecha_nac;
   public $fecha_alta;
   public $sexo;
   public $idraza;
   public $color;
   public $esterilizado;
   public $fecha_esterilizado;
   public $altura;
   public $codcliente;
   
   private static $cliente;
   
   public function __construct($m=FALSE)
   {
      parent::__construct('fbm_mascotas', 'plugins/veterinaria/');
      if($m)
      {
         $this->idmascota = $m['idmascota'];
         $this->nombre = $m['nombre'];
         $this->chip = $m['chip'];
         $this->pasaporte = $m['pasaporte'];
         $this->fecha_nac = $m['fecha_nac'];
         $this->fecha_alta = Date('d-m-Y');
         $this->sexo = $m['sexo'];
         $this->idraza = $m['idraza'];
         $this->color = $m['color'];
         $this->esterilizado = $m['esterilizado'];
         $this->fecha_esterilizado = $m['fecha_esterilizado'];
         $this->altura = $m['altura'];
         $this->codcliente = $m['codcliente'];
      }
      else
      {
         $this->idmascota = NULL;
         $this->nombre = NULL;
         $this->chip = NULL;
         $this->pasaporte = NULL;
         $this->fecha_nac = Date('d-m-Y');
         $this->fecha_alta = Date('d-m-Y');
         $this->sexo = NULL;
         $this->idraza = NULL;
         $this->color = NULL;
         $this->esterilizado = NULL;
         $this->fecha_esterilizado = NULL;
         $this->altura = NULL;
         $this->codcliente = NULL;
      }
   }

   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      return 'index.php?page=veterinaria_mascota&id='.$this->idmascota;
   }
   
   public function nombre_cliente()
   {
      if( !isset(self::$cliente) )
      {
         self::$cliente = new cliente();
      }
      
      $cli0 = self::$cliente->get($this->codcliente);
      if($cli0)
      {
         return $cli0->nombre;
      }
      else
         return '-';
   }
   
   public function especie()
   {
      return '-';
   }
   
   public function raza()
   {
      return '-';
   }
   
   public function get($id)
   {
      $mascotas = $this->db->select("SELECT * FROM ".$this->table_name." WHERE idmascota = ".$this->var2str($id).";");
      if($mascotas)
      {
         return new mascota($mascotas[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->idmascota) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE idmascota = ".$this->var2str($this->idmascota).";");
   }
   
   public function test()
   {
      return TRUE;
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET nombre = ".$this->var2str($this->nombre).",
            chip = ".$this->var2str($this->chip).", pasaporte = ".$this->var2str($this->pasaporte).",
            fecha_nac = ".$this->var2str($this->fecha_nac).", sexo = ".$this->var2str($this->sexo).",
            idraza = ".$this->var2str($this->idraza).", color = ".$this->var2str($this->color).",
            esterilizado = ".$this->var2str($this->esterilizado).",
            fecha_esterilizado = ".$this->var2str($this->fecha_esterilizado).",
            altura = ".$this->var2str($this->altura)."
            WHERE idmascota = ".$this->var2str($this->idmascota).";";
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (nombre,chip,pasaporte,fecha_nac,
            fecha_alta,sexo,idraza,color,esterilizado,fecha_esterilizado,altura,codcliente)
            VALUES (".$this->var2str($this->nombre).",".$this->var2str($this->chip).",
            ".$this->var2str($this->pasaporte).",".$this->var2str($this->fecha_nac).",
            ".$this->var2str($this->fecha_alta).",".$this->var2str($this->sexo).",
            ".$this->var2str($this->idraza).",".$this->var2str($this->color).",
            ".$this->var2str($this->esterilizado).",".$this->var2str($this->fecha_esterilizado).",
            ".$this->var2str($this->altura).",".$this->var2str($this->codcliente).");";
         
         if( $this->db->exec($sql) )
         {
            $this->idmascota = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE idmascota = ".$this->var2str($this->idmascota).";");
   }
   
   public function all()
   {
      $listam = array();
      
      $mascotas = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY nombre ASC;");
      if($mascotas)
      {
         foreach($mascotas as $m)
            $listam[] = new mascota($m);
      }
      
      return $listam;
   }
   
}
