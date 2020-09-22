<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2015-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
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
namespace FacturaScripts\Core\Base\DataBase;

use FacturaScripts\Core\Base\DataBase as db;
use FacturaScripts\Core\Base\ToolBox;

/**
 * This class allows to read and check the required structure for the database tables
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class DataBaseTools
{

    /**
     * DataBase object.
     *
     * @var db
     */
    private static $dataBase;

    /**
     * Checks and compares the database table structure with the xml definition.
     * Returns a SQL statement if there are differences.
     *
     * @param $tableName
     * @param $xmlCols
     * @param $xmlCons
     *
     * @return string
     */
    public static function checkTable($tableName, $xmlCols, $xmlCons)
    {
        /**
         * If we have to make changes to the restrictions, we first eliminate them all.
         * Then we will add the correct ones. We do it like this because it avoids problems in MySQL.
         */
        $dbCons = static::dataBase()->getConstraints($tableName);
        $sql = static::compareConstraints($tableName, $xmlCons, $dbCons, true);
        if ($sql !== '') {
            if (false === static::dataBase()->exec($sql)) {
                static::toolBox()->i18nLog()->critical('check-table', ['%tableName%' => $tableName]);
            }

            /// reload table constraints after changes
            $dbCons = static::dataBase()->getConstraints($tableName);
        }

        /// compare table columns and constraints against xml definition
        $dbCols = static::dataBase()->getColumns($tableName);
        return static::compareColumns($tableName, $xmlCols, $dbCols) .
            static::compareConstraints($tableName, $xmlCons, $dbCons);
    }

    /**
     * Creates the database table with the provided structure.
     *
     * @param string $tableName
     * @param array  $xmlCols
     * @param array  $xmlCons
     *
     * @return string
     */
    public static function generateTable($tableName, $xmlCols, $xmlCons)
    {
        return static::sql()->sqlCreateTable($tableName, $xmlCols, $xmlCons);
    }

    /**
     * Extracts columns and constraints form the XML definition.
     *
     * @param string $tableName
     * @param array  $columns
     * @param array  $constraints
     *
     * @return bool
     */
    public static function getXmlTable($tableName, &$columns, &$constraints)
    {
        $filename = static::getXmlTableLocation($tableName);
        if (false === \file_exists($filename)) {
            static::toolBox()->i18nLog()->critical('file-not-found', ['%fileName%' => $filename]);
            return false;
        }

        $xml = \simplexml_load_string(\file_get_contents($filename, true));
        if (false === $xml) {
            static::toolBox()->i18nLog()->critical('error-reading-file', ['%fileName%' => $filename]);
            return false;
        }

        if ($xml->column) {
            static::checkXmlColumns($columns, $xml);
            if ($xml->constraint) {
                static::checkXmlConstraints($constraints, $xml);
            }

            return true;
        }

        return false;
    }

    /**
     * Returns the full file path for table XML file.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function getXmlTableLocation($tableName)
    {
        $fileName = \FS_FOLDER . '/Dinamic/Table/' . $tableName . '.xml';
        if (\FS_DEBUG && false === \file_exists($fileName)) {
            return \FS_FOLDER . '/Core/Table/' . $tableName . '.xml';
        }

        return $fileName;
    }

    /**
     * Updates names and types for each column.
     *
     * @param array             $columns
     * @param \SimpleXMLElement $xml
     */
    private static function checkXmlColumns(&$columns, $xml)
    {
        $key = 0;
        foreach ($xml->column as $col) {
            $columns[$key]['name'] = (string) $col->name;
            $columns[$key]['type'] = (string) $col->type;
            $columns[$key]['null'] = $col->null && \strtolower($col->null) === 'no' ? 'NO' : 'YES';
            $columns[$key]['default'] = $col->default === '' ? null : (string) $col->default;
            ++$key;
        }
    }

    /**
     * Updates names and constraints for each constraint.
     *
     * @param array             $constraints
     * @param \SimpleXMLElement $xml
     */
    private static function checkXmlConstraints(&$constraints, $xml)
    {
        $key = 0;
        foreach ($xml->constraint as $col) {
            $constraints[$key]['name'] = (string) $col->name;
            $constraints[$key]['constraint'] = (string) $col->type;
            ++$key;
        }
    }

    /**
     * Compares two arrays of columns, returns a SQL statement if there are differences.
     *
     * @param string $tableName
     * @param array  $xmlCols
     * @param array  $dbCols
     *
     * @return string
     */
    private static function compareColumns($tableName, $xmlCols, $dbCols)
    {
        $sql = '';
        foreach ($xmlCols as $xmlCol) {
            $column = static::searchInArray($dbCols, 'name', $xmlCol['name']);
            if (empty($column)) {
                $sql .= static::sql()->sqlAlterAddColumn($tableName, $xmlCol);
                continue;
            }

            if (false === static::compareDataTypes($column['type'], $xmlCol['type'])) {
                $sql .= static::sql()->sqlAlterModifyColumn($tableName, $xmlCol);
            }

            if ($column['default'] === null && $xmlCol['default'] !== '') {
                $sql .= static::sql()->sqlAlterColumnDefault($tableName, $xmlCol);
            }

            if ($column['is_nullable'] !== $xmlCol['null']) {
                $sql .= static::sql()->sqlAlterColumnNull($tableName, $xmlCol);
            }
        }

        return $sql;
    }

    /**
     * Compares two arrays of constraints, returns a SQL statement if there are differences.
     *
     * @param string $tableName
     * @param array  $xmlCons
     * @param array  $dbCons
     * @param bool   $deleteOnly
     *
     * @return string
     */
    private static function compareConstraints($tableName, $xmlCons, $dbCons, $deleteOnly = false)
    {
        /// remove unnecesary constraints (except primary keys and uniques)
        $sql = '';
        foreach ($dbCons as $dbCon) {
            $column = static::searchInArray($xmlCons, 'name', $dbCon['name']);
            if (empty($column) && false === \strpos('PRIMARY;UNIQUE', $dbCon['name'])) {
                $sql .= static::sql()->sqlDropConstraint($tableName, $dbCon);
            }
        }

        if (empty($xmlCons) || $deleteOnly || false === \FS_DB_FOREIGN_KEYS) {
            return $sql;
        }

        /// add new constraints
        foreach ($xmlCons as $xmlCon) {
            /// exclude primary keys on mysql because of fail
            if (\strpos($xmlCon['constraint'], 'PRIMARY') === 0 && \strtolower(\FS_DB_TYPE) === 'mysql') {
                continue;
            }

            $column = static::searchInArray($dbCons, 'name', $xmlCon['name']);
            if (empty($column)) {
                $sql .= static::sql()->sqlAddConstraint($tableName, $xmlCon['name'], $xmlCon['constraint']);
            }
        }

        return $sql;
    }

    /**
     * Compares data types from a column. Returns True if they are the same.
     *
     * @param string $dbType
     * @param string $xmlType
     *
     * @return bool
     */
    private static function compareDataTypes($dbType, $xmlType)
    {
        return static::dataBase()->getEngine()->compareDataTypes($dbType, $xmlType);
    }

    /**
     * 
     * @return db
     */
    private static function dataBase()
    {
        if (!isset(self::$dataBase)) {
            self::$dataBase = new db();
        }

        return self::$dataBase;
    }

    /**
     * Look for a column with a value by his name in array.
     *
     * @param array  $items
     * @param string $index
     * @param string $value
     *
     * @return array
     */
    private static function searchInArray($items, $index, $value)
    {
        foreach ($items as $column) {
            if ($column[$index] === $value) {
                return $column;
            }
        }

        return [];
    }

    /**
     * 
     * @return DataBaseQueries
     */
    private static function sql()
    {
        return static::dataBase()->getEngine()->getSQL();
    }

    /**
     * 
     * @return ToolBox
     */
    private static function toolBox()
    {
        return new ToolBox();
    }
}
