<?php
/**
 * contains the QueryTestVisitor class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester;

use \PhpParser\NodeVisitorAbstract;
use \PhpParser\Node;
use \PhpParser\Node\Expr\MethodCall;
use \PhpParser\PrettyPrinter\Standard;

/**
 * inspects a node from \PhpParser
 *
 * @author          David Lienhard <david@lienhard.win>
 * @copyright       David Lienhard
 */
class Visitor extends NodeVisitorAbstract
{
    /**
     * list of all the queries found
     * @var     array
     */
    public array $queries = [];

    /**
     * enters a \PhpParser node and adds the content to the list if its a db->query()
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           Node                $node       the node to inspect
     * @return          null|int|Node                   Replacement node (or special return value)
     */
    public function enterNode(Node $node) : null | int | Node
    {
        if ($node instanceof MethodCall) {
            if (($node->var->name ?? "") === "db" && ($node->name->name ?? "") === "query") {
                $this->queries[] = [
                    "line" => $node->name->getLine() ?? 0,
                    "data" => explode("\n", (new Standard)->prettyPrint($node->args))
                ];
            }
        }

        return null;
    }
}
