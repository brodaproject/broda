<?php
namespace Broda\Tests\Component\Rest;

use Broda\Component\Rest\RestResponse;

/**
 * @ group x
 */
class RestResponseTest extends \PHPUnit_Framework_TestCase
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

