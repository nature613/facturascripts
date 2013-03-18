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

require_once 'model/divisa.php';

class admin_divisas extends fs_controller
{
   public $divisa;
   
   public function __construct()
   {
      parent::__construct('admin_divisas', 'Divisas', 'admin', TRUE, TRUE);
   }
   
   protected function process()
   {
      $this->divisa = new divisa();
      $this->buttons[] = new fs_button('b_nueva_divisa', 'nueva');
      
      if( isset($_POST['coddivisa']) )
      {
         $div0 = $this->divisa->get($_POST['coddivisa']);
         if( !$div0 )
         {
            $div0 = new divisa();
            $div0->coddivisa = $_POST['coddivisa'];
         }
         $div0->descripcion = $_POST['descripcion'];
         $div0->codiso = $_POST['codiso'];
         $div0->tasaconv = floatval($_POST['tasaconv']);
         if( $div0->save() )
            $this->new_message('Divisa '.$div0->coddivisa.' guardada correctamente.');
         else
            $this->new_error_msg('Error al guardar la divisa.');
      }
      else
      {
         for($i = 0; isset($_POST['coddivisa_'.$i]); $i++)
         {
            $div0 = $this->divisa->get($_POST['coddivisa_'.$i]);
            if($div0)
            {
               if( isset($_POST['delete_'.$i]) )
               {
                  if( $div0->delete() )
                     $this->new_message('Divisa '.$div0->coddivisa.' eliminada correctamente.');
                  else
                     $this->new_error_msg('Error al eliminar la divisa '.$div0->coddivisa.'.');
               }
               else
               {
                  $div0->descripcion = $_POST['descripcion_'.$i];
                  $div0->codiso = $_POST['codiso_'.$i];
                  $div0->tasaconv = floatval($_POST['tasaconv_'.$i]);
                  if( !$div0->save() )
                     $this->new_error_msg('Error al guardar la divisa.');
               }
            }
            else
               $this->new_error_msg('Divisa '.$_POST['coddivisa_'.$i].' no encontrada.');
         }
      }
   }
   
   public function version()
   {
      return parent::version().'-1';
   }
}

?>
