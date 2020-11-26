<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pluralize', [$this, 'doSomething']),
        ];
    }

    public function doSomething(int $count,string $singular, ?string $plural=null):string
    {
        $plural = $plural ?? $singular.'s';//si on a un pluriel en argument on l'utilise sinon on utilise le singulier en lui rajoutant un s
        //$plural ??= $singular.'s';équivalent du dessus
        
        $result = $count === 1 ? $singular : $plural;//si le compte est egale a 1 tu mets au singulier sinon au pluriel
        return "$count $result";//le double cote permet de retourner la valeur des variable
    }
}
