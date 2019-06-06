<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2019 Carlos Garcia Gomez <carlos@facturascripts.com>
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

use FacturaScripts\Core\Model\Base;

/**
 * Description of ReciboProveedor
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class ReciboProveedor extends Base\Receipt
{

    use Base\ModelTrait;

    /**
     *
     * @var string
     */
    public $codproveedor;

    /**
     * 
     * @return FacturaProveedor
     */
    public function getInvoice()
    {
        $invoice = new FacturaProveedor();
        $invoice->loadFromCode($this->idfactura);
        return $invoice;
    }

    /**
     * 
     * @return Proveedor
     */
    public function getSubject()
    {
        $proveedor = new Proveedor();
        $proveedor->loadFromCode($this->codproveedor);
        return $proveedor;
    }

    /**
     * 
     * @return string
     */
    public function install()
    {
        /// needed dependencies
        new Proveedor();

        return parent::install();
    }

    /**
     * 
     * @return string
     */
    public static function tableName()
    {
        return 'recibospagosprov';
    }

    /**
     * 
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'ListFacturaProveedor?activetab=List')
    {
        if ('list' === $type && !empty($this->idfactura)) {
            return $this->getInvoice()->url() . '&activetab=List' . $this->modelClassName();
        }

        return parent::url($type, $list);
    }

    /**
     * 
     * @param array $values
     *
     * @return bool
     */
    protected function saveInsert(array $values = [])
    {
        if (parent::saveInsert($values)) {
            if ($this->pagado) {
                $pago = new PagoProveedor();
                $pago->fecha = $this->fecha;
                $pago->idrecibo = $this->idrecibo;
                $pago->importe = $this->importe;
                $pago->nick = $this->nick;
                $pago->save();
            }

            return true;
        }

        return false;
    }
}
