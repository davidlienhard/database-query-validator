<?php
/**
 * contains the PhpNodeVisitor class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester;

use \DavidLienhard\Database\QueryValidator\Queries\Query;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

/**
 * inspects a node from \PhpParser
 *
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
class PhpNodeVisitor extends NodeVisitorAbstract
{
    /**
     * list of all the queries found
     * @var     array<\DavidLienhard\Database\QueryValidator\Queries\QueryInterface>
     */
    private array $queries = [];

    /**
     * initializes the object & saves the current filename
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string              $filename   name of the file currently beeing parsed
     */
    public function __construct(private string $filename)
    {
    }

    /**
     * enters a \PhpParser node and adds the content to the list if its a db->query()
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           Node                $node       the node to inspect
     * @return          null|int|Node                   Replacement node (or special return value)
     */
    public function enterNode(Node $node) : int|Node|null
    {
        if (!($node instanceof MethodCall)) {
            return null;
        }

        $isDbVar = ($node->var->name ?? "") === "db" || ($node->var->name->name ?? "") === "db";
        $isQueryNode = ($node->name->name ?? "") === "query";

        if (!$isDbVar || !$isQueryNode) {
            return null;
        }

        $queryPrettyPrint = (new Standard)->prettyPrint($node->args);
        $queryContent = explode("\n", $queryPrettyPrint);
        $query = $queryContent[0] ?? "";
        unset($queryContent[0]);
        $parameters = count($queryContent) > 0 ? $queryContent : [];

        $this->queries[] = new Query(
            $query,
            $parameters,
            $this->filename,
            $node->name->getLine() ?? 0
        );

        return null;
    }

    /**
     * returns the queries found in the file
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @return          array<\DavidLienhard\Database\QueryValidator\Queries\QueryInterface>
     */
    public function getQueries() : array
    {
        return $this->queries;
    }
}