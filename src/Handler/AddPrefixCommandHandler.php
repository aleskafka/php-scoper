<?php

/*
 * This file is part of the webmozart/php-scoper package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\PhpScoper\Handler;

use PhpParser\ParserFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\IO\IO;
use Webmozart\PhpScoper\Exception\ParsingException;
use Webmozart\PhpScoper\ScoperOptions;
use Webmozart\PhpScoper\Scoper;

/**
 * Handles the "add-prefix" command.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class AddPrefixCommandHandler
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Finder
     */
    private $finder;


    public function __construct()
    {
        $this->filesystem = new Filesystem;
        $this->finder = new Finder;
    }

    /**
     * Handles the "add-prefix" command.
     *
     * @param Args $args The console arguments.
     * @param IO   $io   The I/O.
     *
     * @return int Returns 0 on success and a positive integer on error.
     */
    public function handle(Args $args, IO $io)
    {
        global $declaredClasses, $declaredInterfaces;

        $options = new ScoperOptions;
        $options->prefix = rtrim($args->getArgument('prefix'), '\\');
        $options->declaredClasses = $declaredClasses;
        $options->declaredInterfaces = $declaredInterfaces;

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $scoper = new Scoper($parser, $options);

        foreach ($args->getArgument('path') as $path) {

            if (!$this->filesystem->isAbsolutePath($path)) {
                $path = getcwd().DIRECTORY_SEPARATOR.$path;
            }

            if (is_dir($path)) {
                $this->finder->files()->name('*.php')->in($path);

                foreach ($this->finder as $file) {
                    $this->scopeFile($scoper, $file->getPathName(), $io);
                }

            } elseif (is_file($path)) {
                $this->scopeFile($scoper, $path, $io);
            }
        }

        return 0;
    }

    private function scopeFile(Scoper $scoper, $path, IO $io)
    {
        $fileContent = file_get_contents($path);
        try {
            $scoppedContent = $scoper->scope($fileContent);
            $this->filesystem->dumpFile($path, $scoppedContent);
            $io->writeLine(sprintf('Scoping %s. . . Success', $path));
        } catch (ParsingException $exception) {
            $io->errorLine(sprintf('Scoping %s. . . Fail', $path));
        }
    }
}
