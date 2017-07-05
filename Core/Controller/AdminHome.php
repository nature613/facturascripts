<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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

use FacturaScripts\Core\Base;
use FacturaScripts\Core\Model;
use RuntimeException;
use Symfony\Component\Translation\Exception\InvalidArgumentException as TranslationInvalidArgumentException;

/**
 * Description of admin_home
 *
 * @author Carlos García Gómez
 */
class AdminHome extends Base\Controller
{
    /**
     * TODO
     * @var Model\Agente
     */
    public $agente;
    /**
     * TODO
     * @var Model\Almacen
     */
    public $almacen;
    /**
     * TODO
     * @var Model\Divisa
     */
    public $divisa;
    /**
     * TODO
     * @var Model\Ejercicio
     */
    public $ejercicio;
    /**
     * TODO
     * @var Model\Empresa
     */
    public $empresa;
    /**
     * TODO
     * @var Model\FormaPago
     */
    public $formaPago;
    /**
     * TODO
     * @var Model\Pais
     */
    public $pais;
    /**
     * TODO
     * @var Model\Serie
     */
    public $serie;

    /**
     * AdminHome constructor.
     * @param Base\Cache $cache
     * @param Base\Translator $i18n
     * @param Base\MiniLog $miniLog
     * @param string $className
     * @throws RuntimeException
     * @throws TranslationInvalidArgumentException
     */
    public function __construct(&$cache, &$i18n, &$miniLog, $className)
    {
        parent::__construct($cache, $i18n, $miniLog, $className);

        /// por ahora desplegamos siempre el contenido de Dinamic, para las pruebas
        $pluginManager = new Base\PluginManager();
        $pluginManager->deploy();

        $this->agente = new Model\Agente();
        $this->almacen = new Model\Almacen();
        $this->divisa = new Model\Divisa();
        $this->ejercicio = new Model\Ejercicio();
        $this->empresa = new Model\Empresa();
        $this->formaPago = new Model\FormaPago();
        $this->pais = new Model\Pais();
        $this->serie = new Model\Serie();
    }

    /**
     * TODO
     */
    public function run()
    {
        parent::run();

        foreach ($this->agente->all() as $age) {
            $age->save();
        }
    }
}
