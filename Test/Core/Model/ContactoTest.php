<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
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

use FacturaScripts\Core\Model\Contacto;
use FacturaScripts\Test\Core\LogErrorsTrait;
use FacturaScripts\Test\Core\RandomDataTrait;
use PHPUnit\Framework\TestCase;

final class ContactoTest extends TestCase
{
    use LogErrorsTrait;
    use RandomDataTrait;

    public function testCreate()
    {
        $contact = new Contacto();
        $contact->nombre = 'Test';
        $contact->apellidos = 'Contact';
        $this->assertTrue($contact->save(), 'contact-cant-save');
        $this->assertNotNull($contact->primaryColumnValue(), 'contact-not-stored');
        $this->assertTrue($contact->exists(), 'contact-cant-persist');
        $this->assertTrue($contact->delete(), 'contact-cant-delete');
    }

    public function testCreateEmail()
    {
        $contact = new Contacto();
        $contact->email = 'pepe@test.es';
        $this->assertTrue($contact->save(), 'contact-cant-save');

        // eliminamos
        $this->assertTrue($contact->delete(), 'contact-cant-delete');
    }

    public function testCreateCustomerAddress()
    {
        // creamos el cliente
        $customer = $this->getRandomCustomer();
        $customer->save();

        // creamos el contacto
        $contact = new Contacto();
        $contact->codcliente = $customer->codcliente;
        $contact->direccion = 'Test';
        $this->assertTrue($contact->save(), 'customer-address-cant-save');

        // eliminamos
        $this->assertTrue($contact->delete(), 'contact-cant-delete');
        $this->assertTrue($customer->delete(), 'customer-cant-delete');
    }

    public function testCreateSupplierAddress()
    {
        // creamos el proveedor
        $supplier = $this->getRandomSupplier();
        $supplier->save();

        // creamos el contacto
        $contact = new Contacto();
        $contact->codproveedor = $supplier->codproveedor;
        $contact->direccion = 'Test';
        $this->assertTrue($contact->save(), 'supplier-address-cant-save');

        // eliminamos
        $this->assertTrue($contact->delete(), 'contact-cant-delete');
        $this->assertTrue($supplier->delete(), 'supplier-cant-delete');
    }

    public function testCantCreateEmpty()
    {
        $contact = new Contacto();
        $contact->nombre = '';
        $contact->apellidos = '';
        $contact->email = '';
        $contact->descripcion = '';
        $contact->direccion = '';
        $this->assertFalse($contact->save(), 'contact-cant-save-empty');
    }

    protected function tearDown(): void
    {
        $this->logErrors();
    }
}
