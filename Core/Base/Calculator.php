<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2018-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\Base;

use FacturaScripts\Core\Base\Contract\CalculatorModInterface;
use FacturaScripts\Core\Lib\RegimenIVA;
use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Core\Model\Base\BusinessDocumentLine;
use FacturaScripts\Core\Model\Impuesto;

final class Calculator
{
    /**
     * @var CalculatorModInterface[]
     */
    public static $mods = [];

    public static function addMod(CalculatorModInterface $mod): void
    {
        self::$mods[] = $mod;
    }

    public static function calculate(BusinessDocument &$doc, array &$lines, bool $save): bool
    {
        // ponemos totales a 0
        self::clearTotals($doc, $lines);

        // calculamos subtotales en líneas
        foreach ($lines as $line) {
            self::calculateLine($doc, $line);
        }

        // calculamos los totales
        $subtotals = self::getSubtotals($doc, $lines);
        $doc->irpf = $subtotals['irpf'];
        $doc->neto = $subtotals['neto'];
        $doc->netosindto = $subtotals['netosindto'];
        $doc->total = $subtotals['total'];
        $doc->totalirpf = $subtotals['totalirpf'];
        $doc->totaliva = $subtotals['totaliva'];
        $doc->totalrecargo = $subtotals['totalrecargo'];
        $doc->totalsuplidos = $subtotals['totalsuplidos'];

        // turno para que los mods apliquen cambios
        foreach (self::$mods as $mod) {
            // si el mod devuelve false, terminamos
            if (false === $mod->calculate($doc, $lines)) {
                break;
            }
        }

        return $save && self::save($doc, $lines);
    }

    /**
     * @param BusinessDocument $doc
     * @param BusinessDocumentLine[] $lines
     *
     * @return array
     */
    public static function getSubtotals(BusinessDocument $doc, array $lines): array
    {
        $subtotals = [
            'irpf' => 0.0,
            'iva' => [],
            'neto' => 0.0,
            'netosindto' => 0.0,
            'total' => 0.0,
            'totalirpf' => 0.0,
            'totaliva' => 0.0,
            'totalrecargo' => 0.0,
            'totalsuplidos' => 0.0
        ];

        // acumulamos por cada línea
        foreach ($lines as $line) {
            $pvpTotal = $line->pvptotal * (100 - $doc->dtopor1) / 100 * (100 - $doc->dtopor2) / 100;
            if (empty($pvpTotal)) {
                continue;
            } elseif ($line->suplido) {
                $subtotals['totalsuplidos'] += $pvpTotal;
                continue;
            }

            // IRPF
            $subtotals['irpf'] = max([$line->irpf, $subtotals['irpf']]);
            $subtotals['totalirpf'] += $pvpTotal * $line->irpf / 100;

            // IVA
            $ivaKey = $line->iva . '|' . $line->recargo;
            if (false === array_key_exists($ivaKey, $subtotals['iva'])) {
                $subtotals['iva'][$ivaKey] = [
                    'iva' => $line->iva,
                    'neto' => 0.0,
                    'netosindto' => 0.0,
                    'recargo' => $line->recargo,
                    'totaliva' => 0.0,
                    'totalrecargo' => 0.0
                ];
            }

            $subtotals['iva'][$ivaKey]['neto'] += $pvpTotal;
            $subtotals['iva'][$ivaKey]['netosindto'] += $line->pvptotal;

            if ($line->iva > 0) {
                $subtotals['iva'][$ivaKey]['totaliva'] += $line->getTax()->tipo === Impuesto::TYPE_FIXED_VALUE ?
                    $pvpTotal * $line->iva :
                    $pvpTotal * $line->iva / 100;
            }

            // recargo de equivalencia
            if ($line->recargo > 0) {
                $subtotals['iva'][$ivaKey]['totalrecargo'] += $line->getTax()->tipo === Impuesto::TYPE_FIXED_VALUE ?
                    $pvpTotal * $line->recargo :
                    $pvpTotal * $line->recargo / 100;
            }
        }

        // redondeamos los IVAs
        foreach ($subtotals['iva'] as $key => $value) {
            $subtotals['iva'][$key]['neto'] = round($value['neto'], FS_NF0);
            $subtotals['iva'][$key]['netosindto'] = round($value['netosindto'], FS_NF0);
            $subtotals['iva'][$key]['totaliva'] = round($value['totaliva'], FS_NF0);
            $subtotals['iva'][$key]['totalrecargo'] = round($value['totalrecargo'], FS_NF0);

            // trasladamos a los subtotales
            $subtotals['neto'] += round($value['neto'], FS_NF0);
            $subtotals['netosindto'] += round($value['netosindto'], FS_NF0);
            $subtotals['totaliva'] += round($value['totaliva'], FS_NF0);
            $subtotals['totalrecargo'] += round($value['totalrecargo'], FS_NF0);
        }

        // redondeamos los subtotales
        $subtotals['neto'] = round($subtotals['neto'], FS_NF0);
        $subtotals['netosindto'] = round($subtotals['netosindto'], FS_NF0);
        $subtotals['totalirpf'] = round($subtotals['totalirpf'], FS_NF0);
        $subtotals['totaliva'] = round($subtotals['totaliva'], FS_NF0);
        $subtotals['totalrecargo'] = round($subtotals['totalrecargo'], FS_NF0);
        $subtotals['totalsuplidos'] = round($subtotals['totalsuplidos'], FS_NF0);

        // calculamos el total
        $subtotals['total'] = $subtotals['neto'] + $subtotals['totalsuplidos'] + $subtotals['totaliva']
            + $subtotals['totalrecargo'] - $subtotals['totalirpf'];

        // turno para que los mods apliquen cambios
        foreach (self::$mods as $mod) {
            // si el mod devuelve false, terminamos
            if (false === $mod->getSubtotals($subtotals, $doc, $lines)) {
                break;
            }
        }

        return $subtotals;
    }

    private static function calculateLine(BusinessDocument $doc, BusinessDocumentLine &$line): void
    {
        $line->pvpsindto = $line->cantidad * $line->pvpunitario;
        $line->pvptotal = $line->pvpsindto * (100 - $line->dtopor) / 100 * (100 - $line->dtopor2) / 100;

        // ¿La serie es sin impuestos?
        if ($doc->getSerie()->siniva) {
            $line->iva = $line->recargo = 0.0;
        }

        // comprobamos el régimen IVA
        $regimen = $doc->getSubject()->regimeniva ?? RegimenIVA::TAX_SYSTEM_GENERAL;
        switch ($regimen) {
            case RegimenIVA::TAX_SYSTEM_EXEMPT:
                $line->iva = $line->recargo = 0.0;
                break;

            case RegimenIVA::TAX_SYSTEM_GENERAL:
                $line->recargo = 0.0;
                break;
        }

        // turno para que los mods apliquen cambios
        foreach (self::$mods as $mod) {
            // si el mod devuelve false, terminamos
            if (false === $mod->calculateLine($doc, $line)) {
                break;
            }
        }
    }

    /**
     * @param BusinessDocument $doc
     * @param BusinessDocumentLine[] $lines
     *
     * @return void
     */
    private static function clearTotals(BusinessDocument &$doc, array &$lines): void
    {
        $doc->neto = 0.0;
        $doc->netosindto = 0.0;
        $doc->total = 0.0;
        $doc->totaleuros = 0.0;
        $doc->totalirpf = 0.0;
        $doc->totaliva = 0.0;
        $doc->totalrecargo = 0.0;
        $doc->totalsuplidos = 0.0;

        foreach ($lines as $line) {
            $line->pvpsindto = $line->pvptotal = 0.0;
        }

        // turno para que los mods apliquen cambios
        foreach (self::$mods as $mod) {
            // si el mod devuelve false, terminamos
            if (false === $mod->clearTotals($doc, $lines)) {
                break;
            }
        }
    }

    /**
     * @param BusinessDocument $doc
     * @param BusinessDocumentLine[] $lines
     *
     * @return bool
     */
    private static function save(BusinessDocument &$doc, array &$lines): bool
    {
        foreach ($lines as $line) {
            if (false === $line->save()) {
                return false;
            }
        }

        return $doc->save();
    }
}
