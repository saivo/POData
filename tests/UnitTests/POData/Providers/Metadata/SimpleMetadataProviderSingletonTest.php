<?php

declare(strict_types=1);

namespace UnitTests\POData\Providers\Metadata;

use AlgoWeb\ODataMetadata\IsOK;
use AlgoWeb\ODataMetadata\MetadataV3\edm\EntityContainer\FunctionImportAnonymousType;
use AlgoWeb\ODataMetadata\MetadataV3\edm\TComplexTypeType;
use AlgoWeb\ODataMetadata\MetadataV3\edm\TEntityTypeType;
use Mockery as m;
use PHPUnit\Framework\Error\Error;
use POData\ObjectModel\IObjectSerialiser;
use POData\OperationContext\ServiceHost;
use POData\Providers\Metadata\ResourceComplexType;
use POData\Providers\Metadata\ResourceEntityType;
use POData\Providers\Metadata\ResourceFunctionType;
use POData\Providers\Metadata\ResourceSet;
use POData\Providers\Metadata\ResourceType;
use POData\Providers\Metadata\SimpleMetadataProvider;
use POData\Providers\Metadata\Type\EdmPrimitiveType;
use POData\Providers\Metadata\Type\TypeCode;
use POData\Providers\Stream\StreamProviderWrapper;
use UnitTests\POData\BaseServiceDummy;
use UnitTests\POData\TestCase;

class SimpleMetadataProviderSingletonTest extends SimpleMetadataProviderTest
{
    public function testCreateSingleton()
    {
        $refDummy = $this->createDummyEntityType();

        $functionName = [get_class($this), 'exampleSingleton'];
        $name         = 'name';
        $return       = m::mock(ResourceEntityType::class);
        $return->shouldReceive('getFullName')->andReturn('number.name');

        $foo = new SimpleMetadataProvider('string', 'number');
        $this->assertNull($foo->resolveSingleton($name));
        $foo->addEntityType($refDummy, 'name');
        $foo->createSingleton($name, $return, $functionName);
        $result = $foo->getSingletons();
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));
        $this->assertTrue($foo->resolveSingleton($name) instanceof ResourceFunctionType);
    }

    public function testCreateSingletonWithDuplicateName()
    {
        $refDummy = $this->createDummyEntityType();

        $functionName = [get_class($this), 'exampleSingleton'];
        $expected     = 'Singleton name already exists';
        $actual       = null;

        $name   = 'name';
        $return = m::mock(ResourceEntityType::class);
        $return->shouldReceive('getFullName')->andReturn('number.name');

        $foo = new SimpleMetadataProvider('string', 'number');
        $foo->addEntityType($refDummy, 'name');
        $foo->createSingleton($name, $return, $functionName);

        try {
            $foo->createSingleton($name, $return, $functionName);
        } catch (\InvalidArgumentException $e) {
            $actual = $e->getMessage();
        }
        $this->assertEquals($expected, $actual);
    }

    public function testCreateSingletonWithNoMapping()
    {
        $functionName = [get_class($this), 'exampleSingleton'];
        $expected     = 'Mapping not defined for number.name';
        $actual       = null;

        $name   = 'name';
        $return = m::mock(ResourceEntityType::class);
        $return->shouldReceive('getFullName')->andReturn('number.name');

        $foo = new SimpleMetadataProvider('string', 'number');

        try {
            $foo->createSingleton($name, $return, $functionName);
        } catch (\InvalidArgumentException $e) {
            $actual = $e->getMessage();
        }
        $this->assertEquals($expected, $actual);
    }

    public function testCreateSingletonLiveWithComplexType()
    {
        $refDummy     = $this->createDummyEntityType();
        $functionName = [get_class($this), 'exampleSingleton'];
        $name         = 'example';
        $complex      = m::mock(TComplexTypeType::class)->makePartial();
        $complex->shouldReceive('getName')->andReturn('number.example');
        $complex->shouldReceive('getName')->andReturn('example');
        $return = new ResourceComplexType($refDummy, $complex);
        $this->assertEquals('example', $return->getName());
        $this->assertEquals('number.example', $return->getFullName());
        //$return->setName('number.example');

        $foo = new SimpleMetadataProvider('string', 'number');
        $foo->addEntityType($refDummy, 'example');
        $foo->createSingleton($name, $return, $functionName);
        $result = $foo->callSingleton('example');
        $this->assertTrue(is_array($result));
        $this->assertEquals(0, count($result));
    }

    /**
     * @throws \POData\Common\InvalidOperationException
     * @throws \ReflectionException
     */
    public function testCreateSingletonNameCollideWithExistingEntitySet()
    {
        $expected = 'Resource set with same name, foreSet, exists';
        $actual   = null;

        $refDummy     = $this->createDummyEntityType();
        $functionName = [get_class($this), 'exampleSingleton'];

        $complex = m::mock(TComplexTypeType::class)->makePartial();
        $complex->shouldReceive('getName')->andReturn('example');
        $return = new ResourceComplexType($refDummy, $complex);

        $forward = new reusableEntityClass4('foo', 'bar');
        $back    = new reusableEntityClass5('foo', 'bar');

        $foo = new SimpleMetadataProvider('string', 'number');

        $fore = $foo->addEntityType(new \ReflectionClass(get_class($forward)), 'fore');
        $aft  = $foo->addEntityType(new \ReflectionClass(get_class($back)), 'aft');
        $foo->addKeyProperty($fore, 'key', EdmPrimitiveType::INT32());
        $foo->addKeyProperty($aft, 'key', EdmPrimitiveType::INT32());
        $this->assertTrue($fore instanceof ResourceType);
        $this->assertTrue($aft instanceof ResourceType);

        $foreSet = $foo->addResourceSet('foreSet', $fore);
        $aftSet  = $foo->addResourceSet('aftSet', $aft);
        $this->assertTrue($foreSet instanceof ResourceSet);
        $this->assertTrue($aftSet instanceof ResourceSet);
        $name = $foreSet->getName();

        try {
            $foo->createSingleton($name, $return, $functionName);
        } catch (\InvalidArgumentException $e) {
            $actual = $e->getMessage();
        }
        $this->assertEquals($expected, $actual);
    }

    public function testCallNonExistentSingleton()
    {
        $expected = 'Requested singleton does not exist';
        $actual   = null;

        $foo  = new SimpleMetadataProvider('string', 'number');
        $name = "Put 'Em High";

        try {
            $foo->callSingleton($name);
        } catch (\InvalidArgumentException $e) {
            $actual = $e->getMessage();
        }
        $this->assertEquals($expected, $actual);
    }

    public function testCallNonExistentSingletonWithArrayName()
    {
        $expected = 'array_key_exists(): The first argument should be either a string or an integer';
        $actual   = null;

        $foo  = new SimpleMetadataProvider('string', 'number');
        $name = [];

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(' must be of the type string, array given');
        $foo->callSingleton($name);
    }

    public function testCallNonExistentSingletonWithObjectName()
    {
        $expected = 'array_key_exists(): The first argument should be either a string or an integer';
        $actual   = null;

        $foo  = new SimpleMetadataProvider('string', 'number');
        $name = new \DateTime();

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(' must be of the type string, object given');
        $foo->callSingleton($name);
    }


    public static function exampleSingleton()
    {
        return [];
    }

    /**
     * @return \ReflectionClass
     */
    private function createDummyEntityType()
    {
        $cereal = m::mock(IObjectSerialiser::class);
        $cereal->shouldReceive('setService')->withAnyArgs()->andReturnNull();
        $host = m::mock(ServiceHost::class);
        $host->shouldReceive('getAbsoluteServiceUri')->andReturn('http://localhost/odata.svc');
        $wrapper = m::mock(StreamProviderWrapper::class);
        $wrapper->shouldReceive('setService')->andReturnNull();
        $dummy    = new BaseServiceDummy(null, $host, $cereal, $wrapper);
        $refDummy = new \ReflectionClass($dummy);
        return $refDummy;
    }
}
