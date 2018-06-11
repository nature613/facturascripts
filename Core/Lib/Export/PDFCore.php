<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018 Carlos Garcia Gomez <carlos@facturascripts.com>
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
namespace FacturaScripts\Core\Lib\Export;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base;
use FacturaScripts\Core\Model;
use FacturaScripts\Core\Model\Base\BusinessDocument;

/**
 * Description of PDFCore
 *
 * @author carlos
 */
class PDFCore
{

    /**
     * X position to start writting.
     */
    const CONTENT_X = 30;

    /**
     * Font size.
     */
    const FONT_SIZE = 9;

    /**
     * Y position to start footer
     */
    const FOOTER_Y = 10;

    /**
     *
     * @var Base\DivisaTools
     */
    protected $divisaTools;

    /**
     * Translator object
     *
     * @var Base\Translator
     */
    protected $i18n;

    /**
     *
     * @var bool
     */
    protected $insertedHeader;

    /**
     * Class with number tools (to format numbers)
     *
     * @var Base\NumberTools
     */
    protected $numberTools;

    /**
     * PDF object.
     *
     * @var \Cezpdf
     */
    protected $pdf;

    /**
     * PDF table width.
     *
     * @var int|float
     */
    protected $tableWidth;

    /**
     * PDFExport constructor.
     */
    public function __construct()
    {
        $this->divisaTools = new Base\DivisaTools();
        $this->i18n = new Base\Translator();
        $this->insertedHeader = false;
        $this->numberTools = new Base\NumberTools();
        $this->tableWidth = 0.0;
    }

    /**
     * 
     * @param string $code
     */
    protected function getCountryName($code): string
    {
        if (empty($code)) {
            return '';
        }

        $country = new Model\Pais();
        return $country->loadFromCode($code) ? $country->nombre : '';
    }

    /**
     * Returns the table data
     *
     * @param array $cursor
     * @param array $tableCols
     * @param array $tableOptions
     *
     * @return array
     */
    protected function getTableData($cursor, $tableCols, $tableOptions)
    {
        $tableData = [];

        /// Get the data
        foreach ($cursor as $key => $row) {
            foreach ($tableCols as $col) {
                if (!isset($row->{$col})) {
                    $tableData[$key][$col] = '';
                    continue;
                }

                $value = $row->{$col};
                if ($tableOptions['cols'][$col]['col-type'] === 'number') {
                    $value = $this->numberTools->format($value);
                } elseif ($tableOptions['cols'][$col]['col-type'] === 'money') {
                    $this->divisaTools->findDivisa($row);
                    $value = $this->divisaTools->format($value, FS_NF0, 'coddivisa');
                } elseif (is_bool($value)) {
                    $value = $this->i18n->trans($value === 1 ? 'yes' : 'no');
                } elseif (null === $value) {
                    $value = '';
                } elseif ($tableOptions['cols'][$col]['col-type'] === 'text') {
                    $value = Base\Utils::fixHtml($value);
                }

                $tableData[$key][$col] = $value;
            }
        }

        return $tableData;
    }

    /**
     * Generate the body of the page with the model data.
     *
     * @param mixed $model
     */
    protected function insertBusinessDocBody($model)
    {
        $headers = [
            'reference' => $this->i18n->trans('reference+description'),
            'quantity' => $this->i18n->trans('quantity'),
            'price' => $this->i18n->trans('price'),
            'discount' => $this->i18n->trans('discount'),
            'tax' => $this->i18n->trans('tax'),
            'total' => $this->i18n->trans('total'),
        ];
        $tableData = [];
        foreach ($model->getlines() as $line) {
            $tableData[] = [
                'reference' => Base\Utils::fixHtml($line->referencia . " - " . $line->descripcion),
                'quantity' => $this->numberTools::format($line->cantidad),
                'price' => $this->numberTools::format($line->pvpunitario),
                'discount' => $this->numberTools::format($line->dtopor),
                'tax' => $this->numberTools::format($line->iva),
                'total' => $this->numberTools::format($line->pvptotal),
            ];
        }

        $tableOptions = [
            'cols' => [
                'quantity' => ['justification' => 'right'],
                'price' => ['justification' => 'right'],
                'discount' => ['justification' => 'right'],
                'tax' => ['justification' => 'right'],
                'total' => ['justification' => 'right'],
            ],
            'shadeCol' => [0.95, 0.95, 0.95],
            'shadeHeadingCol' => [0.95, 0.95, 0.95],
            'width' => $this->tableWidth
        ];
        $this->removeEmptyCols($tableData, $headers);
        $this->pdf->ezTable($tableData, $headers, '', $tableOptions);
    }

    /**
     * Inserts the footer of the page with the model data.
     *
     * @param BusinessDocument $model
     */
    protected function insertBusinessDocFooter($model)
    {
        if (!empty($model->observaciones)) {
            $this->newPage();
            $this->pdf->ezText($this->i18n->trans('notes') . "\n", self::FONT_SIZE);
            $this->newLine();
            $this->pdf->ezText(Base\Utils::fixHtml($model->observaciones) . "\n", self::FONT_SIZE);
        }

        $this->newPage();
        $headers = [
            'currency' => $this->i18n->trans('currency'),
            'net' => $this->i18n->trans('net'),
            'taxes' => $this->i18n->trans('taxes'),
            'total' => $this->i18n->trans('total'),
        ];
        $rows = [
            [
                'currency' => $model->coddivisa,
                'net' => $this->numberTools::format($model->neto, FS_NF0),
                'taxes' => $this->numberTools::format($model->totaliva, FS_NF0),
                'total' => $this->numberTools::format($model->total, FS_NF0),
            ]
        ];
        $tableOptions = [
            'cols' => [
                'net' => ['justification' => 'right'],
                'taxes' => ['justification' => 'right'],
                'total' => ['justification' => 'right'],
            ],
            'shadeCol' => [0.95, 0.95, 0.95],
            'shadeHeadingCol' => [0.95, 0.95, 0.95],
            'width' => $this->tableWidth
        ];
        $this->pdf->ezTable($rows, $headers, '', $tableOptions);
    }

    /**
     * Inserts the header of the page with the model data.
     *
     * @param BusinessDocument $model
     */
    protected function insertBusinessDocHeader($model)
    {
        $headerData = [
            'title' => $this->i18n->trans('delivery-note'),
            'subject' => $this->i18n->trans('customer'),
            'fieldName' => 'nombrecliente',
        ];

        if (isset($model->codproveedor)) {
            $headerData['subject'] = $this->i18n->trans('supplier');
            $headerData['fieldName'] = 'nombre';
        }

        switch ($model->modelClassName()) {
            case 'FacturaProveedor':
            case 'FacturaCliente':
                $headerData['title'] = $this->i18n->trans('invoice');
                break;

            case 'PedidoProveedor':
            case 'PedidoCliente':
                $headerData['title'] = $this->i18n->trans('order');
                break;

            case 'PresupuestoProveedor':
            case 'PresupuestoCliente':
                $headerData['title'] = $this->i18n->trans('estimation');
                break;
        }

        $this->pdf->ezText("\n" . $headerData['title'] . ' ' . $model->codigo . "\n", self::FONT_SIZE + 6);
        $this->newLine();

        $tableData = [
            ['key' => $this->i18n->trans('date'), 'value' => $model->fecha,],
            ['key' => $headerData['subject'], 'value' => $model->{$headerData['fieldName']},],
            ['key' => $this->i18n->trans('cifnif'), 'value' => $model->cifnif,],
        ];

        if (isset($model->direccion)) {
            $tableData[] = ['key' => $this->i18n->trans('address'), 'value' => $model->direccion,];
            $tableData[] = ['key' => $this->i18n->trans('zip-code'), 'value' => $model->codpostal,];
            $tableData[] = ['key' => $this->i18n->trans('city'), 'value' => $model->ciudad,];
            $tableData[] = ['key' => $this->i18n->trans('province'), 'value' => $model->provincia,];
            $tableData[] = ['key' => $this->i18n->trans('country'), 'value' => $this->getCountryName($model->codpais),];
        }

        $tableOptions = [
            'width' => $this->tableWidth,
            'showHeadings' => 0,
            'shaded' => 0,
            'lineCol' => [1, 1, 1],
            'cols' => [],
        ];
        $this->insertParalellTable($tableData, '', $tableOptions);
        $this->pdf->ezText('');
    }

    /**
     * Insert footer details.
     */
    protected function insertFooter()
    {
        $now = $this->i18n->trans('generated-at', ['%when%' => date('d-m-Y H:i')]);
        $this->pdf->addText($this->tableWidth + self::CONTENT_X, self::FOOTER_Y, self::FONT_SIZE, $now, 0, 'right');
    }

    /**
     * Insert header details.
     * 
     * @param int $idempresa
     */
    protected function insertHeader($idempresa = null)
    {
        if ($this->insertedHeader) {
            return;
        }

        $this->insertedHeader = true;
        $code = $idempresa ?? AppSettings::get('default', 'idempresa', '');
        $company = new Model\Empresa();
        if ($company->loadFromCode($code)) {
            $this->pdf->ezText($company->nombre, self::FONT_SIZE + 9);
            $address = $company->direccion;
            $address .= empty($company->codpostal) ? '' : ' - CP: ' . $company->codpostal . ' - ';
            $address .= empty($company->ciudad) ? '' : $company->ciudad;
            $address .= empty($company->provincia) ? '' : ' (' . $company->provincia . ') ' . $this->getCountryName($company->codpais);
            $contactData = empty($company->telefono1) ? '' : $company->telefono1 . ' ';
            $contactData .= empty($company->telefono2) ? '' : $company->telefono2 . ' ';
            $contactData .= empty($company->email) ? '' : $company->email . ' ';
            $contactData .= empty($company->web) ? '' : $company->web;

            $lineText = $company->cifnif . ' - ' . $address . ' - ' . $contactData;
            $this->pdf->ezText($lineText . "\n", self::FONT_SIZE);
        }
    }

    protected function insertParalellTable($tableData, $title = '', $options = [])
    {
        $headers = ['data1' => 'data1', 'data2' => 'data2'];
        $rows = $this->paralellTableData($tableData);
        $this->pdf->ezTable($rows, $headers, $title, $options);
    }

    /**
     * Adds a new line to the PDF.
     */
    protected function newLine()
    {
        $posY = $this->pdf->y + 5;
        $this->pdf->line(self::CONTENT_X, $posY, $this->tableWidth + self::CONTENT_X, $posY);
    }

    /**
     * Adds a description of long titles to the PDF.
     *
     * @param array $titles
     */
    protected function newLongTitles(&$titles)
    {
        $txt = '';
        foreach ($titles as $key => $value) {
            if ($txt !== '') {
                $txt .= ', ';
            }

            $txt .= '*' . $key . ' = ' . $value;
        }

        if ($txt !== '') {
            $this->pdf->ezText($txt);
        }
    }

    /**
     * Adds a new page.
     *
     * @param string $orientation
     */
    protected function newPage($orientation = 'portrait')
    {
        if ($this->pdf === null) {
            $this->pdf = new \Cezpdf('a4', $orientation);
            $this->pdf->addInfo('Creator', 'FacturaScripts');
            $this->pdf->addInfo('Producer', 'FacturaScripts');
            $this->pdf->tempPath = FS_FOLDER . '/MyFiles/Cache';

            $this->tableWidth = $this->pdf->ez['pageWidth'] - self::CONTENT_X * 2;

            $this->pdf->ezStartPageNumbers(self::CONTENT_X, self::FOOTER_Y, self::FONT_SIZE, 'left', '{PAGENUM} / {TOTALPAGENUM}');
        } elseif ($this->pdf->y < 200) {
            $this->pdf->ezNewPage();
            $this->insertedHeader = false;
        } else {
            $this->pdf->ezText("\n");
        }
    }

    /**
     * Returns a new table with 2 columns. Each column with colName1: colName2
     *
     * @param array  $table
     * @param string $colName1
     * @param string $colName2
     * @param string $finalColName1
     * @param string $finalColName2
     *
     * @return array
     */
    protected function paralellTableData($table, $colName1 = 'key', $colName2 = 'value', $finalColName1 = 'data1', $finalColName2 = 'data2')
    {
        $tableData = [];
        $key = 0;
        foreach ($table as $value) {
            $txt = '<b>' . $value[$colName1] . '</b>: ' . $value[$colName2];

            if (isset($tableData[$key])) {
                $tableData[$key][$finalColName2] = $txt;
                ++$key;
                continue;
            }

            $tableData[$key][$finalColName1] = $txt;
            $tableData[$key][$finalColName2] = '';
        }

        return $tableData;
    }

    /**
     * Remove the empty columns to save space.
     *
     * @param $tableData
     * @param $tableColsTitle
     */
    protected function removeEmptyCols(&$tableData, &$tableColsTitle)
    {
        foreach (array_keys($tableColsTitle) as $key) {
            $remove = true;
            foreach ($tableData as $row) {
                if (!empty($row[$key])) {
                    $remove = false;
                    break;
                }
            }

            if ($remove) {
                unset($tableColsTitle[$key]);
            }
        }
    }

    /**
     * Adds to $longTitles, and replace all long titles from $titles
     *
     * @param array $longTitles
     * @param array $titles
     */
    protected function removeLongTitles(&$longTitles, &$titles)
    {
        $num = 1;
        foreach ($titles as $key => $value) {
            if (mb_strlen($value) > 12) {
                $longTitles[$num] = $value;
                $titles[$key] = '*' . $num;
                ++$num;
            }
        }
    }

    /**
     * Set the table content.
     *
     * @param array $columns
     * @param array $tableCols
     * @param array $tableColsTitle
     * @param array $tableOptions
     */
    protected function setTableColumns(&$columns, &$tableCols, &$tableColsTitle, &$tableOptions)
    {
        foreach ($columns as $col) {
            if (is_string($col)) {
                $tableCols[$col] = $col;
                $tableColsTitle[$col] = $col;
                continue;
            }

            if (isset($col->columns)) {
                $this->setTableColumns($col->columns, $tableCols, $tableColsTitle, $tableOptions);
                continue;
            }

            if (isset($col->display) && $col->display !== 'none' && isset($col->widget->fieldName)) {
                $tableCols[$col->widget->fieldName] = $col->widget->fieldName;
                $tableColsTitle[$col->widget->fieldName] = $this->i18n->trans($col->title);
                $tableOptions['cols'][$col->widget->fieldName] = [
                    'justification' => $col->display,
                    'col-type' => $col->widget->type,
                ];
            }
        }
    }

    /**
     * Set table data as key => value.
     *
     * @param array $tableColsTitle
     * @param array $tableDataAux
     * @param mixed $model
     */
    protected function setTablaData(&$tableColsTitle, &$tableDataAux, $model)
    {
        foreach ($tableColsTitle as $key => $colTitle) {
            $value = null;
            if (isset($model->{$key})) {
                $value = $model->{$key};
            }

            if (\is_bool($value)) {
                $txt = $this->i18n->trans($value ? 'yes' : 'no');
                $tableDataAux[] = ['key' => $colTitle, 'value' => $txt];
            } elseif ($value !== null && $value !== '') {
                $value = \is_string($value) ? Base\Utils::fixHtml($value) : $value;
                $tableDataAux[] = ['key' => $colTitle, 'value' => $value];
            }
        }
    }
}
