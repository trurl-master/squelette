<?php

namespace Squelette;

use \App;


trait ResourceSet
{

    public function resRootPath()
    {
        return App::cfg('root') . App::cfg('assets') . 'docs/' . $this::RESOURCE_PATH . '/';
    }

    public function resPath()
    {
        $resid = $this->getResid();

        if ($resid === '') {
            return false;
        }

        return $this->resRootPath() . $resid . '/';
    }

    public function resSrcRootPath()
    {
        return App::cfg('assets') . 'docs/' . $this::RESOURCE_PATH . '/';
    }

    public function resSrcPath()
    {
        $resid = $this->getResid();

        if ($resid === '') {
            return false;
        }

        return $this->resSrcRootPath() . $resid . '/';
    }

    public function updateRes()
    {

        do {

            $new_resid = uniqid();
            // $new_path = App::cfg('root') . App::cfg('assets') . 'docs/' . $this::RESOURCE_PATH . '/' . $new_resid . '/';
            $new_path = self::resRootPath() . $new_resid . '/';

        } while(is_dir($new_path));

        //
        if (empty($this->getResid())) {

            if (mkdir($new_path)) {
                // update DB
                try {
                    $this
                        ->setResid($new_resid)
                        ->save();
                } catch (Exception $e) {
                    $tableMapString = $this::TABLE_MAP;
                    $tableMap = new $tableMapString();
                    $table = $tableMap->getTableMap()->getName();
                    error_log('Resource created but resid not set for ' . $table . ', id = ' . $this->getId());

                    if (!rmdir($new_path)) {
                        error_log('Failed resource not removed: ' . $new_path);
                    }

                    return false;
                }

                $obj['resid'] = $new_resid;
                return $new_path;

            } else {
                error_log('Unable to create resource path: ' . $new_path);
                return false;
            }

        } else {

            $old_path = $this->resPath();

            if (!is_dir($old_path)) {
                error_log('Resid is not empty, but ' . $old_path . 'doesn\'t exist');
                return false;
            }

            //
            if (rename($old_path, $new_path)) {

                $this
                    ->setResid($new_resid)
                    ->save();

                return $new_path;

            } else {
                return false;
            }
        }

    }

}
