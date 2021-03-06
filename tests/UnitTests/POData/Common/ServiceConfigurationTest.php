<?php

declare(strict_types=1);

namespace UnitTests\POData\Common;

use Mockery as m;
use POData\Configuration\ServiceConfiguration;
use POData\Providers\Metadata\IMetadataProvider;
use POData\Providers\Metadata\ResourceSet;
use UnitTests\POData\TestCase;

class ServiceConfigurationTest extends TestCase
{
    public function testUseVerboseErrorsRoundTrip()
    {
        $meta = m::mock(IMetadataProvider::class);
        $foo  = new ServiceConfiguration($meta);
        $foo->setUseVerboseErrors(true);
        $this->assertTrue($foo->getUseVerboseErrors());
    }

    public function testEntitySetPageSizeRoundTrip()
    {
        $resource = m::mock(ResourceSet::class);
        $resource->shouldReceive('getName')->andReturn('entity');

        $meta = m::mock(IMetadataProvider::class);
        $meta->shouldReceive('resolveResourceSet')->andReturn($resource);

        $foo = new ServiceConfiguration($meta);
        $foo->setEntitySetPageSize('entity', PHP_INT_MAX);
        $this->assertEquals(0, $foo->getEntitySetPageSize($resource));
    }

    public function testEntitySetNegativePageSizeBlowsUpWithInvalidArgumentException()
    {
        $resource = m::mock(ResourceSet::class);
        $resource->shouldReceive('getName')->andReturn('entity');

        $meta = m::mock(IMetadataProvider::class);
        $meta->shouldReceive('resolveResourceSet')->andReturn(true);

        $foo = new ServiceConfiguration($meta);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The argument to the function setEntitySetPageSize should be non-negative, negative value \'-1\' passed');

        $foo->setEntitySetPageSize('entity', -1);
    }

    public function testValidateETagHeaderRoundTrip()
    {
        $meta = m::mock(IMetadataProvider::class);

        $foo = new ServiceConfiguration($meta);
        $foo->setValidateETagHeader(true);
        $this->assertTrue($foo->getValidateETagHeader());
    }
}
