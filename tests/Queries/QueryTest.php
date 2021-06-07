<?php declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Queries;

use DavidLienhard\Database\QueryValidator\Queries\Query;
use DavidLienhard\Database\QueryValidator\Queries\QueryInterface;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    private string $query = "SELECT * FROM `table`";
    private array $parameters = [
        "new DBParam(\"i\", 5)",
        "new DBParam(\"s\", \"test\")",
    ];
    private string $filename = "testfile.php";
    private int $linenumber = 5;

    /**
     * @covers DavidLienhard\Database\QueryValidator\Queries\Query
     * @test
     */
    public function testCanBeCreated(): void
    {
        $query = new Query(
            $this->query,
            $this->parameters,
            $this->filename,
            $this->linenumber
        );

        $this->assertInstanceOf(Query::class, $query);
        $this->assertInstanceOf(QueryInterface::class, $query);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Queries\Query
     * @test
     */
    public function testCanBeCreatedWithEmptyParameters(): void
    {
        $query = new Query(
            $this->query,
            [],
            $this->filename,
            $this->linenumber
        );

        $this->assertInstanceOf(Query::class, $query);
        $this->assertInstanceOf(QueryInterface::class, $query);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Queries\Query
     * @test
     */
    public function testCannotBeCreatedWithMissingData(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new Query;

        $this->expectException(\ArgumentCountError::class);
        new Query($this->query);

        $this->expectException(\ArgumentCountError::class);
        new Query($this->query, $this->parameters);

        $this->expectException(\ArgumentCountError::class);
        new Query($this->query, $this->parameters, $this->filename);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Queries\Query
     * @test
     */
    public function testCanGetQuery(): void
    {
        $query = new Query(
            $this->query,
            $this->parameters,
            $this->filename,
            $this->linenumber
        );

        $this->assertEquals($this->query, $query->getQuery());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Queries\Query
     * @test
     */
    public function testCanGetParameters(): void
    {
        $query = new Query(
            $this->query,
            $this->parameters,
            $this->filename,
            $this->linenumber
        );

        $this->assertEquals($this->parameters, $query->getParameters());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Queries\Query
     * @test
     */
    public function testCanGetFilename(): void
    {
        $query = new Query(
            $this->query,
            $this->parameters,
            $this->filename,
            $this->linenumber
        );

        $this->assertEquals($this->filename, $query->getFilename());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Queries\Query
     * @test
     */
    public function testCanGetLinennumber(): void
    {
        $query = new Query(
            $this->query,
            $this->parameters,
            $this->filename,
            $this->linenumber
        );

        $this->assertEquals($this->linenumber, $query->getLinenumber());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Queries\Query
     * @test
     */
    public function testCanGetIsPrepared(): void
    {
        $query = new Query($this->query, [], $this->filename, $this->linenumber);
        $this->assertFalse($query->isPrepared());

        $query = new Query($this->query, $this->parameters, $this->filename, $this->linenumber);
        $this->assertTrue($query->isPrepared());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Queries\Query
     * @test
     */
    public function testQueryTypes(): void
    {
        $queries = [
            "SELECT * FROM `user`"                => Query::TYPE_SELECT,
            "INSERT INTO `user` SET `userID` = 1" => Query::TYPE_INSERT,
            "UPDATE `user` SET `userID` = 1"      => Query::TYPE_UPDATE,
            "DELETE FROM `user`"                  => Query::TYPE_DELETE,
            "CREATE TABLE `user` ()"              => Query::TYPE_CREATE,
            "OPTIMIZE TABLE `user`"               => Query::TYPE_OPTIMIZE,
            "unknown query"                       => Query::TYPE_UNKNOWN
        ];

        foreach ($queries as $query => $type) {
            $query = new Query($query, [], $this->filename, $this->linenumber);
            $this->assertEquals($type, $query->getType());
        }
    }
}
