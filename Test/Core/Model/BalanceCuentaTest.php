<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2022 Carlos Garcia Gomez <carlos@facturascripts.com>
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

namespace FacturaScripts\Test\Core\Model;

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Model\Balance;
use FacturaScripts\Core\Model\BalanceCuenta;
use FacturaScripts\Test\Core\LogErrorsTrait;
use PHPUnit\Framework\TestCase;

/**
 * Description of BalanceCuenta
 *
 * @author Daniel Ferández Giménez <hola@danielfg.es>
 */
final class BalanceCuentaTest extends TestCase
{

    use LogErrorsTrait;

    public static function setUpBeforeClass(): void
    {
        $accountBalance = new BalanceCuenta();
        $database = new DataBase();
        $database->updateSequence($accountBalance::tableName(), $accountBalance->getModelFields());
    }

    public function testCreate()
    {
        // creamos un balance
        $balance = new Balance();
        $balance->codbalance = 'TEST';
        $balance->naturaleza = 'TEST NATURALEZA';
        $this->assertTrue($balance->save(), 'balance-cant-save');

        // creamos un balance de cuenta para el balance anterior
        $accountBalance = new BalanceCuenta();
        $accountBalance->codbalance = $balance->codbalance;
        $accountBalance->codcuenta = 'TEST';
        $accountBalance->desccuenta = 'TEST DESCRIPTION';
        $this->assertTrue($accountBalance->save(), 'account-balance-cant-save');
        $this->assertNotNull($accountBalance->primaryColumnValue(), 'account-balance-not-stored');
        $this->assertTrue($accountBalance->exists(), 'account-balance-cant-persist');

        // eliminamos
        $this->assertTrue($accountBalance->delete(), 'account-balance-cant-delete');
        $this->assertTrue($balance->delete(), 'balance-cant-delete');
    }

    public function testAccountBalanceNoBalance()
    {
        // creamos un balance de cuenta sin balance
        $accountBalance = new BalanceCuenta();
        $accountBalance->codcuenta = 'TEST';
        $accountBalance->desccuenta = 'TEST DESCRIPTION';

        // no debe guardar
        $this->assertFalse($accountBalance->save(), 'account-balance-can-save-without-balance');
    }

    public function testHtmlOnFields()
    {
        // creamos un balance
        $balance = new Balance();
        $balance->codbalance = 'TEST';
        $balance->naturaleza = 'TEST NATURALEZA';
        $this->assertTrue($balance->save(), 'balance-cant-save');

        // creamos un balance de cuenta con html en los campos
        $accountBalance = new BalanceCuenta();
        $accountBalance->codbalance = $balance->codbalance;
        $accountBalance->codcuenta = 'TEST';
        $accountBalance->desccuenta = '<b>Test Html</b>';
        $this->assertTrue($accountBalance->save(), 'account-balance-cant-save');

        // comprobamos que el html ha sido escapado
        $noHtml = ToolBox::utils()::noHtml('<b>Test Html</b>');
        $this->assertEquals($noHtml, $accountBalance->desccuenta, 'account-balance-wrong-html');

        // eliminamos
        $this->assertTrue($accountBalance->delete(), 'account-balance-cant-delete');
        $this->assertTrue($balance->delete(), 'balance-cant-delete');
    }

    public function testDeleteCascade()
    {
        // creamos un balance
        $balance = new Balance();
        $balance->codbalance = 'TEST';
        $balance->naturaleza = 'TEST NATURALEZA';
        $this->assertTrue($balance->save(), 'balance-cant-save');

        // creamos un balance de cuenta
        $accountBalance = new BalanceCuenta();
        $accountBalance->codbalance = $balance->codbalance;
        $accountBalance->codcuenta = 'TEST';
        $accountBalance->desccuenta = 'TEST DESCRIPTION';
        $this->assertTrue($accountBalance->save(), 'account-balance-cant-save');

        // eliminamos el balance
        $this->assertTrue($balance->delete(), 'balance-cant-delete');

        // comprobamos que no existe el balance de cuenta
        $this->assertFalse($accountBalance->exists(), 'account-balance-exists');
    }

    protected function tearDown(): void
    {
        $this->logErrors();
    }
}
