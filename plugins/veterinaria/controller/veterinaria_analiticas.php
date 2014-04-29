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

require_model('analitica.php');

class veterinaria_analiticas extends fs_controller
{
   public $analitica;

   public function __construct()
   {
      parent::__construct('veterinaria_analiticas', 'Tipos Analiticas', 'Veterinaria', TRUE, TRUE);
   }
   
   protected function process()
   {
      $this->analitica = new analitica();
      
      if( isset($_POST['snombre']) ) /// crear o modificar
      {
         $analitica = new analitica();
         $analitica->nombre = $_POST['snombre'];
         if( $analitica->save() )
            $this->new_message("Tipo de analitica guardada correctamente.");
         else
            $this->new_error_msg("¡Imposible guardar el tipo de analitica!");
      }
      else if( isset($_GET['delete']) ) /// eliminar
      {
         $analitica = $this->analitica->get($_GET['delete']);
         if($analitica)
         {
            if( $analitica->delete() )
               $this->new_message("Tipo de analitica eliminada correctamente.");
            else
               $this->new_error_msg("¡Imposible eliminar el tipo de analitica!");
         }
         else
            $this->new_error_msg("Tipo de analitica no encontrada!");
      }
      
      
      
      
      
   }
}

?>