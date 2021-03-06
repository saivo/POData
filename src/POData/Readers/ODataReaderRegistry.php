<?php

declare(strict_types=1);


namespace POData\Readers;

use POData\Common\Version;

/**
 * Class ODataReaderRegistry.
 * @package POData\Readers
 */
class ODataReaderRegistry
{
    /** @var IODataReader[] */
    private $readers = [];

    /**
     * @param IODataReader $reader
     */
    public function register(IODataReader $reader)
    {
        $this->readers[] = $reader;
    }

    /**
     * @param Version $responseVersion
     * @param $contentType
     *
     * @return IODataReader|null the writer that can handle the given criteria, or null
     */
    public function getReader(Version $responseVersion, $contentType)
    {
        foreach ($this->readers as $reader) {
            if ($reader->canHandle($responseVersion, $contentType)) {
                return $reader;
            }
        }
        return null;
    }
}
