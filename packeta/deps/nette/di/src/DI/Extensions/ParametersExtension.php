<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace Packetery\Nette\DI\Extensions;

use Packetery\Nette;
use Packetery\Nette\DI\Container;
use Packetery\Nette\DI\DynamicParameter;
use Packetery\Nette\DI\Helpers;
use Packetery\Nette\PhpGenerator\Method;
/**
 * Parameters.
 */
final class ParametersExtension extends \Packetery\Nette\DI\CompilerExtension
{
    /** @var string[] */
    public $dynamicParams = [];
    /** @var string[][] */
    public $dynamicValidators = [];
    /** @var array */
    private $compilerConfig;
    public function __construct(array &$compilerConfig)
    {
        $this->compilerConfig =& $compilerConfig;
    }
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $params = $this->config;
        foreach ($this->dynamicParams as $key) {
            $params[$key] = new DynamicParameter('$this->getParameter(' . \var_export($key, \true) . ')');
        }
        $builder->parameters = Helpers::expand($params, $params, \true);
        // expand all except 'services'
        $slice = \array_diff_key($this->compilerConfig, ['services' => 1]);
        $slice = Helpers::expand($slice, $builder->parameters);
        $this->compilerConfig = $slice + $this->compilerConfig;
    }
    public function afterCompile(\Packetery\Nette\PhpGenerator\ClassType $class)
    {
        $builder = $this->getContainerBuilder();
        $dynamicParams = \array_fill_keys($this->dynamicParams, \true);
        foreach ($builder->parameters as $key => $value) {
            $value = [$value];
            \array_walk_recursive($value, function ($val) use(&$dynamicParams, $key) : void {
                if ($val instanceof DynamicParameter) {
                    $dynamicParams[$key] = $dynamicParams[$key] ?? \true;
                } elseif ($val instanceof \Packetery\Nette\DI\Definitions\Statement) {
                    $dynamicParams[$key] = \false;
                }
            });
        }
        $method = Method::from([Container::class, 'getStaticParameters'])->addBody('return ?;', [\array_diff_key($builder->parameters, $dynamicParams)]);
        $class->addMember($method);
        if (!$dynamicParams) {
            return;
        }
        $resolver = new \Packetery\Nette\DI\Resolver($builder);
        $generator = new \Packetery\Nette\DI\PhpGenerator($builder);
        $method = Method::from([Container::class, 'getDynamicParameter']);
        $class->addMember($method);
        $method->addBody('switch (true) {');
        foreach ($dynamicParams as $key => $foo) {
            $value = Helpers::expand($this->config[$key] ?? null, $builder->parameters);
            try {
                $value = $generator->convertArguments($resolver->completeArguments(Helpers::filterArguments([$value])))[0];
                $method->addBody("\tcase \$key === ?: return ?;", [$key, $value]);
            } catch (\Packetery\Nette\DI\ServiceCreationException $e) {
                $method->addBody("\tcase \$key === ?: throw new \\Packetery\\Nette\\DI\\ServiceCreationException(?);", [$key, $e->getMessage()]);
            }
        }
        $method->addBody("\tdefault: return parent::getDynamicParameter(\$key);\n};");
        if ($preload = \array_keys($dynamicParams, \true, \true)) {
            $method = Method::from([Container::class, 'getParameters']);
            $class->addMember($method);
            $method->addBody('array_map([$this, \'getParameter\'], ?);', [$preload]);
            $method->addBody('return parent::getParameters();');
        }
        foreach ($this->dynamicValidators as [$param, $expected, $path]) {
            if ($param instanceof DynamicParameter) {
                $this->initialization->addBody('\\Packetery\\Nette\\Utils\\Validators::assert(?, ?, ?);', [$param, $expected, "dynamic parameter used in '" . \implode(" › ", $path) . "'"]);
            }
        }
    }
}
