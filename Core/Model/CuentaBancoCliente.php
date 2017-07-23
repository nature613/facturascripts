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

use FacturaScripts\Core\Base\Model;

/**
 * Una cuenta bancaria de un cliente.
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class CuentaBancoCliente
{
    use Model {
        save as private saveTrait;
    }

    /**
     * Clave primaria. Varchar(6).
     * @var
     */
    public $codcuenta;

    /**
     * Código del cliente.
     * @var
     */
    public $codcliente;
    /**
     * TODO
     * @var
     */
    public $descripcion;
    /**
     * TODO
     * @var
     */
    public $iban;
    /**
     * TODO
     * @var
     */
    public $swift;

    /**
     * ¿Es la cuenta principal del cliente?
     * @var
     */
    public $principal;

    /**
     * Fecha en la que se firmó el mandato para autorizar la domiciliación de recibos.
     * @var
     */
    public $fmandato;

    /**
     * CuentaBancoCliente constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->init(__CLASS__, 'cuentasbcocli', 'codcliente');
        $this->clear();
        if (!empty($data)) {
            $this->loadFromData($data);
        }
    }

    /**
     * Resetea los valores de todas las propiedades modelo.
     */
    public function clear()
    {
        $this->codcliente = null;
        $this->codcuenta = null;
        $this->descripcion = null;
        $this->iban = null;
        $this->swift = null;
        $this->principal = true;
        $this->fmandato = null;
    }

    /**
     * Devuelve el IBAN con o sin espacios.
     *
     * @param $espacios
     *
     * @return string
     */
    public function getIban($espacios = false)
    {
        if ($espacios) {
            $txt = '';
            $iban = str_replace(' ', '', $this->iban);
            for ($i = 0; $i < $len = strlen($iban); $i += 4) {
                $txt .= substr($iban, $i, 4) . ' ';
            }
            return $txt;
        }
        return str_replace(' ', '', $this->iban);
    }

    /**
     * Devuelve la url donde ver/modificar estos datos
     * @return string
     */
    public function url()
    {
        if ($this->codcliente === null) {
            return '#';
        }
        return 'index.php?page=VentasCliente&cod=' . $this->codcliente . '#cuentasb';
    }

    /**
     * Almacena los datos del modelo en la base de datos.
     * @return bool
     */
    public function save()
    {
        $this->descripcion = static::noHtml($this->descripcion);

        if ($this->exists()) {
            $sql = $this->saveUpdateSQL();
        } else {
            $sql = $this->saveInsertSQL();
        }

        if ($this->principal) {
            /// si esta cuenta es la principal, desmarcamos las demás
            $sql .= 'UPDATE ' . $this->tableName() . ' SET principal = false' .
                ' WHERE codcliente = ' . $this->var2str($this->codcliente) .
                ' AND codcuenta != ' . $this->var2str($this->codcuenta) . ';';
        }

        return $this->database->exec($sql);
    }

    /**
     * TODO
     *
     * @param string $codcli
     *
     * @return array
     */
    public function allFromCliente($codcli)
    {
        $clist = [];
        $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE codcliente = ' . $this->var2str($codcli)
            . ' ORDER BY codcuenta DESC;';

        $data = $this->database->select($sql);
        if ($data) {
            foreach ($data as $d) {
                $clist[] = new CuentaBancoCliente($d);
            }
        }

        return $clist;
    }

    /**
     * Actualiza los datos del modelo en la base de datos.
     * @return string
     */
    private function saveUpdateSQL()
    {
        $sql = 'UPDATE ' . $this->tableName() . ' SET descripcion = ' . $this->var2str($this->descripcion) .
            ', codcliente = ' . $this->var2str($this->codcliente) .
            ', iban = ' . $this->var2str($this->iban) .
            ', swift = ' . $this->var2str($this->swift) .
            ', principal = ' . $this->var2str($this->principal) .
            ', fmandato = ' . $this->var2str($this->fmandato) .
            '  WHERE codcuenta = ' . $this->var2str($this->codcuenta) . ';';
        return $sql;
    }

    /**
     * Inserta los datos del modelo en la base de datos.
     * @return string
     */
    private function saveInsertSQL()
    {
        $this->codcuenta = $this->getNewCodigo();
        $sql = 'INSERT INTO ' . $this->tableName()
            . ' (codcliente,codcuenta,descripcion,iban,swift,principal,fmandato)' .
            ' VALUES (' . $this->var2str($this->codcliente) .
            ',' . $this->var2str($this->codcuenta) .
            ',' . $this->var2str($this->descripcion) .
            ',' . $this->var2str($this->iban) .
            ',' . $this->var2str($this->swift) .
            ',' . $this->var2str($this->principal) .
            ',' . $this->var2str($this->fmandato) . ');';
        return $sql;
    }

    /**
     * TODO
     * @return int
     */
    private function getNewCodigo()
    {
        $sql = 'SELECT MAX(' . $this->database->sql2Int('codcuenta') . ') as cod FROM ' . $this->tableName() . ';';
        $cod = $this->database->select($sql);
        if ($cod) {
            return 1 + (int)$cod[0]['cod'];
        }
        return 1;
    }
}
