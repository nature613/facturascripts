<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2019 Carlos Garcia Gomez <carlos@facturascripts.com>
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
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\BaseView;
use FacturaScripts\Dinamic\Lib\Accounting\InvoiceToAccounting;
use FacturaScripts\Dinamic\Lib\BusinessDocumentGenerator;
use FacturaScripts\Dinamic\Lib\ExtendedController\PurchaseDocumentController;
use FacturaScripts\Dinamic\Lib\ReceiptGenerator;
use FacturaScripts\Dinamic\Model\FacturaProveedor;

/**
 * Controller to edit a single item from the FacturaProveedor model
 *
 * @author Carlos García Gómez      <carlos@facturascripts.com>
 * @author Francesc Pineda Segarra  <francesc.pineda.segarra@gmail.com>
 * @author Rafael San José Tovar    <rafael.sanjose@x-netdigital.com>
 */
class EditFacturaProveedor extends PurchaseDocumentController
{

    /**
     * Return the document class name.
     *
     * @return string
     */
    public function getModelClassName()
    {
        return 'FacturaProveedor';
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'purchases';
        $data['title'] = 'invoice';
        $data['icon'] = 'fas fa-file-invoice-dollar';
        return $data;
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createAccountsView($viewName = 'ListAsiento')
    {
        $this->addListView($viewName, 'Asiento', 'accounting-entries', 'fas fa-balance-scale');

        /// buttons
        $newButton = [
            'action' => 'generate-accounting',
            'icon' => 'fas fa-magic',
            'label' => 'generate-accounting-entry',
            'type' => 'action',
        ];
        $this->addButton($viewName, $newButton);

        /// settings
        $this->setSettings($viewName, 'btnNew', false);
    }

    /**
     * 
     * @param string $viewName
     */
    protected function createReceiptsView($viewName = 'ListReciboProveedor')
    {
        $this->addListView($viewName, 'ReciboProveedor', 'receipts', 'fas fa-dollar-sign');
        $this->views[$viewName]->addOrderBy(['vencimiento'], 'expiration');

        /// buttons
        $generateButton = [
            'action' => 'generate-receipts',
            'confirm' => 'true',
            'icon' => 'fas fa-magic',
            'label' => 'generate-receipts',
            'type' => 'action',
        ];
        $this->addButton($viewName, $generateButton);

        $payButton = [
            'action' => 'paid',
            'confirm' => 'true',
            'icon' => 'fas fa-check',
            'label' => 'paid',
            'type' => 'action',
        ];
        $this->addButton($viewName, $payButton);

        /// disable column
        $this->views[$viewName]->disableColumn('invoice');

        /// settings
        $this->setSettings($viewName, 'modalInsert', 'generate-receipts');
    }

    /**
     * Load views
     */
    protected function createViews()
    {
        parent::createViews();
        $this->createReceiptsView();
        $this->createAccountsView();
        $this->addHtmlView('Devoluciones', 'Tab/DevolucionesFacturaProveedor', 'FacturaProveedor', 'refunds', 'fas fa-share-square');
    }

    /**
     * 
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'generate-accounting':
                $this->generateAccountingAction();
                break;

            case 'generate-receipts':
                $this->generateReceiptsAction();
                break;

            case 'new-refund':
                $this->newRefundAction();
                break;

            case 'paid':
                return $this->paidAction();
        }

        return parent::execPreviousAction($action);
    }

    /**
     * 
     * @return bool
     */
    protected function generateAccountingAction()
    {
        $invoice = new FacturaProveedor();
        if (!$invoice->loadFromCode($this->request->query->get('code'))) {
            $this->miniLog->warning($this->i18n->trans('record-not-found'));
            return false;
        }

        $generator = new InvoiceToAccounting();
        $generator->generate($invoice);
        if (empty($invoice->idasiento)) {
            $this->miniLog->error($this->i18n->trans('record-save-error'));
            return false;
        }

        if ($invoice->save()) {
            $this->miniLog->notice($this->i18n->trans('record-updated-correctly'));
            return true;
        }

        $this->miniLog->error($this->i18n->trans('record-save-error'));
        return false;
    }

    /**
     * 
     * @return bool
     */
    protected function generateReceiptsAction()
    {
        $invoice = new FacturaProveedor();
        if (!$invoice->loadFromCode($this->request->query->get('code'))) {
            $this->miniLog->warning($this->i18n->trans('record-not-found'));
            return false;
        }

        $generator = new ReceiptGenerator();
        $number = (int) $this->request->request->get('number', '0');
        if ($generator->generate($invoice, $number)) {
            $this->miniLog->notice($this->i18n->trans('record-updated-correctly'));
            return true;
        }

        $this->miniLog->error($this->i18n->trans('record-save-error'));
        return false;
    }

    /**
     * Load data view procedure
     *
     * @param string   $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'Devoluciones':
            case 'ListReciboProveedor':
                $where = [new DataBaseWhere('idfactura', $this->getViewModelValue($this->getLineXMLView(), 'idfactura'))];
                $view->loadData('', $where);
                break;

            case 'ListAsiento':
                $where = [new DataBaseWhere('idasiento', $this->getViewModelValue($this->getLineXMLView(), 'idasiento'))];
                $view->loadData('', $where);
                break;

            default:
                parent::loadData($viewName, $view);
        }
    }

    /**
     * 
     * @return bool
     */
    protected function newRefundAction()
    {
        $invoice = new FacturaProveedor();
        if (!$invoice->loadFromCode($this->request->request->get('idfactura'))) {
            $this->miniLog->warning($this->i18n->trans('record-not-found'));
            return false;
        }

        $lines = [];
        $quantities = [];
        foreach ($invoice->getLines() as $line) {
            $quantity = (float) $this->request->request->get('refund_' . $line->primaryColumnValue(), '0');
            if (empty($quantity)) {
                continue;
            }

            $quantities[$line->primaryColumnValue()] = 0 - $quantity;
            $lines[] = $line;
        }

        $generator = new BusinessDocumentGenerator();
        if ($generator->generate($invoice, $invoice->modelClassName(), $lines, $quantities)) {
            foreach ($generator->getLastDocs() as $doc) {
                $doc->codigorect = $invoice->codigo;
                $doc->codserie = $this->request->request->get('codserie');
                $doc->fecha = $this->request->request->get('fecha');
                $doc->idfacturarect = $invoice->idfactura;
                $doc->numproveedor = $this->request->request->get('numproveedor');
                $doc->observaciones = $this->request->request->get('observaciones');
                if ($doc->save()) {
                    $this->miniLog->notice($this->i18n->trans('record-updated-correctly'));
                    $this->redirect($doc->url());
                    return true;
                }
            }
        }

        $this->miniLog->error($this->i18n->trans('record-save-error'));
        return false;
    }

    /**
     * 
     * @return bool
     */
    protected function paidAction()
    {
        if (!$this->permissions->allowUpdate) {
            $this->miniLog->warning($this->i18n->trans('not-allowed-modify'));
            return true;
        }

        $codes = $this->request->request->get('code');
        $model = $this->views[$this->active]->model;
        if (!is_array($codes) || empty($model)) {
            $this->miniLog->warning($this->i18n->trans('no-selected-item'));
            return true;
        }

        foreach ($codes as $code) {
            if (!$model->loadFromCode($code)) {
                $this->miniLog->error($this->i18n->trans('record-not-found'));
                continue;
            }

            $model->nick = $this->user->nick;
            $model->pagado = true;
            if (!$model->save()) {
                $this->miniLog->error($this->i18n->trans('record-save-error'));
                return true;
            }
        }

        $this->miniLog->notice($this->i18n->trans('record-updated-correctly'));
        $model->clear();
        return true;
    }
}
