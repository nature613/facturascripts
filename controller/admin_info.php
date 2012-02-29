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

class new_fs_controller extends fs_controller
{
   public function __construct()
   {
      parent::__construct('admin_info', 'Información del sistema', 'admin', TRUE, TRUE);
   }
   
   public function uname()
   {
      return php_uname();
   }
   
   public function php_version()
   {
      return phpversion();
   }
   
   public function pg_version()
   {
      $v = $this->db->version();
      return $v['server'];
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
}

?>
