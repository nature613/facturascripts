<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2013  Carlos Garcia Gomez  neorazorx@gmail.com
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
require_once 'model/subcuenta.php';

class cuenta_especial extends fs_model
{
   public $idcuentaesp; /// pkey
   public $descripcion;
   public $codcuenta;
   public $codsubcuenta;
   
   public function __construct($c = FALSE)
   {
      parent::__construct('co_cuentasesp');
      if($c)
      {
         $this->idcuentaesp = $c['idcuentaesp'];
         $this->descripcion = $c['descripcion'];
         $this->codcuenta = $c['codcuenta'];
         $this->codsubcuenta = $c['codsubcuenta'];
      }
      else
      {
         $this->idcuentaesp = NULL;
         $this->descripcion = NULL;
         $this->codcuenta = NULL;
         $this->codsubcuenta = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get($id)
   {
      $cuentae = $this->db->select("SELECT * FROM ".$this->table_name.
         " WHERE idcuentaesp = ".$this->var2str($id).";");
      if($cuentae)
         return new cuenta_especial($cuentae[0]);
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->idcuentaesp) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name.
            " WHERE idcuentaesp = ".$this->var2str($this->idcuentaesp).";");
   }
   
   public function test()
   {
      return TRUE;
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET descripcion = ".$this->var2str($this->descripcion).",
            codcuenta = ".$this->var2str($this->codcuenta).",
            codsubcuenta = ".$this->var2str($this->codsubcuenta)."
            WHERE idcuentaesp = ".$this->var2str($this->idcuentaesp).";";
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (idcuentaesp,descripcion,codcuenta,codsubcuenta)
            VALUES (".$this->var2str($this->idcuentaesp).",".$this->var2str($this->descripcion).",
            ".$this->var2str($this->codcuenta).",".$this->var2str($this->codsubcuenta).");";
      }
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name.
            " WHERE idcuentaesp = ".$this->var2str($this->idcuentaesp).";");
   }
   
   public function all()
   {
      $culist = array();
      $cuentas = $this->db->select("SELECT * FROM ".$this->table_name." ORDER BY idcuentaesp ASC;");
      if($cuentas)
      {
         foreach($cuentas as $c)
            $culist[] = new cuenta_especial($c);
      }
      return $culist;
   }
}


class cuenta extends fs_model
{
   public $idcuenta;
   public $codcuenta;
   public $codejercicio;
   public $idepigrafe;
   public $codepigrafe;
   public $descripcion;
   public $idcuentaesp;
   
   public function __construct($c=FALSE)
   {
      parent::__construct('co_cuentas');
      if($c)
      {
         $this->idcuenta = $this->intval( $c['idcuenta'] );
         $this->codcuenta = $c['codcuenta'];
         $this->codejercicio = $c['codejercicio'];
         $this->idepigrafe = $this->intval($c['idepigrafe']);
         $this->codepigrafe = $c['codepigrafe'];
         
         if($c['descripcion'] == '')
            $this->descripcion = '---';
         else
            $this->descripcion = $c['descripcion'];
         
         $this->idcuentaesp = $c['idcuentaesp'];
      }
      else
      {
         $this->idcuenta = NULL;
         $this->codcuenta = NULL;
         $this->codejercicio = NULL;
         $this->idepigrafe = NULL;
         $this->codepigrafe = NULL;
         $this->descripcion = '';
         $this->idcuentaesp = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      if( is_null($this->idcuenta) )
         return 'index.php?page=contabilidad_cuentas';
      else
         return 'index.php?page=contabilidad_cuenta&id='.$this->idcuenta;
   }
   
   public function get_subcuentas()
   {
      $subcuenta = new subcuenta();
      return $subcuenta->all_from_cuenta($this->idcuenta);
   }
   
   public function get_ejercicio()
   {
      $eje = new ejercicio();
      return $eje->get($this->codejercicio);
   }
   
   public function get($id)
   {
      $cuenta = $this->db->select("SELECT * FROM ".$this->table_name.
              " WHERE idcuenta = ".$this->var2str($id).";");
      if($cuenta)
         return new cuenta($cuenta[0]);
      else
         return FALSE;
   }
   
   public function get_by_codigo($cod, $ejercicio)
   {
      $cuenta = $this->db->select("SELECT * FROM ".$this->table_name.
         " WHERE codcuenta = ".$this->var2str($cod)." AND codejercicio = ".$this->var2str($ejercicio).";");
      if($cuenta)
         return new cuenta($cuenta[0]);
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->idcuenta) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name.
                 " WHERE idcuenta = ".$this->var2str($this->idcuenta).";");
   }
   
   public function test()
   {
      $this->descripcion = $this->no_html($this->descripcion);
      
      if( !preg_match("/^[A-Z0-9]{1,6}$/i", $this->codcuenta) )
      {
         $this->new_error_msg("Código de cuenta no válido.");
         return FALSE;
      }
      else
         return TRUE;
   }
   
   public function save()
   {
      if( $this->test() )
      {
         if( $this->exists() )
         {
            $sql = "UPDATE ".$this->table_name." SET codcuenta = ".$this->var2str($this->codcuenta).",
               codejercicio = ".$this->var2str($this->codejercicio).",
               idepigrafe = ".$this->var2str($this->idepigrafe).", codepigrafe = ".$this->var2str($this->codepigrafe).",
               descripcion = ".$this->var2str($this->descripcion).", idcuentaesp = ".$this->var2str($this->idcuentaesp)."
               WHERE idcuenta = ".$this->var2str($this->idcuenta).";";
            return $this->db->exec($sql);
         }
         else
         {
            $newid = $this->db->nextval($this->table_name.'_idcuenta_seq');
            if($newid)
            {
               $this->idcuenta = intval($newid);
               $sql = "INSERT INTO ".$this->table_name." (idcuenta,codcuenta,codejercicio,idepigrafe,codepigrafe,
                  descripcion,idcuentaesp) VALUES (".$this->var2str($this->idcuenta).",
                  ".$this->var2str($this->codcuenta).",".$this->var2str($this->codejercicio).",
                  ".$this->var2str($this->idepigrafe).",".$this->var2str($this->codepigrafe).",
                  ".$this->var2str($this->descripcion).",".$this->var2str($this->idcuentaesp).");";
               return $this->db->exec($sql);
            }
            else
               return FALSE;
         }
      }
      else
         return FALSE;
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name.
              " WHERE idcuenta = ".$this->var2str($this->idcuenta).";");
   }
   
   public function all($offset=0)
   {
      $cuenlist = array();
      $cuentas = $this->db->select_limit("SELECT * FROM ".$this->table_name.
              " ORDER BY codejercicio DESC, codcuenta ASC", FS_ITEM_LIMIT, $offset);
      if($cuentas)
      {
         foreach($cuentas as $c)
            $cuenlist[] = new cuenta($c);
      }
      return $cuenlist;
   }
   
   public function full_from_epigrafe($id)
   {
      $cuenlist = array();
      $cuentas = $this->db->select("SELECT * FROM ".$this->table_name."
         WHERE idepigrafe = ".$this->var2str($id)." ORDER BY codcuenta ASC;");
      if($cuentas)
      {
         foreach($cuentas as $c)
            $cuenlist[] = new cuenta($c);
      }
      return $cuenlist;
   }
   
   public function all_from_ejercicio($codejercicio, $offset=0)
   {
      $cuenlist = array();
      $cuentas = $this->db->select_limit("SELECT * FROM ".$this->table_name."
         WHERE codejercicio = ".$this->var2str($codejercicio)." ORDER BY codcuenta ASC", FS_ITEM_LIMIT, $offset);
      if($cuentas)
      {
         foreach($cuentas as $c)
            $cuenlist[] = new cuenta($c);
      }
      return $cuenlist;
   }
   
   public function full_from_ejercicio($codejercicio)
   {
      $cuenlist = array();
      $cuentas = $this->db->select("SELECT * FROM ".$this->table_name."
         WHERE codejercicio = ".$this->var2str($codejercicio)." ORDER BY codcuenta ASC;");
      if($cuentas)
      {
         foreach($cuentas as $c)
            $cuenlist[] = new cuenta($c);
      }
      return $cuenlist;
   }
   
   public function search($query, $offset=0)
   {
      $cuenlist = array();
      $query = strtolower( $this->no_html($query) );
      $cuentas = $this->db->select_limit("SELECT * FROM ".$this->table_name." WHERE codcuenta LIKE '".$query."%'
         OR lower(descripcion) LIKE '%".$query."%' ORDER BY codejercicio DESC, codcuenta ASC", FS_ITEM_LIMIT, $offset);
      if($cuentas)
      {
         foreach($cuentas as $c)
            $cuenlist[] = new cuenta($c);
      }
      return $cuenlist;
   }
}

?>