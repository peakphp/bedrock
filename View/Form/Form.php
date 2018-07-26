<?php

declare(strict_types=1);

namespace Peak\Bedrock\View\Form;

use \Exception;

/**
 * Class Form
 * @package Peak\Bedrock\View\Form
 */
class Form
{
    /**
     * Form data
     * @var array
     */
    protected $data = [];

    /**
     * Form errors
     * @var array
     */
    protected $errors = [];

    /**
     * Set also the data
     *
     * @param array $data
     * @param array $errors
     */
    public function __construct(array $data = [], array $errors = [])
    {
        $this->setData($data);
        $this->setErrors($errors);
    }
    
    /**
     * Set data
     *
     * @return $this
     */
    public function setData($data)
    {
        $d = [];
        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $d[$k] = $v;
            }
        } else {
            $d = $data;
        }

        $this->data = $d;
        return $this;
    }

    /**
     * Get all data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set errors
     *
     * @param  array $errors
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Load a form control
     *
     * @param $type
     * @param $name
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function control($type, $name, $options = [])
    {
        if (class_exists($type)) {
            $cname = $type;
        } else {
            $cname = 'Peak\Bedrock\View\Form\Control\\'.ucfirst($type);
        }

        $data = $this->get($name);
        $error = $this->getError($name);

        if (class_exists($cname)) {
            return new $cname($name, $data, $options, $error);
        } else {
            throw new Exception('Form control type '.$type.' not found');
        }
    }

    /**
     * Get a data value if exists
     *
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * Get error
     *
     * @param  string $name
     * @return string
     */
    public function getError($name)
    {
        if (array_key_exists($name, $this->errors)) {
            return $this->errors[$name];
        }
        return null;
    }
}
