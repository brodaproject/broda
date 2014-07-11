<?php

namespace Broda\Component\Rest\Filter;

/**
 * Classe NullFilter
 *
 * Filtro que não filtra nada.
 * Útil para não dar erro no RestService quando um filtro não puder ser usado no momento
 * ou não puder ser detectado pelo AbstractFilter::detectFilterByRequest()
 *
 * @author raphael
 */
final class NullFilter extends AbstractFilter
{
}
