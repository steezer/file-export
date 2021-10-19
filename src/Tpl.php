<?php

namespace FileExport;

use Exception;
use FileExport\Smarty\Smarty;

class Tpl
{

    public static function compile($tplName, $data = null, $savePath=null)
    {
        $smarty = new Smarty();
        $dir = !empty($savePath) ? $savePath : dirname(__FILE__) . '/';
        $smarty->config_dir = self::createDir($dir . "tmp/configs");
        $smarty->caching = false;
        $smarty->template_dir = dirname(__DIR__).DS.'tpls';
        $smarty->compile_dir = self::createDir($dir . "tmp/templates_c");
        $smarty->cache_dir = self::createDir($dir . "tmp/cahce");

        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $smarty->assign($k, $v);
            }
        }

        return $smarty->fetch($tplName);
    }

    private static function createDir($path)
    {

        if (file_exists($path)) {
            return $path;
        }

        if (!mkdir($path, 0700, true)) {
            throw new Exception("directory creation failed: " . $path);
        }

        return $path;
    }
}
