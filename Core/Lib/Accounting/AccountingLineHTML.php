<?php
/**
 * Copyright (C) 2021 Carlos Garcia Gomez <carlos@facturascripts.com>
 */

namespace FacturaScripts\Core\Lib\Accounting;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Base\Translator;
use FacturaScripts\Core\DataSrc\Impuestos;
use FacturaScripts\Dinamic\Model\Asiento;
use FacturaScripts\Dinamic\Model\Partida;
use FacturaScripts\Dinamic\Model\Subcuenta;

/**
 * Description of SalesLineHTML
 *
 * @author Carlos Garcia Gomez           <carlos@facturascripts.com>
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class AccountingLineHTML
{
    /** @var array */
    protected static $deletedLines = [];

    /** @var int */
    protected static $num = 0;

    /**
     * @param Asiento $model
     * @param Partida[] $lines
     * @param array $formData
     */
    public static function apply(Asiento &$model, array &$lines, array $formData)
    {
        // update or remove lines
        $rmLineId = $formData['action'] === 'rm-line' ? $formData['selectedLine'] : 0;
        foreach ($lines as $key => $value) {
            if ($value->idpartida === (int)$rmLineId || false === isset($formData['codsubcuenta_' . $value->idpartida])) {
                self::$deletedLines[] = $value->idpartida;
                unset($lines[$key]);
                continue;
            }

            self::applyToLine($formData, $lines[$key], (string)$value->idpartida);
        }

        // new lines
        for ($num = 1; $num < 1000; $num++) {
            if (isset($formData['codsubcuenta_n' . $num]) && $rmLineId !== 'n' . $num) {
                $newLine = $model->getNewLine();
                $idNewLine = 'n' . $num;
                self::applyToLine($formData, $newLine, $idNewLine);
                $lines[] = $newLine;
            }
        }

        // Calculate model debit and credit
        static::calculateUnbalance($model, $lines);

        // add new line
        if ($formData['action'] === 'new-line' && !empty($formData['new_subaccount'])) {
            $subcuenta = static::getSubcuenta($formData['new_subaccount'], $model);
            if (false === $subcuenta->exists()) {
                ToolBox::i18nLog()->error('subaccount-not-found', ['%subAccountCode%' => $formData['new_subaccount']]);
                return;
            }

            $newLine = $model->getNewLine();
            $newLine->setAccount($subcuenta);
            $newLine->debe = ($model->debe < $model->haber) ? $model->haber - $model->debe : 0.00;
            $newLine->haber = ($model->debe > $model->haber) ? $model->debe - $model->haber : 0.00;
            $lines[] = $newLine;

            static::calculateUnbalance($model, $lines);
        }
    }

    /**
     * Returns the list of deleted lines.
     *
     * @return array
     */
    public static function getDeletedLines(): array
    {
        return self::$deletedLines;
    }

    /**
     * Render the lines of the accounting entry.
     *
     * @param Partida[] $lines
     * @param Asiento $model
     *
     * @return string
     */
    public static function render(array $lines, Asiento $model): string
    {
        $html = '';
        foreach ($lines as $line) {
            $html .= static::renderLine($line, $model);
        }

        return empty($html) ?
            '<div class="alert alert-warning border-top mb-0">' . ToolBox::i18n()->trans('new-acc-entry-line-p') . '</div>' :
            $html;
    }

    /**
     * Render one of the lines of the accounting entry
     *
     * @param Partida $line
     * @param Asiento $model
     *
     * @return string
     */
    public static function renderLine(Partida $line, Asiento $model): string
    {
        static::$num++;
        $i18n = new Translator();
        $idlinea = $line->idpartida ?? 'n' . static::$num;
        $cssClass = static::$num % 2 == 0 ? 'bg-white border-top' : 'bg-light border-top';
        return '<div class="' . $cssClass . ' pl-2 pr-2">'
            . '<div class="form-row">'
            . static::subcuenta($i18n, $line, $model)
            . static::debe($i18n, $line, $model)
            . static::haber($i18n, $line, $model)
            . static::renderExpandButton($i18n, $idlinea, $model)
            . '</div>'
            . '<div class="form-row collapse" id="collapse_' . $idlinea . '">'
            . static::contrapartida($i18n, $line, $model)
            . static::iva($i18n, $line, $model)
            . static::recargo($i18n, $line, $model)
            . static::baseimponible($i18n, $line, $model)
            . static::cifnif($i18n, $line, $model)
            . '</div>'
            . '</div>';
    }

    /**
     * @param array $formData
     * @param Partida $line
     * @param string $id
     */
    protected static function applyToLine(array &$formData, Partida &$line, string $id)
    {
        $line->baseimponible = (float)($formData['baseimponible_' . $id] ?? '0');
        $line->cifnif = $formData['cifnif_' . $id] ?? '';
        $line->codcontrapartida = $formData['codcontrapartida_' . $id] ?? '';
        $line->codsubcuenta = $formData['codsubcuenta_' . $id] ?? '';
        $line->debe = (float)($formData['debe_' . $id] ?? '0');
        $line->haber = (float)($formData['haber_' . $id] ?? '0');
        $line->iva = (float)($formData['iva_' . $id] ?? '0');
        $line->orden = (int)($formData['orden_' . $id] ?? '0');
        $line->recargo = (float)($formData['recargo_' . $id] ?? '0');
    }

    /**
     * Amount base for apply tax.
     *
     * @param Translator $i18n
     * @param Partida $line
     * @param Asiento $model
     *
     * @return string
     */
    protected static function baseimponible(Translator $i18n, Partida $line, Asiento $model): string
    {
        $idlinea = $line->idpartida ?? 'n' . static::$num;
        $attributes = $model->editable ? 'name="baseimponible_' . $idlinea . '"' : 'disabled';
        return '<div class="col-sm pb-2">' . $i18n->trans('tax-base')
            . '<input type="number" ' . $attributes . ' value="' . $line->baseimponible . '" class="form-control" step="any" autocomplete="off">'
            . '</div>';
    }

    /**
     * @param Asiento $model
     * @param Partida[] $lines
     */
    private static function calculateUnbalance(Asiento &$model, array $lines)
    {
        $model->debe = 0.0;
        $model->haber = 0.0;
        foreach ($lines as $line) {
            $model->debe += $line->debe;
            $model->haber += $line->haber;
        }
        $model->importe = max([$model->debe, $model->haber]);
    }

    /**
     * @param Translator $i18n
     * @param Partida $line
     * @param Asiento $model
     *
     * @return string
     */
    protected static function cifnif(Translator $i18n, Partida $line, Asiento $model): string
    {
        $idlinea = $line->idpartida ?? 'n' . static::$num;
        $attributes = $model->editable ? 'name="cifnif_' . $idlinea . '"' : 'disabled';
        return '<div class="col-sm pb-2">' . $i18n->trans('cifnif')
            . '<input type="text" ' . $attributes . ' value="' . $line->cifnif . '" class="form-control" maxlength="30" autocomplete="off"/>'
            . '</div>';
    }

    /**
     * @param Translator $i18n
     * @param Partida $line
     * @param Asiento $model
     *
     * @return string
     */
    protected static function contrapartida(Translator $i18n, Partida $line, Asiento $model): string
    {
        $idlinea = $line->idpartida ?? 'n' . static::$num;
        $attributes = $model->editable
            ? 'name="codcontrapartida_' . $idlinea . '" onkeyup="return recalculateLine(\'recalculate\', \'' . $idlinea . '\');"'
            : 'disabled';

        return '<div class="col-sm-6 col-md-4 col-lg-2 pb-2">' . $i18n->trans('counterpart')
            . '<input type="text" ' . $attributes . ' value="' . $line->codcontrapartida . '" class="form-control" maxlength="15" autocomplete="off"/>'
            . '</div>';
    }

    /**
     * @param Translator $i18n
     * @param Partida $line
     * @param Asiento $model
     *
     * @return string
     */
    protected static function debe(Translator $i18n, Partida $line, Asiento $model): string
    {
        $idlinea = $line->idpartida ?? 'n' . static::$num;
        $attributes = $model->editable
            ? 'name="debe_' . $idlinea . '" step="1" onkeyup="return recalculateLine(\'recalculate\', \'' . $idlinea . '\');"'
            : 'disabled';

        return '<div class="col-sm-2 col-lg-1 pb-2 small">' . $i18n->trans('debit')
            . '<input type="number" class="form-control line-debit" ' . $attributes . ' value="' . $line->debe . '"/>'
            . '</div>';
    }

    /**
     * @param Translator $i18n
     * @param Subcuenta $subcuenta
     *
     * @return string
     */
    protected static function descripcion(Translator $i18n, Subcuenta $subcuenta): string
    {
        return '<div class="col-sm-6 col-md pb-2 small">' . $i18n->trans('description')
            . '<div class="input-group">'
            . '<input type="text" class="form-control" value="' . $subcuenta->descripcion . '" tabindex="-1" readonly>'
            . '<div class="input-group-append"><a href="' . $subcuenta->url() . '" target="_blank" class="btn btn-outline-primary">'
            . '<i class="far fa-eye"></i></a></div>'
            . '</div>'
            . '</div>';
    }

    /**
     * @param string $code
     * @param Asiento $model
     *
     * @return Subcuenta
     */
    protected static function getSubcuenta(string $code, Asiento $model): Subcuenta
    {
        $subcuenta = new Subcuenta();
        $where = [
            new DataBaseWhere('codejercicio', $model->codejercicio),
            new DataBaseWhere('codsubcuenta', $subcuenta->transformCodsubcuenta($code))
        ];
        $subcuenta->loadFromCode('', $where);
        return $subcuenta;
    }

    /**
     * @param Translator $i18n
     * @param Partida $line
     * @param Asiento $model
     *
     * @return string
     */
    protected static function haber(Translator $i18n, Partida $line, Asiento $model): string
    {
        $idlinea = $line->idpartida ?? 'n' . static::$num;
        $attributes = $model->editable
            ? 'name="haber_' . $idlinea . '" step="1" onkeyup="return recalculateLine(\'recalculate\', \'' . $idlinea . '\');"'
            : 'disabled';

        return '<div class="col-sm-2 pb-2 small">' . $i18n->trans('credit')
            . '<input type="number" class="form-control" ' . $attributes . ' value="' . $line->haber . '"/>'
            . '</div>';
    }

    /**
     * @param Translator $i18n
     * @param Partida $line
     * @param Asiento $model
     *
     * @return string
     */
    protected static function iva(Translator $i18n, Partida $line, Asiento $model): string
    {
        $options = ['<option value="">------</option>'];
        foreach (Impuestos::all() as $row) {
            $selected = ($row->iva == $line->iva) ? ' selected' : '';
            $options[] = '<option value="' . $row->iva . '"' . $selected . '>' . $row->descripcion . '</option>';
        }

        $idlinea = $line->idpartida ?? 'n' . static::$num;
        $attributes = $model->editable ? 'name="iva_' . $idlinea . '"' : 'disabled';
        return '<div class="col-sm pb-2">' . $i18n->trans('vat')
            . '<select ' . $attributes . ' class="form-control">' . implode('', $options) . '</select>'
            . '</div>';
    }

    /**
     * @param Translator $i18n
     * @param Partida $line
     * @param Asiento $model
     *
     * @return string
     */
    protected static function recargo(Translator $i18n, Partida $line, Asiento $model): string
    {
        $idlinea = $line->idpartida ?? 'n' . static::$num;
        $attributes = $model->editable ? 'name="recargo_' . $idlinea . '"' : 'disabled';
        return '<div class="col-sm pb-2">' . $i18n->trans('surcharge')
            . '<input type="number" ' . $attributes . ' value="' . $line->recargo . '" decimal="2" class="form-control" step="any" autocomplete="off">'
            . '</div>';
    }

    /**
     * @param Translator $i18n
     * @param string $idlinea
     * @param Asiento $model
     *
     * @return string
     */
    protected static function renderExpandButton(Translator $i18n, string $idlinea, Asiento $model): string
    {
        if ($model->editable) {
            return '<div class="col-sm-1"><div class="text-right small">' . $i18n->trans('drag') . '<i class="fas fa-arrows-alt-v ml-2"></i></div>'
                . '<a href="#collapse_' . $idlinea . '" data-toggle="collapse" class="btn btn-block btn-outline-secondary mb-1">'
                . $i18n->trans('more') . '</a></div>';
        }

        return '<div class="col-sm-1"><a href="#collapse_' . $idlinea . '" data-toggle="collapse" class="btn btn-block btn-outline-secondary mb-1">'
            . $i18n->trans('more') . '</a></div>';
    }

    /**
     * @param Translator $i18n
     * @param Subcuenta $subcuenta
     *
     * @return string
     */
    protected static function saldo(Translator $i18n, Subcuenta $subcuenta): string
    {
        return '<div class="col-sm-2 pb-2 small">' . $i18n->trans('balance')
            . '<input type="text" class="form-control" value="' . ToolBox::numbers()::format($subcuenta->saldo) . '" tabindex="-1" readonly>'
            . '</div>';
    }

    /**
     * @param Translator $i18n
     * @param Partida $line
     * @param Asiento $model
     *
     * @return string
     */
    protected static function subcuenta(Translator $i18n, Partida $line, Asiento $model): string
    {
        $idlinea = $line->idpartida ?? 'n' . static::$num;
        $subcuenta = static::getSubcuenta($line->codsubcuenta, $model);
        if (false === $model->editable) {
            return '<div class="col-sm-6 col-md-4 col-lg-2 pb-2 small">' . $i18n->trans('subaccount')
                . '<input type="text" value="' . $line->codsubcuenta . '" class="form-control" tabindex="-1" readonly>'
                . '</div>'
                . static::descripcion($i18n, $subcuenta)
                . static::saldo($i18n, $subcuenta);
        }

        return '<div class="col-sm-6 col-md-4 col-lg-2 pb-2 small">'
            . '<input type="hidden" name="orden_' . $idlinea . '" value="' . $line->orden . '"/>'
            . '<i class="fas fa-arrows-alt-v"></i> ' . $i18n->trans('subaccount')
            . '<div class="input-group">'
            . '<input type="text" name="codsubcuenta_' . $idlinea . '" value="' . $line->codsubcuenta . '" class="form-control" tabindex="-1" readonly>'
            . '<div class="input-group-append"><button class="btn btn-outline-danger" type="button"'
            . ' onclick="return accEntryFormAction(\'rm-line\', \'' . $idlinea . '\');"><i class="fas fa-trash-alt"></i></button></div>'
            . '</div>'
            . '</div>'
            . static::descripcion($i18n, $subcuenta)
            . static::saldo($i18n, $subcuenta);
    }
}
