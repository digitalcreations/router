<?php

namespace DC\Tests\Router\Swagger;

/**
 * Cats. They're awesome.
 */
class CatsController extends \DC\Router\JsonController implements \DC\Router\Swagger\ISwaggerAPI {
    private $repository;

    function __construct()
    {
        $this->repository = [
            1 => new \DC\Tests\Router\Swagger\Cat(1, "Squiggles"),
            2 => new \DC\Tests\Router\Swagger\Cat(2, "Squishy"),
            3 => new \DC\Tests\Router\Swagger\Cat(3, "Angry")
        ];
    }

    /**
     * Record the birth of a cat.
     *
     * @route POST /api/cats
     * @param \DC\Tests\Router\Swagger\Cat $cat
     * @body $cat
     * @return \DC\Tests\Router\Swagger\Cat
     */
    public function postCat(\DC\Tests\Router\Swagger\Cat $cat) {
        return $cat;
    }

    /**
     * List all cats.
     *
     * Return a list of all cats in the system.
     *
     * @route GET /api/cats
     * @return \DC\Tests\Router\Swagger\Cat[] All the cats
     */
    public function getAll() {
        return array_values($this->repository);
    }

    /**
     * Get a specific cat by id.
     *
     * @route GET /api/cat/{id:int}
     * @param int $id Cat ID
     * @return \DC\Tests\Router\Swagger\Cat A single cat
     */
    public function get($id) {
        return $this->repository[$id];
    }

    /**
     * @route GET /api/cats/search?q={name}
     * @param string $name Name
     * @return \DC\Tests\Router\Swagger\Cat[]
     */
    public function getByName($name) {
        return array_filter($this->repository, function($cat) use ($name) {
            return stripos($cat->name, $name) !== false;
        });
    }

    /**
     * Get the cat's birthday.
     *
     * @route GET /api/cat/{id:int}/birthday?format={foo:int}
     * @param int $id Cat ID
     * @param int $foo DateTime format
     * @return \DateTime Name
     * @throws \Exception When the kitten isn't born yet.
     */
    public function getBirthday($id, $foo = 23) {
        return new \DateTime();
    }
}

class Cat {
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;

    function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
}

class SwaggerDefinitionBuilderTest extends \PHPUnit_Framework_TestCase
{
    private function createContainer(array $controllers) {
        $composerFile = str_replace("src/Tests/Router/Swagger/SwaggerDefinitionBuilderTest.php", "composer.json", __FILE__);
        $swaggerOptions = new \DC\Router\Swagger\Options(new \DC\Router\Swagger\ComposerPackage($composerFile));
        $routerOptions = new \DC\Router\IoC\ModuleOptions();
        $routerOptions->enableSwagger($swaggerOptions);

        $container = new \DC\IoC\Container();
        $container->registerModules([
            new \DC\Cache\Module(),
            new \DC\JSON\IoC\Module(),
            new \DC\Router\IoC\Module($controllers, $routerOptions)
        ]);

        return $container;
    }

    function testBuilder() {
        $container = $this->createContainer(['\DC\Tests\Router\Swagger\CatsController']);

        /** @var \DC\Router\Swagger\SwaggerDefinitionBuilder $builder */
        $builder = $container->resolve('\DC\Router\Swagger\SwaggerDefinitionBuilder');
        $this->assertInstanceOf('\DC\Router\Swagger\SwaggerDefinitionBuilder', $builder);

        $routeFactory = $container->resolve('\DC\Router\IRouteFactory');

        $definition = $builder->build($routeFactory->getRoutes());
        $this->assertEquals("2.0", $definition["swagger"]);
        $this->assertEquals("dc/router", $definition["info"]["title"]);
        $this->assertEquals("Digital Creations AS", $definition["info"]["contact"]["name"]);
        $this->assertEquals("MIT", $definition["info"]["license"]["name"]);
        $this->assertTrue(key_exists("version", $definition["info"]));

        $this->assertEquals(4, count($definition["paths"]));

        // single endpoint
        $indexDef = $definition["paths"]["/api/cats"]["get"];
        $this->assertEquals("List all cats.", $indexDef["summary"]);
        $this->assertEquals("Return a list of all cats in the system.", $indexDef["description"]);
        $this->assertEquals("application/json", $indexDef["produces"][0]);
        $this->assertEquals("array", $indexDef["responses"]["200"]["schema"]["type"]);
        $this->assertEquals("#/definitions/DC_Tests_Router_Swagger_Cat", $indexDef["responses"]["200"]["schema"]["items"]['$ref']);
        $this->assertEquals("All the cats", $indexDef["responses"]["200"]["description"]);

        // types
        $typeDef = $definition["definitions"]["DC_Tests_Router_Swagger_Cat"];
        $this->assertEquals("object", $typeDef["type"]);
        $this->assertEquals("integer", $typeDef["properties"]->id["type"]);
        $this->assertEquals("int64", $typeDef["properties"]->id["format"]);
        $this->assertEquals("string", $typeDef["properties"]->name["type"]);
    }
}
