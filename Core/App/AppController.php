<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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

namespace FacturaScripts\Core\App;

use DebugBar\StandardDebugBar;
use Exception;
use FacturaScripts\Core\Base\Controller;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\InvalidArgumentException as TranslationInvalidArgumentException;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use Twig_Loader_Filesystem;
use UnexpectedValueException;

/**
 * Description of App
 *
 * @author Carlos García Gómez
 */
class AppController extends App
{
    /**
     * Controlador cargado.
     * @var Controller
     */
    private $controller;

    /**
     * PHDebugBar.
     * @var StandardDebugBar
     */
    private $debugBar;

    /**
     * AppController constructor.
     *
     * @param string $folder
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws TranslationInvalidArgumentException
     */
    public function __construct($folder = '')
    {
        parent::__construct($folder);
        // Es prescindible, ya que es el mismo valor que por defecto
        // $this->controller = null;
        $this->debugBar = new StandardDebugBar();
    }

    /**
     * Selecciona y ejecuta el controlador pertinente.
     * @throws InvalidArgumentException
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     * @throws UnexpectedValueException
     */
    public function run()
    {
        if (!$this->dataBase->connected()) {
            $this->response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $this->renderHtml('Error/DbError.html');
        } elseif ($this->isIPBanned()) {
            $this->response->setStatusCode(Response::HTTP_FORBIDDEN);
            $this->response->setContent('IP-BANNED');
        } else {
            /// Obtenemos el nombre del controlador a cargar
            $pageName = $this->request->query->get('page', 'AdminHome');
            $this->loadController($pageName);
        }
    }

    /**
     * Carga y procesa el controlador $pageName.
     *
     * @param string $pageName nombre del controlador
     *
     * @throws InvalidArgumentException
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     * @throws UnexpectedValueException
     */
    private function loadController($pageName)
    {
        $controllerName = "FacturaScripts\\Dinamic\\Controller\\{$pageName}";
        $template = 'Error/ControllerNotFound.html';
        $httpStatus = Response::HTTP_NOT_FOUND;

        if (!class_exists($controllerName)) {
            $controllerName = "FacturaScripts\\Core\\Controller\\{$pageName}";
        }

        /// Si hemos encontrado el controlador, lo cargamos
        if (class_exists($controllerName)) {
            $this->miniLog->debug('Loading controller: ' . $controllerName);

            try {
                $this->controller = new $controllerName($this->cache, $this->i18n, $this->miniLog, $pageName);
                $this->controller->run();
                $template = $this->controller->getTemplate();
                $httpStatus = Response::HTTP_OK;
            } catch (Exception $exc) {
                $this->debugBar['exceptions']->addException($exc);
                $httpStatus = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
        }

        $this->response->setStatusCode($httpStatus);
        if ($template) {
            $this->renderHtml($template);
        }
    }

    /**
     * Crea el HTML con la plantilla seleccionada. Aunque los datos no se volcarán
     * hasta ejecutar render()
     *
     * @param string $template archivo html a utilizar
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    private function renderHtml($template)
    {
        /// cargamos el motor de plantillas
        $twigLoader = new Twig_Loader_Filesystem($this->folder . '/Core/View');
        foreach ($this->pluginManager->enabledPlugins() as $pluginName) {
            if (file_exists($this->folder . '/Plugins/' . $pluginName . '/View')) {
                $twigLoader->prependPath($this->folder . '/Plugins/' . $pluginName . '/View');
            }
        }

        /// opciones de twig
        $twigOptions = array('cache' => $this->folder . '/Cache/Twig');

        /// variables para la plantilla HTML
        $templateVars = array(
            'debugBarRender' => false,
            'fsc' => $this->controller,
            'i18n' => $this->i18n,
            'log' => $this->miniLog->read(),
            'sql' => $this->miniLog->read(['sql']),
        );

        if (FS_DEBUG) {
            unset($twigOptions['cache']);
            $twigOptions['debug'] = true;
            $baseUrl = 'vendor/maximebf/debugbar/src/DebugBar/Resources/';
            $templateVars['debugBarRender'] = $this->debugBar->getJavascriptRenderer($baseUrl);

            /// añadimos del log a debugBar
            foreach ($this->miniLog->read(['debug']) as $msg) {
                $this->debugBar['messages']->info($msg['message']);
            }
            $this->debugBar['messages']->info('END');
        }
        $twig = new Twig_Environment($twigLoader, $twigOptions);

        try {
            $this->response->setContent($twig->render($template, $templateVars));
        } catch (Exception $exc) {
            $this->debugBar['exceptions']->addException($exc);
            $this->response->setContent($twig->render('Error/TemplateNotFound.html', $templateVars));
            $this->response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
