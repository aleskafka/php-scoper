<?php

namespace Webmozart\PhpScoper\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeVisitorAbstract;
use Webmozart\PhpScoper\ScoperOptions;

class FullyQualifiedNamespaceUseScoperNodeVisitor extends NodeVisitorAbstract
{
    /** @var ScoperOptions */
    private $options;

    public function __construct(ScoperOptions $options)
    {
        $this->options = $options;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof FullyQualified) {
            if (in_array((string) $node, $this->options->declaredClasses)) {
                return NULL;

            } elseif (in_array((string) $node, $this->options->declaredInterfaces)) {
                return NULL;
            }

            return new Name(Name::concat($this->options->prefix, (string) $node));
        }
    }
}
