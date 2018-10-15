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
namespace FacturaScripts\Core\Lib\Widget;

/**
 * Description of RowButton
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */
class RowButton extends VisualItem
{

    /**
     *
     * @var string
     */
    public $action;

    /**
     *
     * @var string
     */
    public $color;

    /**
     *
     * @var string
     */
    public $icon;

    /**
     *
     * @var string
     */
    public $label;

    /**
     * Indicates the security level of the button
     *
     * @var int
     */
    public $level;

    /**
     *
     * @var string
     */
    public $type;

    /**
     * 
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->action = $data['action'] ?? '';
        $this->color = $data['color'] ?? '';
        $this->icon = $data['icon'] ?? '';
        $this->label = isset($data['label']) ? static::$i18n->trans($data['label']) : '';
        $this->level = isset($data['level']) ? (int) $data['level'] : 0;
        $this->type = $data['type'] ?? 'action';
    }

    /**
     * 
     * @param bool   $small
     * @param string $viewName
     * @param string $jsFunction
     *
     * @return string
     */
    public function render($small = false, $viewName = '', $jsFunction = '')
    {
        if ($this->getLevel() < $this->level) {
            return '';
        }

        $cssClass = $small ? 'btn mr-1 ' : 'btn btn-sm mr-1 ';
        $cssClass .= empty($this->color) ? 'btn-light' : $this->colorToClass($this->color, 'btn-');
        $icon = empty($this->icon) ? '' : '<i class="' . $this->icon . ' fa-fw"></i> ';
        $label = $small ? '' : $this->label;
        $divID = empty($this->id) ? '' : ' id="' . $this->id . '"';

        switch ($this->type) {
            case 'js':
                return '<button type="button"' . $divID . ' class="' . $cssClass . '" onclick="' . $this->action
                    . '" title="' . $this->label . '">' . $icon . $label . '</button>';

            case 'link':
                return '<a ' . $divID . ' class="' . $cssClass . '" href="' . $this->action . '"'
                    . ' title="' . $this->label . '">' . $icon . $label . '</a>';

            case 'modal':
                return '<button type="button"' . $divID . ' class="' . $cssClass . '" data-toggle="modal" data-target="#modal'
                    . $this->action . '" title="' . $this->label . '">' . $icon . $label . '</button>';

            default:
                $onclick = empty($jsFunction) ? 'this.form.action.value=\'' . $this->action . '\';this.form.submit();' :
                    $jsFunction . '(\'' . $viewName . '\',\'' . $this->action . '\');';
                return '<button type="button"' . $divID . ' class="' . $cssClass . '" onclick="' . $onclick
                    . '" title="' . $this->label . '">' . $icon . $label . '</button>';
        }
    }
}
