<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
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
use FacturaScripts\Dinamic\Model\BalanceCuentaA;
use FacturaScripts\Dinamic\Model\Partida;

/**
 * Description of BalanceSheet
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Raul Jiménez         <comercial@nazcanetworks.com>
 * @author Artex Trading sa     <jcuello@artextrading.com>
 */
class BalanceSheet extends AccountingBase
{

    /**
     * Date from for filter
     *
     * @var string
     */
    protected $dateFromPrev;

    /**
     * * Date to for filter
     *
     * @var string
     */
    protected $dateToPrev;

    /**
     * BalanceSheet constructor
     */
    public function __construct()
    {
        parent::__construct();

        /// needed dependencies
        new Partida();
        new BalanceCuentaA();
    }

    /**
     * Generate the balance amounts between two dates.
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param array  $params
     *
     * @return array
     */
    public function generate(string $dateFrom, string $dateTo, array $params = [])
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->dateFromPrev = $this->addToDate($dateFrom, '-1 year');
        $this->dateToPrev = $this->addToDate($dateTo, '-1 year');

        $data = $this->getData($params);
        if (empty($data)) {
            return [];
        }

        /// every page is a table
        $pages = [$this->calcSheetBalance($data)];
        return $pages;
    }

    /**
     * Obtains the balances for each one of the sections of the balance sheet according to their assigned accounts.
     *
     * @return array
     */
    protected function getData(array $params = [])
    {
        $dateFrom = $this->dataBase->var2str($this->dateFrom);
        $dateTo = $this->dataBase->var2str($this->dateTo);
        $dateFromPrev = $this->dataBase->var2str($this->dateFromPrev);
        $dateToPrev = $this->dataBase->var2str($this->dateToPrev);

        $entryJoin = 'asto.idempresa = ' . $this->exercise->idempresa
            . ' AND asto.operacion <> \'' . Asiento::OPERATION_CLOSING . '\''
            . ' AND asto.fecha BETWEEN ' . $dateFromPrev . ' AND ' . $dateTo;

        $sql = 'SELECT cb.codbalance,cb.naturaleza,cb.descripcion1,cb.descripcion2,cb.descripcion3,cb.descripcion4,ccb.codcuenta,'
            . ' SUM(CASE WHEN asto.fecha BETWEEN ' . $dateFrom . ' AND ' . $dateTo . ' THEN pa.debe - pa.haber ELSE 0 END) saldo,'
            . ' SUM(CASE WHEN asto.fecha BETWEEN ' . $dateFromPrev . ' AND ' . $dateToPrev . ' THEN pa.debe - pa.haber ELSE 0 END) saldoprev'
            . ' FROM balances cb '
            . ' INNER JOIN balancescuentasabreviadas ccb ON ccb.codbalance = cb.codbalance '
            . ' INNER JOIN asientos asto ON ' . $entryJoin
            . ' INNER JOIN partidas pa ON pa.idasiento = asto.idasiento AND substr(pa.codsubcuenta, 1, 1) BETWEEN \'1\' AND \'5\' AND pa.codsubcuenta LIKE CONCAT(ccb.codcuenta,\'%\')'
            . ' WHERE ' . $this->getDataWhere($params)
            . ' GROUP BY 1, 2, 3, 4, 5, 6, 7 '
            . ' ORDER BY cb.naturaleza, cb.nivel1, cb.nivel2, cb.orden3, cb.nivel4';

        return $this->dataBase->select($sql);
    }

    /**
     *
     * @param array $params
     * @return string
     */
    protected function getDataWhere(array $params = [])
    {
        $where = 'cb.naturaleza IN (\'A\', \'P\')';

        $channel = $params['channel'] ?? '';
        if (!empty($channel)) {
            $where .= ' AND asto.canal = ' . $channel;
        }

        $subaccountFrom = $params['subaccount-from'] ?? '';
        $subaccountTo = $params['subaccount-to'] ?? $subaccountFrom;
        if (!empty($subaccountFrom) || (!empty($subaccountTo))) {
            $where .= ' AND pa.codsubcuenta BETWEEN ' . $this->dataBase->var2str($subaccountFrom) . ' AND ' . $this->dataBase->var2str($subaccountTo);
        }

        return $where;
    }

    /**
     * Process a balance values.
     *
     * @param array  $linea
     * @param array  $balance
     * @param string $description
     */
    protected function processDescription(&$linea, &$balance, $description)
    {
        $index = $linea[$description];
        if (empty($index)) {
            return;
        }

        if (!array_key_exists($index, $balance)) {
            $balance[$index] = [
                'descripcion' => $index,
                'saldo' => $linea['saldo'],
                'saldoprev' => $linea['saldoprev'],];
        } else {
            $balance[$index]['saldo'] += $linea['saldo'];
            $balance[$index]['saldoprev'] += $linea['saldoprev'];
        }
    }

    /**
     * Process the line data to use the appropiate formats.
     *
     * @param array $line
     *
     * @return array
     */
    protected function processLine($line)
    {
        $line['descripcion'] = $this->toolBox()->utils()->fixHtml($line['descripcion']);
        $line['saldo'] = $this->toolBox()->coins()->format($line['saldo'], FS_NF0, '');
        $line['saldoprev'] = $this->toolBox()->coins()->format($line['saldoprev'], FS_NF0, '');
        return $line;
    }

    /**
     * Format de balance including then chapters
     *
     * @param array $data
     *
     * @return array
     */
    private function calcSheetBalance($data)
    {
        $balanceCalculado = [];
        foreach ($data as $lineaBalance) {
            if (!array_key_exists($lineaBalance['naturaleza'], $balanceCalculado)) {
                $balanceCalculado[$lineaBalance['naturaleza']] = [
                    'descripcion' => $lineaBalance['naturaleza'] == 'A' ? 'ACTIVO' : 'PASIVO',
                    'saldo' => $lineaBalance['saldo'],
                    'saldoprev' => $lineaBalance['saldoprev']
                ];
            } else {
                $balanceCalculado[$lineaBalance['naturaleza']]['saldo'] += $lineaBalance['saldo'];
                $balanceCalculado[$lineaBalance['naturaleza']]['saldoprev'] += $lineaBalance['saldoprev'];
            }

            $this->processDescription($lineaBalance, $balanceCalculado, 'descripcion1');
            $this->processDescription($lineaBalance, $balanceCalculado, 'descripcion2');
            $this->processDescription($lineaBalance, $balanceCalculado, 'descripcion3');
            $this->processDescription($lineaBalance, $balanceCalculado, 'descripcion4');
        }

        $balanceFinal = [];
        foreach ($balanceCalculado as $lineaBalance) {
            $balanceFinal[] = $this->processLine($lineaBalance);
        }

        return $balanceFinal;
    }
}
