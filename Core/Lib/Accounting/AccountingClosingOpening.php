<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2018-2019 Carlos Garcia Gomez <carlos@facturascripts.com>
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
namespace FacturaScripts\Core\Lib\Accounting;

use FacturaScripts\Dinamic\Model\Asiento;
use FacturaScripts\Dinamic\Model\Ejercicio;
use FacturaScripts\Dinamic\Model\Subcuenta;

/**
 * Perform opening of account balances for the exercise.
 *
 * @author Artex Trading sa     <jcuello@artextrading.com>
 */
class AccountingClosingOpening extends AccountingClosingBase
{

    /**
     *
     * @var Ejercicio
     */
    protected $newExercise;

    /**
     * Delete main process.
     * Delete opening accounting entry from exercise.
     *
     * @param Ejercicio $exercise
     * @return boolean
     */
    public function delete($exercise): bool
    {
        $this->exercise = $exercise;
        $this->loadNewExercise();
        return $this->deleteAccountEntry($this->newExercise, Asiento::OPERATION_OPENING);
    }

    /**
     * Execute main process.
     * Create a new account entry for channel with a one line by account balance.
     * The informed exercise must be the closed exercise.
     *
     * @param Ejercicio $exercise
     * @param int       $idjournal
     * @return boolean
     */
    public function exec($exercise, $idjournal): bool
    {
        return $this->delete($exercise) && $this->copyAccounts() && parent::exec($exercise, $idjournal);
    }

    /**
     * Copy accounts and subaccounts from exercise to new exercise
     *
     * @return bool
     */
    protected function copyAccounts(): bool
    {
        $subaccount = new Subcuenta();
        foreach (self::$dataBase->selectLimit($this->getSQLCopyAccounts(), 0) as $value) {
            if ($subaccount->loadFromCode($value['idsubcuenta'])) {
                $subaccount->idcuenta = null;
                $subaccount->idsubcuenta = null;
                $subaccount->debe = 0.00;
                $subaccount->haber = 0.00;
                $subaccount->codejercicio = $this->newExercise->codejercicio;
                if (!$subaccount->save()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Get the concept for the accounting entry and its lines.
     *
     * @return string
     */
    protected function getConcept(): string
    {
        return $this->toolBox()->i18n()->trans(
            'closing-opening-concept',
            ['%exercise%' => $this->newExercise->nombre]
        );
    }

    /**
     * Get the date for the accounting entry.
     *
     * @return date
     */
    protected function getDate()
    {
        return $this->newExercise->fechainicio;
    }

    /**
     * Get the special operation identifier for the accounting entry.
     *
     * @return string
     */
    protected function getOperation(): string
    {
        return Asiento::OPERATION_CLOSING;
    }

    /**
     * Get the sub accounts filter for obtain balance.
     *
     * @return string
     */
    protected function getSubAccountsFilter(): string
    {
        return "AND t2.codsubcuenta BETWEEN '1' AND '599999999999999'";
    }

    /**
     * Add accounting entry line with balance override.
     * Return true without doing anything, if you do not need balance override.
     *
     * @param Asiento $accountEntry
     * @param float   $debit
     * @param float   $credit
     * @return bool
     */
    protected function saveBalanceLine($accountEntry, $debit, $credit): bool
    {
        return true;
    }

    /**
     * Establishes the common data of the accounting entry
     *
     * @param Asiento $accountEntry
     */
    protected function setData(&$entry)
    {
        parent::setData($entry);
        $entry->codejercicio = $this->newExercise->codejercicio;
    }

    /**
     * Establishes the common data of the entries of the accounting entry
     *
     * @param Partida $line
     * @param array   $data
     */
    protected function setDataLine(&$line, $data)
    {
        parent::setDataLine($line, $data);
        if ($data['debit'] > $data['credit']) {
            $line->debe = $data['debit'] - $data['credit'];
            return;
        }
        $line->haber = $data['credit'] - $data['debit'];
    }

    /**
     * Return sql sentence for subaccounts with balance
     * that don't exists into next exercise.
     *
     * @return string
     */
    private function getSQLCopyAccounts(): string
    {
        return "SELECT t2.idsubcuenta, t2.codsubcuenta, t2.haber AS debe, t2.debe AS haber"
            . " FROM asientos t1"
            . " INNER JOIN partidas t2 ON t2.idasiento = t1.idasiento"
            . " WHERE t1.codejercicio = '" . $this->exercise->codejercicio . "'"
            .   " AND t1.operacion = '" . Asiento::OPERATION_CLOSING . "'"
            . " AND NOT EXISTS("
            .       "SELECT 1 FROM subcuentas t3"
            .       " WHERE t3.codsubcuenta = t2.codsubcuenta"
            .         " AND t3.codejercicio = '" . $this->newExercise->codejercicio . "'"
            .        ")";
    }

    /**
     * Search and load next exercise of indicated exercise.
     */
    private function loadNewExercise()
    {
        $date = date('d-m-Y', strtotime($this->exercise->fechainicio . ' +1 year'));

        $this->newExercise = new Ejercicio();
        $this->newExercise->idempresa = $this->exercise->idempresa;
        $this->newExercise->loadFromDate($date, true, true);
    }
}
