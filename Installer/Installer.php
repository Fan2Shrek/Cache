<?php

namespace Sruuua\Cache\Installer;

use Composer\Script\Event;
use Symfony\Component\Yaml\Yaml;

class Installer
{
    public static function postInstall(Event $event)
    {
        $srcDir = __DIR__ . '/../config';
        if (!file_exists($srcDir)) {
            mkdir($srcDir);
        }

        $file = $srcDir . '/cache.yml';
        if (!file_exists($file)) {
            $defaultsConfig = array('cache' => ['enable' => true, 'folder' => '../temp/']);
            $yaml = Yaml::dump($defaultsConfig);
            file_put_contents($file, $yaml);
        }
    }
}
