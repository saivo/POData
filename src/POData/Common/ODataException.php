<?php

declare(strict_types=1);

namespace POData\Common;

use Exception;

/**
 * Class ODataException.
 */
class ODataException extends Exception
{
    /**
     * The error code.
     *
     * @var string
     */
    private $errorCode;

    /**
     * The HTTP status code.
     *
     * @var int
     */
    private $statusCode;

    /**
     * Create new instance of ODataException.
     *
     * @param string      $message    The error message
     * @param int         $statusCode The HTTP status code
     * @param string|null $errorCode  The error code
     */
    public function __construct($message, $statusCode, string $errorCode = null)
    {
        assert(is_int($statusCode) && 0 < $statusCode, 'Status code must be integer and positive');
        assert(is_string($message), 'Message must be a string');
        assert(null === $errorCode || is_string($errorCode), 'Error code must be null or a string');
        $this->errorCode  = $errorCode;
        $this->statusCode = $statusCode;
        parent::__construct($message, $statusCode);
    }

    /**
     * Creates an instance of ODataException
     * representing syntax error in the query.
     *
     * @param string $message The error message
     *
     * @return ODataException
     */
    public static function createSyntaxError($message)
    {
        return self::createBadRequestError($message);
    }

    /**
     * Creates an instance of ODataException
     * representing HTTP bad request error.
     *
     * @param string $message The error message
     *
     * @return ODataException
     */
    public static function createBadRequestError($message)
    {
        return new self($message, 400);
    }

    /**
     * Creates an instance of ODataException when a
     * resource represented by a segment in the url is not found.
     *
     * @param string $segment The segment in the url for which corresponding
     *                        resource not present in the data source
     *
     * @return ODataException
     */
    public static function createResourceNotFoundError($segment)
    {
        return new self(Messages::uriProcessorResourceNotFound($segment), 404);
    }

    /**
     * Creates an instance of ODataException when a
     * resource not found in the data source.
     *
     * @param string $message The error message
     *
     * @return ODataException
     */
    public static function resourceNotFoundError($message)
    {
        return new self($message, 404);
    }

    /**
     * Creates an instance of ODataException when some
     * internal error happens in the library.
     *
     * @param string $message The detailed internal error message
     *
     * @return ODataException
     */
    public static function createInternalServerError($message)
    {
        return new self($message, 500);
    }

    /**
     * Creates an instance of ODataException when requester tries to
     * access a resource which is forbidden.
     *
     * @return ODataException
     */
    public static function createForbiddenError()
    {
        return new self(Messages::uriProcessorForbidden(), 403);
    }

    /**
     * Creates a new exception to indicate Precondition error.
     *
     * @param string $message Error message for this exception
     *
     * @return ODataException
     */
    public static function createPreConditionFailedError($message)
    {
        return new self($message, 412);
    }

    /**
     * Creates a new exception when requester ask for a service facility
     * which is not implemented by this library.
     *
     * @param string $message Error message for this exception
     *
     * @return ODataException
     */
    public static function createNotImplementedError($message)
    {
        return new self($message, 501);
    }

    /**
     * Creates an instance of ODataException when requester to
     * set value which is not allowed.
     *
     * @param string $message Error message for this exception
     *
     * @return ODataException
     */
    public static function notAcceptableError($message)
    {
        return new self($message, 406);
    }

    /**
     * Get the status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
