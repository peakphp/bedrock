<?php

namespace Peak\Bedrock\View\Form;

use Peak\Common\Collection;
use \Exception;

class FormBuilder extends Collection
{
    /**
     * Form object
     * @var \Peak\Bedrock\View\Form\Form
     */
    protected $form;

    /**
     * Form Validation
     * @var \Peak\Bedrock\View\Form\FormValidation
     */
    protected $form_validation;

    /**
     * Constructor
     *
     * @param Form|null $form
     */
    public function __construct(Form $form = null)
    {
        $this->form = $form;
        $this->form_validation = new FormValidation($this);
        $this->init();
    }

    /**
     * Custom stuff to do on launch
     */
    public function init()
    {
    }

    /**
     * Get a html control
     *
     * @param  object|string $arg1
     * @param  string $arg2
     * @return object
     */
    public function control($arg1, $arg2 = null)
    {
        if (is_string($arg1) && !isset($arg2)) {
            $name = $arg1;
            $form = $this->form;
        } else {
            $form = $arg1;
            $name = $arg2;

            if (!$form instanceof Form) {
                throw new Exception(__CLASS__.': when using 2 params with control(), first argument must be an instance of Peak\Bedrock\View\Form\Form');
            }
        }

        if (!isset($this->items[$name])) {
            trigger_error('Field '.$name.' not found');
            return null;
        }

        // call preload(if specified) only when we need the form control
        if (array_key_exists('preload', $this->items[$name])) {
            $method = $this->items[$name]['preload'];
            $this->$method($this->items[$name]);
        }

        return $form->control(
            $this->items[$name]['type'],
            $name,
            $this->items[$name]['settings']
        );
    }

    /**
     * Validate the form
     *
     * @param  array $data
     * @return bool
     */
    public function validate($data = null)
    {
        if (!isset($data)) {
            $data = $this->form->getData();
        }
        $pass = $this->form_validation->validate($data);
        $this->setErrors($this->getErrors());
        return $pass;
    }

    /**
     * See Form::setData()
     *
     * @return $this
     */
    public function setData($data)
    {
        if (!isset($this->form)) {
            throw new Exception('FormBuilder has no Form setted');
        }

        $this->form->setData($data);
        return $this;
    }

    /**
     * See Form::setErrors()
     *
     * @return $this
     */
    public function setErrors($errors)
    {
        if (!isset($this->form)) {
            throw new Exception('FormBuilder has no Form setted');
        }

        $this->form->setErrors($errors);
        return $this;
    }

    /**
     * Get form validation errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->form_validation->getErrors();
    }
}