<?php

namespace DC\Router;

class Response implements IResponse {

    private $content;
    private $statusCode = StatusCodes::HTTP_OK;
    private $headers = array();
    private $charset = Charset::UTF8;
    private $contentType = ContentType::HTML;

    /**
     * Return the status code this should return
     *
     * @return int HTTP status code
     */
    function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $code HTTP status code
     */
    function setStatusCode($code)
    {
        $this->statusCode = (int)$code;
        $this->checkStatusCodeAndContent();
    }

    private function checkStatusCodeAndContent() {
        if (!StatusCodes::canHaveBody($this->statusCode) && $this->content != null) {
            throw new Exceptions\ContentFoundWithNoContentStatusCodeException($this);
        }
    }

    /**
     * Return content that you want to send to the browser.
     *
     * @return mixed
     */
    function getContent()
    {
        return $this->content;
    }

    /**
     * Set the content to send.
     *
     * @param mixed $content
     */
    function setContent($content)
    {
        $this->content = $content;
        $this->checkStatusCodeAndContent();
    }

    private function setContentTypeHeader() {
        $this->removeCustomHeader("Content-Type");
        $this->setCustomHeader("Content-Type", sprintf("%s; charset=%s", $this->getContentType(), $this->getCharset()));
    }

    /**
     * Set the content type. Convenience method for
     *   $this->removeCustomHeader('Content-Type');
     *   $this->addCustomHeader('Content-Type', $mime);
     *
     * @param string $mime
     */
    function setContentType($mime)
    {
        $this->contentType = $mime;
        $this->setContentTypeHeader();
    }

    /**
     * @return string MIME type
     */
    function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return string[string] Array of names to values
     */
    function getCustomHeaders()
    {
        return $this->headers;
    }

    /**
     * Set an additional header with this name.
     *
     * @param string $name The header to set
     * @param string $value The value(s) to set
     * @return void
     */
    function setCustomHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Add a set of additional headers.
     *
     * Values can be strings or arrays (to set multiple values)
     *
     * @param array $values
     * @return void
     */
    function setCustomHeaders(array $values)
    {
        array_walk($values, function($value, $name) {
            $this->setCustomHeader($name, $value);
        });
    }

    /**
     * Removes all headers with the corresponding name.
     *
     * @param string $name
     * @return void
     */
    function removeCustomHeader($name)
    {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
    }

    /**
     * Removes all custom headers (except Content-Type).
     *
     * @return void
     */
    function clearCustomHeaders()
    {
        $this->headers = [];
        $this->setContentTypeHeader();
    }

    /**
     * @inheritdoc
     */
    function setCharset($charset)
    {
        $this->charset = $charset;
        $this->setContentTypeHeader();
    }

    /**
     * @inheritdoc
     */
    function getCharset()
    {
        return $this->charset;
    }
}