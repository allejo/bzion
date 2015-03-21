<?php
namespace BZIon\Twig;

use Service;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class TwigCacheWarmer implements CacheWarmerInterface
{
    public function warmUp($cacheDir)
    {
        $directory = __DIR__ . '/../../views';

        $twig = Service::getTemplateEngine();

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $i) {
            $matches = array();
            if (preg_match('/^(' . preg_quote($directory, '/') . '\\/)(.+\.twig)$/i', $i, $matches)) {

                list($topDirectory) = explode("/", $matches[2], 2);

                // Don't cache the profiler files, since they are rendered by a
                // different twig instance
                if ($topDirectory === 'Profiler') {
                    continue;
                }

                $twig->loadTemplate($matches[2]);
            }
        }
    }

    public function isOptional()
    {
        return true;
    }
}
