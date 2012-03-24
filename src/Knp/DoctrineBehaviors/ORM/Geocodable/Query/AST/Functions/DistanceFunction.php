<?php

namespace Knp\DoctrineBehaviors\ORM\Geocodable\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

/**
 * DQL function for calculating distances between two points
 *
 *   DISTANCE(entity.point, POINT(:param))
 */
class DistanceFunction extends FunctionNode
{
    private $firstArg;
    private $secondArg;

    /**
     * Returns SQL representation of this function.
     *
     * @param SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('%s <@> %s',
            $this->firstArg->dispatch($sqlWalker),
            $this->secondArg->dispatch($sqlWalker)
        );
    }

    /**
     * Parses DQL function.
     *
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstArg = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondArg = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
