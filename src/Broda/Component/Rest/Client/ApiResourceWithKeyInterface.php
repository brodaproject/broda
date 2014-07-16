<?php

namespace Broda\Component\Rest\Client;

/**
 * Interface ApiResourceWithKeyInterface
 *
 *
 *
 * @author raphael
 *
 */
interface ApiResourceWithKeyInterface extends ApiResourceInterface
{

    /**
     * Define o token do resource
     *
     * @param string $key
     * @return ApiResourceInterface
     */
    public function setKey($key);

    /**
     * Retorna o token do resource
     *
     * @return string
     */
    public function getKey();

}
