<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\ExtendedController;
use FacturaScripts\Core\Base;

/**
 * Description of EditAgente
 *
 * @author Raul
 * Clase edit Agente basada en la funcionalidad de Editcliente
 */
class EditAgente extends ExtendedController\EditController
{
    /**
     * EditAgente constructor.
     *
     * @param Base\Cache $cache
     * @param Base\Translator $i18n
     * @param Base\MiniLog $miniLog
     * @param string $className
     */
    public function __construct(&$cache, &$i18n, &$miniLog, $className)
    {
        parent::__construct($cache, $i18n, $miniLog, $className);
        $this->modelName = 'FacturaScripts\Core\Model\Agente';
    }

    /**
     * Devuelve el texto para el pie del panel de datos
     *
     * @return string
     */
    public function getPanelFooter()
    {
        $model = $this->getModel();
        return $this->i18n->trans('discharge-date') . ': ' . $model->f_alta;
    }

    /**
     * Devuelve los datos básicos de la página
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['title'] = 'agent';
        $pagedata['icon'] = 'fa-id-badge';
        $pagedata['showonmenu'] = false;

        return $pagedata;
    }
}
