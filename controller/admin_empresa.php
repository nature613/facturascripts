<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2012  Carlos Garcia Gomez  neorazorx@gmail.com
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

require_once 'model/almacen.php';
require_once 'model/divisa.php';
require_once 'model/ejercicio.php';
require_once 'model/forma_pago.php';
require_once 'model/serie.php';
require_once 'model/pais.php';

class admin_empresa extends fs_controller
{
   public $almacen;
   public $divisa;
   public $ejercicio;
   public $forma_pago;
   public $serie;
   public $pais;
   
   public function __construct()
   {
      parent::__construct('admin_empresa', 'Empresa', 'admin', TRUE, TRUE);
   }
   
   protected function process()
   {
      $this->almacen = new almacen();
      $this->divisa = new divisa();
      $this->ejercicio = new ejercicio();
      $this->forma_pago = new forma_pago();
      $this->serie = new serie();
      $this->pais = new pais();
      
      if( isset($_POST['nombre']) )
      {
         $this->set_default_elements();
         
         $this->empresa->nombre = $_POST['nombre'];
         $this->empresa->cifnif = $_POST['cifnif'];
         $this->empresa->administrador = $_POST['administrador'];
         $this->empresa->direccion = $_POST['direccion'];
         $this->empresa->ciudad = $_POST['ciudad'];
         $this->empresa->codpostal = $_POST['codpostal'];
         $this->empresa->codpais = $_POST['codpais'];
         $this->empresa->telefono = $_POST['telefono'];
         $this->empresa->fax = $_POST['fax'];
         $this->empresa->web = $_POST['web'];
         $this->empresa->email = $_POST['email'];
         $this->empresa->lema = $_POST['lema'];
         $this->empresa->horario = $_POST['horario'];
         $this->empresa->contintegrada = isset($_POST['contintegrada']);
         $this->empresa->codejercicio = $_POST['codejercicio'];
         $this->empresa->codserie = $_POST['codserie'];
         $this->empresa->coddivisa = $_POST['coddivisa'];
         $this->empresa->codpago = $_POST['codpago'];
         $this->empresa->codalmacen = $_POST['codalmacen'];
         
         if( $this->empresa->save() )
         {
            $this->new_message('Datos guardados correctamente.');
            setcookie('empresa', $this->empresa->nombre, time()+FS_COOKIES_EXPIRE);
         }
         else
            $this->new_error_msg ('Error al guardar los datos.');
      }
   }
   
   public function version() {
      return parent::version().'-4';
   }
   
   private function set_default_elements()
   {
      if( isset($_POST['codalmacen']) )
      {
         $almacen = $this->almacen->get($_POST['codalmacen']);
         if( $almacen )
            $almacen->set_default();
      }
      
      if( isset($_POST['coddivisa']) )
      {
         $divisa = $this->divisa->get($_POST['coddivisa']);
         if( $divisa )
            $divisa->set_default();
      }
      
      if( isset($_POST['codejercicio']) )
      {
         $ejercicio = $this->ejercicio->get($_POST['codejercicio']);
         if( $ejercicio )
            $ejercicio->set_default();
      }
      
      if( isset($_POST['codpago']) )
      {
         $fpago = $this->forma_pago->get($_POST['codpago']);
         if( $fpago )
            $fpago->set_default();
      }
      
      if( isset($_POST['codserie']) )
      {
         $serie = $this->serie->get($_POST['codserie']);
         if( $serie )
            $serie->set_default();
      }
      
      if( isset($_POST['codpais']) )
      {
         $pais = $this->pais->get($_POST['codpais']);
         if( $pais )
            $pais->set_default();
      }
   }
}

?>
