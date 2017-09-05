<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  carlos@facturascripts.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ExtendedController;

/**
 * Description of ListProveedor
 *
 * @author carlos
 */
class ListProveedor extends ExtendedController\ListController
{

    public function __construct(&$cache, &$i18n, &$miniLog, $className)
    {
        parent::__construct($cache, $i18n, $miniLog, $className);
    }

    public function privateCore(&$response, $user)
    {
        parent::privateCore($response, $user);
    }

    protected function getWhere()
    {
        $result = parent::getWhere();

        if ($this->query != '') {
            $fields = "nombre|razonsocial|codproveedor";
            $result[] = new DataBaseWhere($fields, $this->query, "LIKE");
        }
        return $result;
    }

    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['title'] = 'Proveedores';
        $pagedata['icon'] = 'fa-users';
        $pagedata['menu'] = 'compras';

        return $pagedata;
    }

    protected function createViews()
    {
        $className = $this->getClassName();
        $index = $this->addView('FacturaScripts\Core\Model\Proveedor', $className);
        
        $this->addOrderBy($index, 'codproveedor', 'code');
        $this->addOrderBy($index, 'nombre', 'name', 1);
        $this->addOrderBy($index, 'fecha', 'date');

        $this->addFilterCheckbox($index, 'debaja', 'De baja');
    }
}
