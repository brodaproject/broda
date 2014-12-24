<?php
namespace Broda\Tests\Component\Rest;

use Broda\Component\Rest\RestResponse;
use Broda\Tests\TestCase;

/**
 * @ group x
 */
class RestResponseTest extends TestCase
{

    public function testInstanciateAndGetData()
    {
        $response = new RestResponse('{"prop":"value"}');

        $this->assertInstanceOf("Broda\Component\Rest\RestResponse", $response);
        // desacoplado do Symfony (mudar isso pode ser uma BC)
        $this->assertNotInstanceOf("Symfony\Component\HttpFoundation\Response", $response);
        $this->assertEquals('{"prop":"value"}', $response->getData());
    }

}

