<?php

namespace Broda\Framework\Model\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Filtro que faz retornar somente os registros que não estão deletados
 * numa tabela que tem a possibilidade de deletar registros através de uma flag.
 *
 * Tabelas deste tipo são úteis para coisas que precisam de um histórico ou
 * que possibilitam a recuperação de dados deletados caso aconteça.
 *
 * @author raphael
 */
class DeletableFilter extends SQLFilter
{

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (!$targetEntity->reflClass->implementsInterface('Broda\Framework\Model\DeletableInterface')) {
            return '';
        }

        $this->setParameter('deleted', false);
        $deletableField = $targetEntity->reflClass->getMethod('getDeletableField')->invoke(null);
        return $targetTableAlias . '.' . $deletableField . ' = ' . $this->getParameter('deleted');
    }

}
