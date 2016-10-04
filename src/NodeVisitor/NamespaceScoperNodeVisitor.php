<?php

namespace Webmozart\PhpScoper\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use Webmozart\PhpScoper\ScoperOptions;

class NamespaceScoperNodeVisitor extends NodeVisitorAbstract
{
    /** @var ScoperOptions */
    private $options;

    public function __construct(ScoperOptions $options)
    {
        $this->options = $options;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_ && null !== $node->name) {
            $node->name = Name::concat($this->options->prefix, $node->name);
        }
    }
}
