<?php

namespace DC\Router;

class JsonController extends ControllerBase {
    /**
     * @inject
     * @var \Negotiation\FormatNegotiator
     */
    public $formatNegotiator;

    private $jsonMimeTypes = array(
        'application/json',
        'application/x-json'
    );

    /**
     * @inject
     * @var \DC\JSON\Serializer
     */
    private $serializer;

    private function getSerializer() {
        if (!isset($this->serializer)) {
            $this->serializer = new \DC\JSON\Serializer();
        }
        return $this->serializer;
    }

    function afterRoute(array $params, IResponse $response)
    {
        $acceptHeader = $this->getRequest()->getHeaders()['Accept'];
        $format = $this->formatNegotiator->getBestFormat($acceptHeader, $this->jsonMimeTypes);

        $content = $response->getContent();
        $response->setContent($this->getSerializer()->serialize($content));
        if ($format == null) {
            $format = "application/json";
        }
        $response->setContentType($format);

        parent::afterRoute($params, $response);
    }

    /**
     * @param string|null $class The class you want the body deserialized to.
     * @return mixed|null
     * @throws Exceptions\UnknownContentTypeException
     */
    function getRequestBodyAsObject($class = null) {
        $body = $this->getRequest()->getBody();
        if ($body == null) return null;

        $headers = $this->getRequest()->getHeaders();
        $contentType = isset($headers['Content-Type']) ? $headers['Content-Type'] : 'application/json';
        if (strpos($contentType, ';') !== false) {
            $contentType = substr($contentType, 0, strpos($contentType, ';'));
        }
        if (in_array($contentType, $this->jsonMimeTypes)) {
            if (!isset($class)) {
                return json_decode($body);
            }
            else {
                return $this->getSerializer()->deserialize($body, $class);
            }
        }

        throw new Exceptions\UnknownContentTypeException($headers['Content-Type']);
    }
}