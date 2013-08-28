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

require_once 'base/fs_printer.php';

class admin_info extends fs_controller
{
   public function __construct()
   {
      parent::__construct('admin_info', 'Información del sistema', 'admin', TRUE, TRUE);
   }
   
   protected function process()
   {
      $this->buttons[] = new fs_button_img('b_clean_cache', 'limpiar la cache', 'trash.png',
              $this->url()."&clean_cache=TRUE", TRUE);
      
      if( isset($_GET['clean_cache']) )
      {
         if( $this->cache->clean() )
            $this->new_message("Cache limpiada correctamente.");
         else
            $this->new_error_msg("¡Imposible limpiar la cache!");
      }
      
      if(FS_LCD != '')
      {
         $fpt = new fs_printer(FS_LCD);
         $fpt->add( $fpt->center_text('The cake is a lie!', 20) );
         $fpt->add( $fpt->center_text('The cake is a lie!', 20) );
         $fpt->imprimir();
      }
   }
   
   public function linux()
   {
      return (php_uname('s') == 'Linux');
   }
   
   public function uname()
   {
      return php_uname();
   }
   
   public function php_version()
   {
      return phpversion();
   }
   
   public function cache_version()
   {
      return $this->cache->version();
   }
   
   public function sys_uptime()
   {
      return system('uptime');
   }
   
   public function sys_df()
   {
      return system('df -h');
   }
   
   public function sys_free()
   {
      return system('free -m');
   }
   
   public function version()
   {
      return parent::version().'-4';
   }
   
   public function fs_version()
   {
      return parent::version();
   }
   
   public function fs_db_name()
   {
      return FS_DB_NAME;
   }
   
   public function fs_db_version()
   {
      return $this->db->version();
   }
   
   public function get_locks()
   {
      return $this->db->get_locks();
   }
}

?>