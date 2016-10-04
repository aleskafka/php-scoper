<?php

/*
 * This file is part of the webmozart/php-scoper package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\PhpScoper;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Webmozart\PhpScoper\Exception\ParsingException;
use Webmozart\PhpScoper\NodeVisitor\FullyQualifiedNamespaceUseScoperNodeVisitor;
use Webmozart\PhpScoper\NodeVisitor\NamespaceScoperNodeVisitor;
use Webmozart\PhpScoper\NodeVisitor\UseNamespaceScoperNodeVisitor;

class Scoper
{
    /** @var Parser */
    private $parser;

    /** @var ScoperOptions */
    private $options;


    public function __construct(Parser $parser, ScoperOptions $options)
    {
        $this->parser = $parser;
        $this->options = $options;
    }

    /**
     * @param $content
     * @param $prefix
     *
     * @return string
     */
    public function scope($content)
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NamespaceScoperNodeVisitor(clone $this->options));
        $traverser->addVisitor(new UseNamespaceScoperNodeVisitor(clone $this->options));
        $traverser->addVisitor(new FullyQualifiedNamespaceUseScoperNodeVisitor(clone $this->options));

        try {
            $statements = $this->parser->parse($content);
        } catch (Error $error) {
            throw new ParsingException($error->getMessage());
        }

        $statements = $traverser->traverse($statements);

        $prettyPrinter = new Standard;

        return $prettyPrinter->prettyPrintFile($statements)."\n";
    }
}
