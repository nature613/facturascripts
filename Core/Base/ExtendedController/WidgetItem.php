<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Base\ExtendedController;

/**
 * This WidgetItem class modelises the common data and method of a WidgetItem element.
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
abstract class WidgetItem implements VisualItemInterface
{

    /**
     * Field name with the data that the widget displays
     *
     * @var string
     */
    public $fieldName;

    /**
     * Type of widget displayed
     *
     * @var string
     */
    public $type;

    /**
     * Additional information for the user
     *
     * @var string
     */
    public $hint;

    /**
     * Indicates that the field is read only
     *
     * @var bool
     */
    public $readOnly;

    /**
     * Indicates that the field is mandatory and it must have a value
     *
     * @var bool
     */
    public $required;

    /**
     * Icon used as a value or to accompany the widget
     *
     * @var string
     */
    public $icon;

    /**
     * Destination controller to go to when the displayed data is clicked
     *
     * @var string
     */
    public $onClick;

    /**
     * Visual options to configure the widget
     *
     * @var array
     */
    public $options;

    /**
     * Generates the html code to display the model data for List controller
     *
     * @param string $value
     */
    abstract public function getListHTML($value);

    /**
     * Generates the html code to display the model data for Edit controller
     *
     * @param string $value
     */
    abstract public function getEditHTML($value);

    /**
     * Class dynamic constructor. It creates a widget of the given type
     *
     * @param string $type
     *
     * @return WidgetItem
     */
    private static function widgetItemFromType($type)
    {
        switch ($type) {
            case 'number':
                return new WidgetItemNumber();

            case 'money':
                return new WidgetItemMoney();

            case 'checkbox':
                return new WidgetItemCheckBox();

            case 'datepicker':
                return new WidgetItemDateTime();

            case 'select':
                return new WidgetItemSelect();

            case 'radio':
                return new WidgetItemRadio();

            case 'color':
                return new WidgetItemColor();

            default:
                return new WidgetItemText($type);
        }
    }

    /**
     * Creates and loads the attributes structure from a XML file
     *
     * @param \SimpleXMLElement $column
     *
     * @return WidgetItem
     */
    public static function newFromXML($column)
    {
        $widgetAtributes = $column->widget->attributes();
        $type = (string) $widgetAtributes->type;
        $widget = self::widgetItemFromType($type);
        $widget->loadFromXML($column);
        return $widget;
    }

    /**
     * Creates and loads the attributes structure from the database
     *
     * @param array $column
     *
     * @return WidgetItem
     */
    public static function newFromJSON($column)
    {
        $type = (string) $column['widget']['type'];
        $widget = self::widgetItemFromType($type);
        $widget->loadFromJSON($column);
        return $widget;
    }

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->fieldName = '';
        $this->hint = '';
        $this->readOnly = false;
        $this->required = false;
        $this->icon = null;
        $this->onClick = '';
        $this->options = [];
    }

    /**
     * Array with list of personalization functions of the column
     */
    public function columnFunction()
    {
        return ['ColumnClass', 'ColumnHint', 'ColumnRequired', 'ColumnDescription'];
    }

    /**
     * Generate the html code to visualize the visual element header
     *
     * @param string $value
     *
     * @return string
     */
    public function getHeaderHTML($value)
    {
        return '<span title="' . $value . '"></span>';
    }

    /**
     * Loads the attribute dictionary for a widget's group of options or values
     *
     * @param array $property
     * @param \SimpleXMLElement[] $group
     */
    protected function getAttributesGroup(&$property, $group)
    {
        $property = [];
        foreach ($group as $item) {
            $values = [];
            foreach ($item->attributes() as $attributeKey => $attributeValue) {
                $values[$attributeKey] = (string) $attributeValue;
            }
            $values['value'] = (string) $item;
            $property[] = $values;
            unset($values);
        }
    }

    /**
     * Loads the attributes structure from a XML file
     *
     * @param \SimpleXMLElement $column
     */
    public function loadFromXML($column)
    {
        $widgetAtributes = $column->widget->attributes();
        $this->fieldName = (string) $widgetAtributes->fieldname;
        $this->hint = (string) $widgetAtributes->hint;
        $this->readOnly = (bool) $widgetAtributes->readonly;
        $this->required = (bool) $widgetAtributes->required;
        $this->icon = (string) $widgetAtributes->icon;
        $this->onClick = (string) $widgetAtributes->onclick;

        $this->getAttributesGroup($this->options, $column->widget->option);
    }

    /**
     * Loads the attributes structure from the database
     *
     * @param \SimpleXMLElement[] $column
     */
    public function loadFromJSON($column)
    {
        $this->fieldName = (string) $column['widget']['fieldName'];
        $this->hint = (string) $column['widget']['hint'];
        $this->readOnly = (bool) $column['widget']['readOnly'];
        $this->required = (bool) $column['widget']['required'];
        $this->icon = (string) $column['widget']['icon'];
        $this->onClick = (string) $column['widget']['onClick'];
        $this->options = (array) $column['widget']['options'];
    }

    /**
     * Indicates if the conditions to apply an Option Text are met
     *
     * @param string $optionValue
     * @param string $valueItem
     *
     * @return bool
     */
    private function canApplyOptions($optionValue, $valueItem)
    {
        switch ($optionValue[0]) {
            case '<':
                $optionValue = substr($optionValue, 1) ? : '';
                $result = ((float) $valueItem < (float) $optionValue);
                break;

            case '>':
                $optionValue = substr($optionValue, 1) ? : '';
                $result = ((float) $valueItem > (float) $optionValue);
                break;

            default:
                $result = ($optionValue === $valueItem);
                break;
        }
        return $result;
    }

    /**
     * Generates the CSS code for the widget style from the options
     *
     * @param string $valueItem
     *
     * @return string
     */
    protected function getTextOptionsHTML($valueItem)
    {
        $html = '';
        foreach ($this->options as $option) {
            if ($this->canApplyOptions($option['value'], $valueItem)) {
                $html = ' style="';
                foreach ($option as $key => $value) {
                    if ($key !== 'value') {
                        $html .= $key . ':' . $value . '; ';
                    }
                }
                $html .= '"';
                break;
            }
        }

        return $html;
    }

    /**
     * Returns the HTML code to display a popover with the given text
     *
     * @param string $hint
     *
     * @return string
     */
    public function getHintHTML($hint)
    {
        return empty($hint) ? '' : ' data-toggle="popover" data-placement="auto" data-trigger="hover" data-content="'
            . $hint . '" ';
    }

    /**
     * Generates the HTML code to display an icon on the left side of the data
     *
     * @return string
     */
    protected function getIconHTML()
    {
        if (empty($this->icon)) {
            return '';
        }

        $html = '<div class="input-group"><span class="input-group-addon">';
        if (strpos($this->icon, 'fa-') === 0) {
            return $html . '<i class="fa ' . $this->icon . '" aria-hidden="true"></i></span>';
        }

        return $html . '<i aria-hidden="true">' . $this->icon . '</i></span>';
    }

    /**
     * Generates the HTML code for special attributes like:
     * hint
     * read only
     * mandatory value
     *
     * @return string
     */
    protected function specialAttributes()
    {
        $hint = $this->getHintHTML($this->hint);
        $readOnly = empty($this->readOnly) ? '' : ' readonly';
        $required = empty($this->required) ? '' : ' required';

        return $hint . $readOnly . $required;
    }

    /**
     * Returns the HTML code for the list of non special controls
     *
     * @param string $value
     * @param string $text
     *
     * @return string
     */
    protected function standardListHTMLWidget($value, $text = '')
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (empty($text)) {
            $text = $value;
        }

        $style = $this->getTextOptionsHTML($value);
        $html = empty($this->onClick) ? '<span' . $style . '>' . $text . '</span>' : '<a href="?page=' . $this->onClick
            . '&code=' . $value . '" ' . $style . '>' . $text . '</a>';

        return $html;
    }

    /**
     * Returns the HTML code to edit non special controls
     *
     * @param string $value
     * @param string $specialAttributes
     * @param string $extraClass
     * @param string $type
     *
     * @return string
     */
    protected function standardEditHTMLWidget($value, $specialAttributes, $extraClass = '', $type = '')
    {
        $fieldName = '"' . $this->fieldName . '"';
        $icon = $this->getIconHTML();

        if (empty($type)) {
            $type = $this->type;
        }

        $html = $icon
            . '<input id=' . $fieldName . ' type="' . $type . '" class="form-control' . $extraClass . '"'
            . 'name=' . $fieldName . ' value="' . $value . '"' . $specialAttributes . ' />';

        if (!empty($this->icon)) {
            $html .= '</div>';
        }

        return $html;
    }
}
