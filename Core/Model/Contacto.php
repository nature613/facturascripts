<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2015-2018 Carlos Garcia Gomez <carlos@facturascripts.com>
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
namespace FacturaScripts\Core\Model;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base\Utils;

/**
 * Description of crm_contacto
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class Contacto extends Base\Contact
{

    use Base\ModelTrait;

    /**
     * True if it supports marketing, but False.
     *
     * @var bool
     */
    public $admitemarketing;

    /**
     * Last name.
     *
     * @var string
     */
    public $apellidos;

    /**
     * Contact charge.
     *
     * @var string
     */
    public $cargo;

    /**
     * Contact city.
     *
     * @var string
     */
    public $ciudad;

    /**
     * Associated agente to this contact. Agent model.
     *
     * @var string
     */
    public $codagente;

    /**
     * Associated client to this contact. Client model.
     *
     * @var string
     */
    public $codcliente;

    /**
     * Contact country.
     *
     * @var string
     */
    public $codpais;

    /**
     * Postal code of the contact.
     *
     * @var string
     */
    public $codpostal;

    /**
     * Address of the contact.
     *
     * @var string
     */
    public $direccion;

    /**
     * Contact company.
     *
     * @var string
     */
    public $empresa;

    /**
     * Primary key.
     *
     * @var int
     */
    public $idcontacto;

    /**
     * Last activity date.
     *
     * @var string
     */
    public $lastactivity;

    /**
     * Last IP used.
     *
     * @var string
     */
    public $lastip;

    /**
     * Session key, saved also in cookie. Regenerated when user log in.
     *
     * @var string
     */
    public $logkey;

    /**
     * Password hashed with password_hash()
     *
     * @var string
     */
    public $password;

    /**
     * Contact province.
     *
     * @var string
     */
    public $provincia;

    /**
     *
     * @var integer
     */
    public $puntos;

    /**
     * TRUE if contact is verified.
     *
     * @var bool
     */
    public $verificado;

    /**
     * Reset the values of all model properties.
     */
    public function clear()
    {
        parent::clear();
        $this->admitemarketing = false;
        $this->codpais = AppSettings::get('default', 'codpais');
        $this->puntos = 0;
        $this->verificado = false;
    }

    /**
     * Returns full name.
     *
     * @return string
     */
    public function fullName()
    {
        return $this->nombre . ' ' . $this->apellidos;
    }

    /**
     * This function is called when creating the model table. Returns the SQL
     * that will be executed after the creation of the table. Useful to insert values
     * default.
     *
     * @return string
     */
    public function install()
    {
        new Cliente();
        return parent::install();
    }

    /**
     * Generates a new login key for the user. It also updates lastactivity
     * ans last IP.
     *
     * @param string $ipAddress
     *
     * @return string
     */
    public function newLogkey($ipAddress)
    {
        $this->lastactivity = date('d-m-Y H:i:s');
        $this->lastip = $ipAddress;
        $this->logkey = Utils::randomString(99);

        return $this->logkey;
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn()
    {
        return 'idcontacto';
    }

    /**
     * Returns the name of the column used to describe this item.
     *
     * @return string
     */
    public function primaryDescriptionColumn()
    {
        return 'email';
    }

    /**
     * Asigns the new password to the contact.
     *
     * @param string $pass
     */
    public function setPassword(string $pass): void
    {
        $this->password = password_hash($pass, PASSWORD_DEFAULT);
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'contactos';
    }

    /**
     * Returns True if there is no errors on properties values.
     *
     * @return bool
     */
    public function test()
    {
        $this->apellidos = Utils::noHtml($this->apellidos);
        $this->cargo = Utils::noHtml($this->cargo);
        $this->ciudad = Utils::noHtml($this->ciudad);
        $this->direccion = Utils::noHtml($this->direccion);
        $this->empresa = Utils::noHtml($this->empresa);
        $this->provincia = Utils::noHtml($this->provincia);

        return parent::test();
    }

    /**
     * Verifies the login key.
     *
     * @param string $value
     *
     * @return bool
     */
    public function verifyLogkey($value)
    {
        return $this->logkey === $value;
    }

    /**
     * Verifies password. It also rehash the password if needed.
     *
     * @param string $pass
     *
     * @return bool
     */
    public function verifyPassword(string $pass): bool
    {
        if (password_verify($pass, $this->password)) {
            if (password_needs_rehash($this->password, PASSWORD_DEFAULT)) {
                $this->setPassword($pass);
            }

            return true;
        }

        return false;
    }
}
