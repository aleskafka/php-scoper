<?php

namespace Webmozart\PhpScoper\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeVisitorAbstract;
use Webmozart\PhpScoper\ScoperOptions;

class UseNamespaceScoperNodeVisitor extends NodeVisitorAbstract
{
    /** @var ScoperOptions */
    private $options;

    public function __construct(ScoperOptions $options)
    {
        $this->options = $options;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof UseUse) {
            if (in_array((string) $node->name, $this->options->declaredClasses)) {
                return NULL;

            } elseif (in_array((string) $node->name, $this->options->declaredInterfaces)) {
                return NULL;
            }

            $node->name = Name::concat($this->options->prefix, $node->name);
        }
    }
}
