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

require_once 'model/impuesto.php';

class contabilidad_impuestos extends fs_controller
{
   public $impuesto;
   
   public function __construct()
   {
      parent::__construct('contabilidad_impuestos', 'Impuestos', 'contabilidad', FALSE, TRUE);
   }
   
   protected function process()
   {
      $this->impuesto = new impuesto();
      $this->buttons[] = new fs_button('b_nuevo_impuesto', 'nuevo impuesto');
      
      if( isset($_POST['codimpuesto']) )
      {
         $impuesto = $this->impuesto->get($_POST['codimpuesto']);
         if( !$impuesto )
         {
            $impuesto = new impuesto();
            $impuesto->codimpuesto = $_POST['codimpuesto'];
         }
         $impuesto->descripcion = $_POST['descripcion'];
         $impuesto->iva = $_POST['iva'];
         if( $impuesto->save() )
            $this->new_message("Impuesto guardado correctamente");
         else
            $this->new_error_msg("¡Error al guardar el impuesto!");
      }
   }
}

?>
