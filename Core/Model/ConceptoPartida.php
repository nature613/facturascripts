<?php
/**
 * This file is part of facturacion_base
 * Copyright (C) 2014-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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
namespace FacturaScripts\Core\Model;

/**
 * Un concepto predefinido para una partida (la línea de un asiento contable).
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class ConceptoPartida
{

    use Base\ModelTrait;

    /**
     * Clave primaria.
     * @var string
     */
    public $idconceptopar;

    /**
     * TODO
     * @var string
     */
    public $concepto;

    /**
     * ConceptoPartida constructor.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->init('co_conceptospar', 'idconceptopar');
        if (empty($data)) {
            $this->clear();
        } else {
            $this->loadFromData($data);
        }
    }

    /**
     * TODO
     * @return bool
     */
    public function test()
    {
        $this->concepto = static::noHtml($this->concepto);
        return true;
    }

    /**
     * Almacena los datos del modelo en la base de datos.
     * @return bool
     */
    public function save()
    {
        return false;
    }
}
