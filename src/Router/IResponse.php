<?php

namespace DC\Router;

interface IResponse {
    /**
     * Return the status code this should return
     *
     * @return int HTTP status code
     */
    function getStatusCode();

    /**
     * @param int $code HTTP status code
     */
    function setStatusCode($code);

    /**
     * Return content that you want to send to the browser.
     *
     * @return mixed
     */
    function getContent();

    /**
     * Set the content to send.
     *
     * @param mixed $content
     */
    function setContent($content);

    /**
     * Set the content type. Convenience method for
     *   $this->setCustomHeader('Content-Type', $mime);
     *
     * @param string $mime
     */
    function setContentType($mime);

    /**
     * @return string MIME type
     */
    function getContentType();

    /**
     * @return string[string] Array of names to values
     */
    function getCustomHeaders();

    /**
     * Set an additional header with this name.
     *
     * @param string $name The header to set
     * @param string $value The value to set
     * @return void
     */
    function setCustomHeader($name, $value);

    /**
     * Add a set of additional headers.
     *
     * @param array $values
     * @return void
     */
    function setCustomHeaders(array $values);

    /**
     * Removes header with the corresponding name.
     *
     * @param string $name
     * @return void
     */
    function removeCustomHeader($name);

    /**
     * Removes all custom headers (except Content-Type, if set).
     *
     * @return void
     */
    function clearCustomHeaders();
}