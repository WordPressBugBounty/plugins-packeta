<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace Packetery\Nette\Forms\Controls;

use Packetery\Nette;
/**
 * Submittable button control.
 *
 * @property-read bool $submittedBy
 */
class SubmitButton extends Button implements \Packetery\Nette\Forms\SubmitterControl
{
    /**
     * Occurs when the button is clicked and form is successfully validated
     * @var array<callable(self, array|object): void|callable(\Packetery\Nette\Forms\Form, array|object): void|callable(array|object): void>
     */
    public $onClick = [];
    /** @var array<callable(self): void>  Occurs when the button is clicked and form is not validated */
    public $onInvalidClick = [];
    /** @var array|null */
    private $validationScope;
    /**
     * @param  string|object  $caption
     */
    public function __construct($caption = null)
    {
        parent::__construct($caption);
        $this->control->type = 'submit';
        $this->setOmitted(\true);
    }
    public function loadHttpData() : void
    {
        parent::loadHttpData();
        if ($this->isFilled()) {
            $this->getForm()->setSubmittedBy($this);
        }
    }
    /**
     * Tells if the form was submitted by this button.
     */
    public function isSubmittedBy() : bool
    {
        return $this->getForm()->isSubmitted() === $this;
    }
    /**
     * Sets the validation scope. Clicking the button validates only the controls within the specified scope.
     * @return static
     */
    public function setValidationScope(?iterable $scope)
    {
        if ($scope === null) {
            $this->validationScope = null;
        } else {
            $this->validationScope = [];
            foreach ($scope ?: [] as $control) {
                if (!$control instanceof \Packetery\Nette\Forms\Container && !$control instanceof \Packetery\Nette\Forms\Control) {
                    throw new \Packetery\Nette\InvalidArgumentException('Validation scope accepts only \\Packetery\\Nette\\Forms\\Container or \\Packetery\\Nette\\Forms\\Control instances.');
                }
                $this->validationScope[] = $control;
            }
        }
        return $this;
    }
    /**
     * Gets the validation scope.
     */
    public function getValidationScope() : ?array
    {
        return $this->validationScope;
    }
    /**
     * Fires click event.
     */
    public function click() : void
    {
        \Packetery\Nette\Utils\Arrays::invoke($this->onClick, $this);
    }
    public function getControl($caption = null) : \Packetery\Nette\Utils\Html
    {
        $scope = [];
        foreach ((array) $this->validationScope as $control) {
            $scope[] = $control->lookupPath(\Packetery\Nette\Forms\Form::class);
        }
        return parent::getControl($caption)->addAttributes(['formnovalidate' => $this->validationScope !== null, 'data-nette-validation-scope' => $scope ?: null]);
    }
}
