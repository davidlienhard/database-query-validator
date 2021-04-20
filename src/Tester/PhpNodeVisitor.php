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
     * @var     array
     */
    private array $queries = [];

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
        if ($node instanceof MethodCall) {
            if (($node->var->name->name ?? "") === "db" && ($node->name->name ?? "") === "query") {
                $this->queries[] = [
                    "line" => $node->name->getLine() ?? 0,
                    "data" => explode("\n", (new Standard)->prettyPrint($node->args))
                ];
            }
        }

        return null;
    }

    /**
     * returns the queries found in the file
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getQueries() : array
    {
        return $this->queries;
    }
}
