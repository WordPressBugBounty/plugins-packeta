<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace Packetery\Latte\Bridges\Tracy;

use Packetery\Latte;
use Packetery\Tracy;
use Packetery\Tracy\BlueScreen;
use Packetery\Tracy\Helpers;
/**
 * BlueScreen panels for Tracy 2.x
 */
class BlueScreenPanel
{
    public static function initialize(?BlueScreen $blueScreen = null) : void
    {
        $blueScreen = $blueScreen ?? Tracy\Debugger::getBlueScreen();
        $blueScreen->addPanel([self::class, 'renderError']);
        $blueScreen->addAction([self::class, 'renderUnknownMacro']);
        if (\version_compare(Tracy\Debugger::VERSION, '2.9.0', '>=') && \version_compare(Tracy\Debugger::VERSION, '3.0', '<')) {
            Tracy\Debugger::addSourceMapper([self::class, 'mapLatteSourceCode']);
            $blueScreen->addFileGenerator(function (string $file) {
                return \substr($file, -6) === '.latte' ? "{block content}\n\$END\$" : null;
            });
        }
    }
    public static function renderError(?\Throwable $e) : ?array
    {
        if ($e instanceof \Packetery\Latte\CompileException && $e->sourceName) {
            return ['tab' => 'Template', 'panel' => (\preg_match('#\\n|\\?#', $e->sourceName) ? '' : '<p>' . (@\is_file($e->sourceName) ? '<b>File:</b> ' . Helpers::editorLink($e->sourceName, $e->sourceLine) : '<b>' . \htmlspecialchars($e->sourceName . ($e->sourceLine ? ':' . $e->sourceLine : '')) . '</b>') . '</p>') . '<pre class="code tracy-code"><div>' . BlueScreen::highlightLine(\htmlspecialchars($e->sourceCode, \ENT_IGNORE, 'UTF-8'), $e->sourceLine) . '</div></pre>'];
        } elseif ($e && ($file = $e->getFile()) && \version_compare(Tracy\Debugger::VERSION, '2.9.0', '<') && ($mapped = self::mapLatteSourceCode($file, $e->getLine()))) {
            return ['tab' => 'Template', 'panel' => '<p><b>File:</b> ' . Helpers::editorLink($mapped['file'], $mapped['line']) . '</p>' . ($mapped['line'] ? BlueScreen::highlightFile($mapped['file'], $mapped['line']) : '')];
        }
        return null;
    }
    public static function renderUnknownMacro(?\Throwable $e) : ?array
    {
        if ($e instanceof \Packetery\Latte\CompileException && $e->sourceName && @\is_file($e->sourceName) && (\preg_match('#Unknown tag (\\{\\w+)\\}, did you mean (\\{\\w+)\\}\\?#A', $e->getMessage(), $m) || \preg_match('#Unknown attribute (n:\\w+), did you mean (n:\\w+)\\?#A', $e->getMessage(), $m))) {
            return ['link' => Helpers::editorUri($e->sourceName, $e->sourceLine, 'fix', $m[1], $m[2]), 'label' => 'fix it'];
        }
        return null;
    }
    /** @return array{file: string, line: int, label: string, active: bool} */
    public static function mapLatteSourceCode(string $file, int $line) : ?array
    {
        if (!\strpos($file, '.latte--')) {
            return null;
        }
        $lines = \file($file);
        if (!\preg_match('#^/\\*\\* source: (\\S+\\.latte)#m', \implode('', \array_slice($lines, 0, 10)), $m) || !@\is_file($m[1])) {
            return null;
        }
        $file = $m[1];
        $line = $line && \preg_match('#/\\* line (\\d+) \\*/#', $lines[$line - 1], $m) ? (int) $m[1] : 0;
        return ['file' => $file, 'line' => $line, 'label' => 'Latte', 'active' => \true];
    }
}
