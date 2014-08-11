<?php

namespace Broda\Framework\Model;

/**
 * Classe conveniente para models que precisam retornar seu nome de classe (FQCN)
 * quando são chamadas pelos repositórios do Doctrine.
 *
 * Em vez de:
 * $em->getRepository('Projeto\Model\ModelX')...
 *
 * É possível fazer:
 * use Projeto\Model\ModelX;
 *
 * $em->getRepository(ModelX::getClass())...
 *
 * Desta forma é muito mais fácil renomear uma classe se você usa uma IDE que
 * suporta este tipo de refatoração (do que procurar o nome da classe em strings,
 * o que muitas IDEs não suportam)
 *
 * Se você usa o PHP 5.4>, você pode usar:
 * $em->getRepository(ModelX::class)
 *
 * ...e não é necessário usar esta classe.
 *
 */
abstract class AbstractModel
{
    static public function getClass()
    {
        return get_called_class();
    }
}
