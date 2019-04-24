<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
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
 * Controller to list the items in the SecuenciaDocumento model
 *
 * @author Carlos García Gómez          <carlos@facturascripts.com>
 * @author Cristo M. Estévez Hernández  <cristom.estevez@gmail.com>
 */
class ListSecuenciaDocumento extends ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['menu'] = 'admin';
        $pagedata['title'] = 'document-sequences';
        $pagedata['icon'] = 'fas fa-code';

        return $pagedata;
    }

    /**
     * Load views
     */
    protected function createViews()
    {
        $this->addView('ListSecuenciaDocumento', 'SecuenciaDocumento', 'document-sequences', 'fas fa-code');
        $this->addSearchFields('ListSecuenciaDocumento', ['titulo', 'tipodoc']);
        $this->addOrderBy('ListSecuenciaDocumento', ['codejercicio'], 'exercise');
        $this->addOrderBy('ListSecuenciaDocumento', ['codserie'], 'serie');
        $this->addOrderBy('ListSecuenciaDocumento', ['numero'], 'number');
    }
}
