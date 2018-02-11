<?php

include 'Html.php';

class FormHelper extends Html
{
	protected $model;
	protected $rules;
	protected $form = '';

	protected $fieldName     = '';
	protected $fieldLabel    = '';
	protected $fieldType     = '';
	protected $fieldRequired = false;
	protected $fieldLength   = '';
	protected $fieldOptions  = '';
	protected $fieldValue    = '';
	protected $fieldTemplate = '';

	public function __construct(Model $model = [], array $rules = [])
	{
		$this->model = $model;
		$this->rules = empty($rules) ? $this->model->rules() : $rules;
	}

	public function formStart(string $action, string $method = '', array $options = [])
	{
		$this->form = sprintf('<form method="%s" action="%s" %s >', empty($method) ? 'POST' : $method, $action, self::setTagOptions($options));
	}

	public function display(string $template = '') : string
	{
		$fieldLabel = empty($this->fieldLabel) ? ucwords(preg_replace('/([^a-zA-Z0-9])/', ' ', $this->fieldName)) : $this->fieldLabel;
		$length = empty($this->fieldLength) ? '' : sprintf('maxlength="%s"', $this->fieldLength);
		$value = empty($this->fieldValue) ? '' : sprintf('value="%s"', $this->fieldValue);

		$input = sprintf('<input type="%s" %s %s %s %s >', $this->setFieldType(), $this->setRequired(), self::setTagOptions($this->fieldOptions), $length, $value);

		if(!empty($template))
		{
			$field = str_replace('{label}', $fieldLabel, $template);
			$field = str_replace('{input}', $input, $field);
		}
		else{
			$field = $input;
		}
		return $field;
	}

	private function input()
	{

	}

	private function textarea()
	{
		
	}

	private function setRequired() : string
	{
		if(isset($this->rules[$this->fieldName]['required']) && $this->rules[$this->fieldName]['required'])
		{
			return 'required';
		}
		elseif($this->fieldRequired)
		{
			return 'required';
		}
		else{
			return '';
		}
	}

	private function setFieldType() : string
	{
		if(empty($this->fieldType))
		{
			return isset($this->rules[$this->fieldName]) ? $this->rules[$this->fieldName] : 'text';
		}
		else{
			return $type;
		}
	}

	public function field(string $fieldName, string $type = '', array $options = []) : FormHelper
	{
		$this->fieldName = $fieldName;
		$this->fieldType = $type;
		$this->fieldOptions = $options;
		return $this;
	}

	public function label(string $label) : FormHelper
	{
		$this->fieldLabel = $label;
		return $this;
	}

	public function length(int $length) : FormHelper
	{
		$this->fieldLength = $length;
		return $this;
	}

	public function required() : FormHelper
	{
		$this->fieldRequired = true;
		return $this;
	}

	public function value($value) : FormHelper
	{
		$this->fieldValue = $value;
		return $this;
	}
}