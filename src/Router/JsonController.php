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

    function afterRoute(array $params, IResponse $response)
    {
        $acceptHeader = $this->getRequest()->getHeaders()['Accept'];
        $format = $this->formatNegotiator->getBestFormat($acceptHeader, $this->jsonMimeTypes);

        $content = $response->getContent();
        $response->setContent(json_encode($content));
        $response->setContentType($format);

        parent::afterRoute($params, $response);
    }

    function getRequestBodyAsObject() {
        $body = $this->getRequest()->getBody();
        if ($body == null) return null;

        $headers = $this->getRequest()->getHeaders();
        $contentType = isset($headers['Content-Type']) ? $headers['Content-Type'] : 'application/json';
        if (strpos($contentType, ';') !== false) {
            $contentType = substr($contentType, 0, strpos($contentType, ';'));
        }
        if (in_array($contentType, $this->jsonMimeTypes)) {
            return json_decode($body);
        }

        throw new Exceptions\UnknownContentTypeException($headers['Content-Type']);
    }
}