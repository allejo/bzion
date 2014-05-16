<?php
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Bridge\Twig\Extension\RoutingExtension;

class TwigCacheWarmer implements CacheWarmerInterface
{
    public function warmUp($cacheDir)
    {
        $directory = __DIR__.'/../views';

        $loader = new Twig_Loader_Filesystem($directory);
        $twig = new Twig_Environment($loader, array(
            'cache' => $cacheDir . '/twig',
            'debug' => false
        ));
        $twig->addExtension(new RoutingExtension(Service::getGenerator()));

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $i) {
            $matches = array();
            if (preg_match('/^(' . preg_quote($directory,'/') . '\\/)(.+\.twig)$/i', $i, $matches)) {
                $twig->loadTemplate($matches[2]);
            }
        }
    }

    public function isOptional()
    {
        return true;
    }
}
