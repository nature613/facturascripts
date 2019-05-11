<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2019 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

/**
 * Controller to list the items in the RegularizacionImpuesto model
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Artex Trading sa     <jcuello@artextrading.com>
 */
class ListRegularizacionImpuesto extends ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['menu'] = 'accounting';
        $pagedata['submenu'] = 'taxes';
        $pagedata['title'] = 'vat-regularization';
        $pagedata['icon'] = 'fas fa-map-signs';

        return $pagedata;
    }

    /**
     * Load views
     */
    protected function createViews()
    {
        $this->addView('ListRegularizacionImpuesto', 'RegularizacionImpuesto', 'vat-regularization', 'fas fa-map-signs');
        $this->addSearchFields('ListRegularizacionImpuesto', ['periodo', 'fechainicio']);
        $this->addOrderBy('ListRegularizacionImpuesto', ['codejercicio||periodo'], 'period');
        $this->addOrderBy('ListRegularizacionImpuesto', ['fechainicio'], 'start-date');
    }
}
