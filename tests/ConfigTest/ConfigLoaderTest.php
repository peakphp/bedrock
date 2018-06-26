<?php

use PHPUnit\Framework\TestCase;

use Peak\Config\ConfigLoader;
use Peak\Config\ConfigData;
use Peak\Config\ConfigFile;
use Peak\Config\Processor\JsonProcessor;
use Peak\Common\Collection\DotNotationCollection;
use Peak\Common\Collection\Collection;

class ConfigLoaderTest extends TestCase
{
    public $good_files = [
        'config/arrayfile1.php',
        'config/arrayfile2.php',
    ];

    public $good_mixed_files = [
        'config/arrayfile1.php',
        'config/jsonfile.json',
    ];

    public $good_files_inverse = [
        'config/arrayfile2.php',
        'config/arrayfile1.php',
    ];

    public $good_files2 = [
        FIXTURES_PATH.'/config/arrayfile1.php',
        FIXTURES_PATH.'/config/arrayfile2.php',
    ];

    public $not_good_files = [
        FIXTURES_PATH.'/config/arrayfile98.php',
        FIXTURES_PATH.'/config/arrayfile99.php',
    ];

    public $not_good_mixed_files = [
        FIXTURES_PATH.'/config/arrayfile1.php',
        FIXTURES_PATH.'/config/malformed.json',
    ];

    /**
     * Test load as Collection object
     */
    function testLoadFilesAsCollection()
    {
        $col = (new ConfigLoader(
            $this->good_files,
            FIXTURES_PATH
        ))->asCollection();

        $this->assertTrue($col instanceof Collection);
        $this->assertTrue($col->iam === 'arrayfile2');

        $col = (new ConfigLoader($this->good_files2))->asCollection();
        $this->assertTrue($col instanceof Collection);
        $this->assertTrue($col->iam === 'arrayfile2');

        $col = (new ConfigLoader($this->good_files_inverse, FIXTURES_PATH))->asCollection();
        $this->assertTrue($col instanceof Collection);
        $this->assertTrue($col->iam === 'arrayfile1');
    }

    /**
     * Test load as DotNotationCollection object
     */
    function testLoadFilesAsDotNotationCollection()
    {
        $col = (new ConfigLoader($this->good_files, FIXTURES_PATH))->asDotNotationCollection();
        $this->assertTrue($col instanceof DotNotationCollection);
        $this->assertTrue($col->iam === 'arrayfile2');

        $col = (new ConfigLoader($this->good_files2))->asDotNotationCollection();
        $this->assertTrue($col instanceof DotNotationCollection);
        $this->assertTrue($col->iam === 'arrayfile2');

        $col = (new ConfigLoader($this->good_files_inverse, FIXTURES_PATH))->asDotNotationCollection();
        $this->assertTrue($col instanceof DotNotationCollection);
        $this->assertTrue($col->iam === 'arrayfile1');
    }

    /**
     * Test load as array
     */
    function testLoadFilesAsArray()
    {
        $array = (new ConfigLoader($this->good_files, FIXTURES_PATH))->asArray();
        $this->assertTrue(is_array($array));
        $this->assertTrue($array['iam'] === 'arrayfile2');
    }

    /**
     * Test load as object
     */
    function testLoadFilesAsObject()
    {
        $obj = (new ConfigLoader($this->good_files, FIXTURES_PATH))->asObject();
        $this->assertTrue(is_object($obj));
        $this->assertTrue($obj->iam === 'arrayfile2');
    }

    /**
     * Test load as closure
     */
    function testLoadFilesAsClosure()
    {
        $obj = (new ConfigLoader($this->good_files, FIXTURES_PATH))->asClosure(function($coll) {
            return new Peak\Bedrock\Application\Config($coll->toArray());
        });

        $this->assertInstanceOf('Peak\Bedrock\Application\Config', $obj);
        $this->assertTrue($obj->iam === 'arrayfile2');
    }

    /**
     * Test not found
     */
    function testExceptionFileNotFound()
    {
        try {
            $col = (new ConfigLoader($this->not_good_files))->asCollection();
        } catch (Exception $e) {
            $error = true;
        }
        $this->assertTrue(isset($error));
    }

    /**
     * Test ini
     *
     * @throws \Peak\Config\Exception\UnknownTypeException
     */
    function testIni()
    {
        $col = (new ConfigLoader([
            FIXTURES_PATH.'/config/config.ini',
        ]))->asCollection();

        $this->assertTrue($col instanceof Collection);
        $this->assertTrue($col->all['php']['display_errors'] == 1);
        $this->assertTrue($col->all['front']['default_controller'] === 'index');
    }

    /**
     * Test multiple configuration type together
     *
     * @throws \Peak\Config\Exception\UnknownTypeException
     */
    function testMixedTypeConfigs()
    {
        $col = (new ConfigLoader([
            FIXTURES_PATH.'/config/simple.txt',
            new Collection([
                'foo' => 'bar'
            ]),
            new Collection(['foo' => 'bar']),
            FIXTURES_PATH.'/config/arrayfile1.php',
            FIXTURES_PATH.'/config/config.yml',
            ['array' => 'hophop'],
            function() {
                return ['anonym' => 'function'];
            },
        ]))->asCollection();

        $this->assertTrue($col instanceof Collection);
        $this->assertTrue($col->iam === 'arrayfile1');
        $this->assertTrue($col->foo === 'bar');
        //$this->assertTrue($col->bar === 'foo');
        $this->assertTrue($col->array === 'hophop');
        $this->assertTrue($col->anonym === 'function');
        $this->assertTrue(count($col->items) == 2);
        $this->assertTrue(isset($col[0]));
        $this->assertTrue(trim($col[0]) === 'John');
        $this->assertTrue(isset($col->items) == 2);
    }

    /**
     * Test multiple configuration with ConfigFile and ConfigData
     *
     * @throws \Peak\Config\Exception\UnknownTypeException
     */
    function testMixedTypeConfigs2()
    {
        $col = (new ConfigLoader([
            new ConfigData('{"foo": "bar2", "bar" : "foo"}', new JsonProcessor()),
            new ConfigFile(FIXTURES_PATH.'/config/jsonfile.json')
        ]))->asCollection();

        $this->assertTrue($col->foo === 'bar2');
        $this->assertTrue($col->bar === 'foo');
        $this->assertTrue(is_array($col->widget));
    }

    /**
     * Test UnknownTypeException exception
     * @expectedException \Peak\Config\Exception\UnknownTypeException
     */
    function testIniProcessorException2()
    {
        $col = (new ConfigLoader([
            true,
        ]))->asCollection();
    }


}