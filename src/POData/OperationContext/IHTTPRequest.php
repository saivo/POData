<?php

declare(strict_types=1);

namespace POData\OperationContext;

/**
 * Interface IHTTPRequest.
 * @package POData\OperationContext
 */
interface IHTTPRequest
{
    /**
     * get the raw incoming url.
     *
     * @return string RequestURI called by User with the value of QueryString
     */
    public function getRawUrl(): string;

    /**
     * get the specific request headers.
     *
     * @param string $key The header name
     *
     * @return array|string|null value of the header, NULL if header is absent
     */
    public function getRequestHeader(string $key);

    /**
     * Returns the Query String Parameters (QSPs) as an array of KEY-VALUE pairs.  If a QSP appears twice
     * it will have two entries in this array.
     *
     * @return array[]
     */
    public function getQueryParameters(): array;

    /**
     * Get the HTTP method/verb of the HTTP Request.
     *
     * @return HTTPRequestMethod
     */
    public function getMethod(): HTTPRequestMethod;

    /**
     * Get the input data of the HTTP Request.
     *
     * @return mixed|null
     */
    public function getAllInput();
}
