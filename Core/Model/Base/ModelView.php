<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
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
namespace FacturaScripts\Core\Model\Base;

use FacturaScripts\Core\Base;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * The class from which all model views inherit.
 * It allows the visualization of data of several models.
 * This type of model is only for reading data,
 * it does not allow modification or deletion.
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
abstract class ModelView
{
    /**
     * It provides direct access to the database.
     *
     * @var Base\DataBase
     */
    private static $dataBase;

    /**
     * List of tables required for the execution of the view.
     */
    abstract protected function getTables(): array;

    /**
     * List of fields or columns to select clausule
     */
    abstract protected function getFields(): array;

    /**
     * List of tables related to from clausule
     */
    abstract protected function getSQLFrom(): string;

    /**
     * Return default order by
     */
    abstract protected function getDefaultOrderBy(): string;

    /**
     * Reset the values of all model view properties.
     */
    abstract protected function clear();

    /**
     * Assign the values of the $data array to the model view properties.
     *
     * @param array $data
     */
    abstract protected function loadFromData($data);

    /**
     * Constructor and class initializer.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        if (self::$dataBase === null) {
            self::$dataBase = new Base\DataBase();
        }

        if (empty($data)) {
            $this->clear();
        } else {
            $this->loadFromData($data);
        }
    }

    /**
     * Convert an array of filters order by in string.
     *
     * @param array $order
     *
     * @return string
     */
    private function getOrderBy(array $order): string
    {
        if (empty($order)) {
            return $this->getDefaultOrderBy();
        }

        $result = '';
        $coma = ' ORDER BY ';
        foreach ($order as $key => $value) {
            $result .= $coma . $key . ' ' . $value;
            if ($coma === ' ORDER BY ') {
                $coma = ', ';
            }
        }
        return $result;
    }

    /**
     * Check list of tables required.
     *
     * @return bool
     */
    private function checkTables(): bool
    {
        $result = true;
        foreach ($this->getTables() as $tableName) {
            if (!self::$dataBase->tableExists($tableName)) {
                $result = false;
                break;
            }
        }
        return $result;
    }

    /**
     * Returns the number of records that meet the condition.
     *
     * @param DataBase\DataBaseWhere[] $where filters to apply to records.
     *
     * @return int
     */
    public function count(array $where = [])
    {
        $sql = 'SELECT COUNT(1) AS total FROM ' . $this->tableName() . DataBaseWhere::getSQLWhere($where);
        $data = self::$dataBase->select($sql);
        return empty($data) ? 0 : $data[0]['total'];
    }

    /**
     * Convert the list of fields into a string to use as a select clause
     *
     * @return string
     */
    private function fieldsList(): string
    {
        $result = '';
        $comma = '';
        foreach ($this->getFields() as $key => $value) {
            $result += $comma . $value . ' ' . $key;
            $comma = ',';
        }
        return $result;
    }

    /**
     * Load data for the indicated where.
     *
     * @param DataBase\DataBaseWhere[] $where  filters to apply to model records.
     * @param array                    $order  fields to use in the sorting. For example ['code' => 'ASC']
     * @param int   $offset
     * @param int   $limit
     *
     * @return self[]
     */
    public function all(array $where, array $order = [], int $offset = 0, int $limit = 0)
    {
        if (self::$dataBase === null) {
            self::$dataBase = new Base\DataBase();
        }

        $result = [];
        if ($this->checkTables()) {
            $sqlWhere = DataBaseWhere::getSQLWhere($where);
            $sqlOrderBy = $this->getOrderBy($order);
            $sql = 'SELECT ' . $this->fieldsList() . ' FROM ' . $this->getSQLFrom() . $sqlWhere . ' ' . $sqlOrderBy;
            foreach (self::$dataBase->selectLimit($sql, $limit, $offset) as $d) {
                $result[] = new self($d);
            }
        }
        return $result;
    }
}