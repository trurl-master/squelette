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

                // if (!$model::setResid($new_resid)) {

                //     error_log('Resource created but resid not set for ' . $table . ', id = ' . $obj['id']);

                //     if (!rmdir($new_path)) {
                //         error_log('Failed resource not removed: ' . $new_path);
                //     }

                //     return false;
                // } else {
                //     $obj['resid'] = $new_resid;
                //     return $new_path;
                // }
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


// class ResourceSet
// {

//     public static function path($model, $path = false)
//     {
//         $resid = $model->getResid();

//         return App::cfg('assets') . 'docs/' . ($path ? $path.'/' : '') . $resid . '/';
//     }

//     public static function srcpath($model, $path = false)
//     {
//         $resid = $model->getResid();

//         return App::home() . '/docs/' . ($path ? $path.'/' : '') . $resid . '/';
//     }

//     public static function update($model, $path)
//     {

//         $old_path = self::path($model, $path);

//         if (!is_dir($old_path)) {
//             error_log('Resid is not empty, but ' . $old_path . 'doesn\'t exist');
//             return false;
//         }

//         do {

//             $new_resid = uniqid();
//             $new_path = App::cfg('assets') . 'docs/' . ($path ? $path.'/' : '') . $new_resid . '/';

//         } while(is_dir($new_path));

//         //
//         if (empty($model->getResid())) {

//             if (mkdir($new_path)) {
//                 // update DB
//                 try {
//                     $model::setResid($new_resid)
//                 } catch (Exception $e) {
//                     error_log('Resource created but resid not set for ' . $table . ', id = ' . $obj['id']);

//                     if (!rmdir($new_path)) {
//                         error_log('Failed resource not removed: ' . $new_path);
//                     }

//                     return false;
//                 }

//                 $obj['resid'] = $new_resid;
//                 return $new_path;

//                 // if (!$model::setResid($new_resid)) {

//                 //     error_log('Resource created but resid not set for ' . $table . ', id = ' . $obj['id']);

//                 //     if (!rmdir($new_path)) {
//                 //         error_log('Failed resource not removed: ' . $new_path);
//                 //     }

//                 //     return false;
//                 // } else {
//                 //     $obj['resid'] = $new_resid;
//                 //     return $new_path;
//                 // }
//             } else {
//                 error_log('Unable to create resource path: ' . $new_path);
//                 return false;
//             }

//         } else {
//             if (rename($old_path, $new_path)) {
//                 // update DB
//                 if (!DBM::set_value($table, 'resid', $new_resid, $obj['id'])) {

//                     error_log('Resource renamed but resid not set for ' . $table . ', new path = ' . $new_path . ', old path = ' . $old_path . ', id = ' . $obj['id']);

//                     if (!rename($new_path, $old_path)) {
//                         error_log('Unable to rename resource back from: ' . $new_path . ' to ' . $old_path);
//                     }

//                     return false;
//                 } else {
//                     $obj['resid'] = $new_resid;
//                     return $new_path;
//                 }
//             } else {
//                 return false;
//             }
//         }


//     }

// }
