<?php

namespace Broda;

use Pimple\Container;

/**
 * Classe Framework
 *
 * @author raphael
 */
class Framework extends Container
{
    public function __construct(array $values = array())
    {

        $this['kernel'] = function ($frm) {
            return new \Symfony\Component\HttpKernel\HttpKernel($frm['dispatcher'], $frm['resolver']);
        };

        $this['dispatcher'] = function () {
            return new \Symfony\Component\EventDispatcher\EventDispatcher();
        };

        parent::__construct($values);
    }
}
