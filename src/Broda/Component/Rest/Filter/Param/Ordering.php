<?php

namespace Broda\Component\Rest\Filter\Param;

use Doctrine\Common\Collections\Criteria;

/**
 * Classe Ordering
 *
 * @author raphael
 */
class Ordering
{

    /**
     *
     * @var Column
     */
    protected $column;
    protected $dir = Criteria::ASC;

    public function __construct(Column $column, $dir = Criteria::ASC)
    {
        $this->column = $column;
        $this->setDir($dir);
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function setColumn(Column $column)
    {
        $this->column = $column;
        return $this;
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function setDir($dir)
    {
        $this->dir = strtolower($dir) == 'desc' ||
                $dir === Criteria::DESC ||
                $dir < 0
                ? Criteria::DESC : Criteria::ASC;
        return $this;
    }

}
