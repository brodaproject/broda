<?php

namespace Broda\Component\Rest\Client;

use Broda\Component\Rest\RestService;

/**
 * Classe Resource
 *
 * {@example}
    $api = new Api('http://api.example.com/v1');
    $restService = new RestService(new JMS\Serializer());
    $resource = new Resource($restService, $api->createResource('posts', '/post/{id}', 'Project\Element\Post', 'json'));
    $post = $resource->find('48239482'); // GET http://api.google.com/v1/posts/48239482

    var_dump($post instanceof \Project\Element\Post); // TRUE

    echo $post->getTitle(); // Title of the post

    $resource->delete($post); // TRUE
    // or (only if \Project\Element\Post implements ElementInterface or extends AbstractElement)
    $post->delete(); // TRUE
 * {/@example}
 *
 * @author raphael
 */
class Resource implements ResourceInterface
{

    /**
     *
     * @var RestService
     */
    protected $rest;

    /**
     *
     * @var ApiResourceInterface
     */
    protected $apiResource;

    /**
     *
     * @var ElementMetadataFactory
     */
    protected $elementMetadataFactory;

    public function __construct(RestService $rest, ApiResourceInterface $apiResource)
    {
        $this->rest = $rest;
        $this->apiResource = $apiResource;
        $this->elementMetadataFactory = new ElementMetadataFactory;
    }

    public function find($id)
    {
        $data = $this->execute('GET', $this->apiResource->getCurrentPath($id));
        return $this->rest->createObject($data, $this->apiResource->getClass(), $this->apiResource->getFormat());
    }

    public function all($criteria = null)
    {
        $data = $this->execute('GET', $this->apiResource->getCurrentPath(null, null, $criteria));
        return $this->rest->createObject($data, $this->apiResource->getClass(), $this->apiResource->getFormat());
    }

    public function save($element)
    {
        $class = $this->apiResource->getClass();
        if (!($element instanceof $class)) {
            throw new \InvalidArgumentException(sprintf(
                    'Resource %s só aceita objetos da classe %s',
                    $this->apiResource->getCurrentPath(),
                    $class
            ));
        }
        $data = $this->rest->formatOutput($element, $this->apiResource->getFormat());

        // mapeia as propriedades e retorna uma classe com os metadados
        $metadata = $this->elementMetadataFactory->getMetadata($class);
        $id = $metadata->getIdentifier($element);

        $response = $this->execute($id ? 'PUT' : 'POST', $this->apiResource->getCurrentPath($id), $data);
        if (!$id) {
            return $this->rest->createObject($response, $this->apiResource->getClass(), $this->apiResource->getFormat());
        }
        return $element;
    }

    public function delete($element)
    {
        $class = $this->apiResource->getClass();
        if (!($element instanceof $class)) {
            throw new \InvalidArgumentException(sprintf(
                    'Resource %s só aceita objetos da classe %s',
                    $this->apiResource->getCurrentPath(),
                    $class
            ));
        }

        // mapeia as propriedades e retorna uma classe com os metadados
        $metadata = $this->elementMetadataFactory->getMetadata($class);
        $id = $metadata->getIdentifier($element);
        if (!$id) {
            throw new \InvalidArgumentException(sprintf(
                    'Necessário um identifier para o objeto %s no resource %s',
                    $class,
                    $this->apiResource->getCurrentPath()
            ));
        }

        $this->execute('DELETE', $this->apiResource->getCurrentPath($id));
        return true;
    }

    public function trigger($element, $action)
    {
        $class = $this->apiResource->getClass();
        if (!($element instanceof $class)) {
            throw new \InvalidArgumentException(sprintf(
                    'Resource %s só aceita objetos da classe %s',
                    $this->apiResource->getCurrentPath(),
                    $class
            ));
        }

        // mapeia as propriedades e retorna uma classe com os metadados
        $metadata = $this->elementMetadataFactory->getMetadata($class);
        $id = $metadata->getIdentifier($element);
        if (!$id) {
            throw new \InvalidArgumentException(sprintf(
                    'Necessário um identifier para o objeto %s no resource %s',
                    $class,
                    $this->apiResource->getCurrentPath()
            ));
        }

        $this->execute('PATCH', $this->apiResource->getCurrentPath($id, $action));
        return true;
    }

    private function execute($method, $url, $body = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:', 'Accept: application/json,*/*;q=0.5', 'Content-type: application/json'));

        /*$username = $request->getUsername();
        $password = $request->getPassword();

        if ($username && $password) {
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
        }*/

        switch ($method) {
            case 'POST':
            case 'PUT':
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'GET':
            default:
                break;
        }

        $result = curl_exec($ch);

        if (!$result) {
            $errorNumber = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);

            throw new \Exception($errorNumber . ': ' . $error);
        }

        curl_close($ch);

        return $result;
    }

}
