<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
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
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\DownloadTools;
use ZipArchive;

/**
 * Description of Updater
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class Updater extends Controller
{

    const UPDATE_CORE_URL = 'https://s3.eu-west-2.amazonaws.com/facturascripts/2018.zip';

    /**
     *
     * @var array
     */
    public $updaterItems = [];

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'admin';
        $pageData['title'] = 'updater';
        $pageData['icon'] = 'fa-cloud-download';

        return $pageData;
    }

    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        /// Folders writables?
        $folders = $this->notWritablefolders();
        if (!empty($folders)) {
            $this->miniLog->alert($this->i18n->trans('folder-not-writable'));
            foreach ($folders as $folder) {
                $this->miniLog->alert($folder);
            }
            return;
        }

        $this->updaterItems[] = [
            'id' => 'CORE',
            'description' => 'Core component',
            'downloaded' => file_exists(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip')
        ];

        $action = $this->request->get('action');
        switch ($action) {
            case 'download':
                $this->download();
                break;

            case 'update':
                $this->update();
                break;
        }
    }

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    private function download()
    {
        if (file_exists(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip')) {
            unlink(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip');
        }

        $downloader = new DownloadTools();
        if ($downloader->download(self::UPDATE_CORE_URL, FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip')) {
            $this->miniLog->info('download-completed');
        }
    }

    private function foldersFrom($baseDir)
    {
        $directories = [];
        foreach (scandir($baseDir) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $dir = $baseDir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($dir)) {
                $directories[] = $dir;
                $directories = array_merge($directories, $this->foldersFrom($dir));
            }
        }

        return $directories;
    }

    private function notWritablefolders()
    {
        $notwritable = [];
        foreach ($this->foldersFrom(FS_FOLDER) as $dir) {
            if (!is_writable($dir)) {
                $notwritable[] = $dir;
            }
        }

        return $notwritable;
    }

    private function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir))) {
            if ($file != '.' || $file != '..') {
                continue;
            }

            if (is_dir($src . '/' . $file)) {
                $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
        closedir($dir);
    }

    private function update()
    {
        if (file_exists(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip')) {
            $this->extractCore();
        }
    }

    private function extractCore()
    {
        $zip = new ZipArchive();
        $zipStatus = $zip->open(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip', ZipArchive::CHECKCONS);
        if ($zipStatus !== true) {
            $this->miniLog->critical('ZIP ERROR: ' . $zipStatus);
            return false;
        }

        $zip->extractTo('.');
        $zip->close();

        foreach (['Core', 'node_modules', 'vendor'] as $folder) {
            $this->delTree(FS_FOLDER . DIRECTORY_SEPARATOR . $folder);
            $this->recurseCopy(FS_FOLDER . DIRECTORY_SEPARATOR . 'facturascripts' . DIRECTORY_SEPARATOR . $folder, FS_FOLDER . DIRECTORY_SEPARATOR . $folder);
        }
        return true;
    }
}
