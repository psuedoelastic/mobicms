<?php
/*
 * mobiCMS Content Management System (http://mobicms.net)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://mobicms.net mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 * @license     LICENSE.md (see attached file)
 */

namespace Mobicms\Form;

use Mobicms\Captcha\Captcha;
use Mobicms\Environment\Request;

/**
 * Class Form
 *
 * @package Mobicms\Form
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.1.0.0 2015-02-01
 */
class Form
{
    private $form = [];
    private $fields = [];
    private $rules = [];
    private $validate = true;
    private $requestObject;
    public $input;

    public $validationToken = true;
    public $submitNames = [];
    public $isSubmitted = false;
    public $isValid = false;
    public $output = [];

    public $infoMessages = '<div class="alert %s">%s</div>';
    public $confirmation = false;
    public $continueLink;
    public $successMessage;
    public $errorMessage;

    public function __construct(array $option)
    {
        $this->form = $option;

        if (!isset($this->form['name'])) {
            $this->form['name'] = 'form';
        }

        $this->requestObject = new Request;
        if (isset($option['method']) && $option['method'] == 'get') {
            $this->form['method'] = 'get';
            $this->input = $this->requestObject->getQuery();
        } else {
            $this->form['method'] = 'post';
            $this->input = $this->requestObject->getPost();
        }

        $this->successMessage = __('data_saved');
        $this->errorMessage = __('errors_occurred');
    }

    public function __toString()
    {
        return $this->display();
    }

    /**
     * Adding form elements
     *
     * @param string $type   Тип добавляемого элемента
     * @param string $name   Имя элемента
     * @param array  $option Дополнительные параметры
     * @return $this
     */
    public function element($type, $name, array $option = [])
    {
        if ($type == 'submit') {
            $this->submitNames[] = $name;
        } elseif ($type == 'file') {
            $this->form['enctype'] = true;
        } elseif ($type == 'textarea' && !isset($option['rows'])) {
            $option['rows'] = \App::user()->settings['field_h'];
        }

        $option['type'] = $type;
        $option['name'] = $name;
        $this->fields[$name] = $option;

        unset($option);

        return $this;
    }

    /**
     * Adding Title
     *
     * @param string      $title
     * @param null|string $class
     * @return $this
     */
    public function title($title, $class = null)
    {
        $this->fields[] =
            [
                'type'    => 'html',
                'content' => '<div class="'.($class === null ? 'form-title' : $class).'">'.htmlspecialchars($title).'</div>'
            ];

        return $this;
    }

    /**
     * Adding HTML code
     * The string is not processed and transmitted in the form as it is.
     *
     * @param string $html
     * @return $this
     */
    public function html($html)
    {
        $this->fields[] =
            [
                'type'    => 'html',
                'content' => $html
            ];

        return $this;
    }

    /**
     * Adding a divider
     *
     * @param int $height
     * @return $this
     */
    public function divider($height = 24)
    {
        $this->fields[] =
            [
                'type'    => 'html',
                'content' => '<div style="height: '.$height.'px; clear: both"></div>'
            ];

        return $this;
    }

    /**
     * Adding a CAPTCHA block
     *
     * @return $this
     */
    public function captcha()
    {
        $this->fields[] =
            [
                'type' => 'captcha'
            ];

        return $this;
    }

    /**
     * Adding validation rules
     *
     * @param string $field
     * @param string $type
     * @param array  $options
     * @return $this
     */
    public function validate($field, $type, $options = [])
    {
        $options['field'] = $field;
        $options['type'] = $type;
        $this->rules[] = $options;

        return $this;
    }

    /**
     * Processing form data
     *
     * @return bool
     */
    public function process()
    {
        // Checking whether the form is submitted?
        foreach ($this->submitNames as $submit) {
            if ($this->input->offsetExists($submit)) {
                if (!$this->validationToken
                    || (isset($this->input['form_token'], $_SESSION['form_token'])
                        && $this->input['form_token'] == $_SESSION['form_token'])
                ) {
                    $this->isSubmitted = true;
                    $this->isValid = true;
                }
                break;
            }
        }

        if ($this->isSubmitted) {
            // Assigns the value
            foreach ($this->fields as &$element) {
                $this->_setValues($element);
            }

            // Data Validation
            foreach ($this->rules as $validator) {
                if ($this->validate && array_key_exists($validator['field'], $this->fields)) {
                    $this->_validateField($validator, $this->fields[$validator['field']]);
                }
            }

            if ($this->isValid) {
                return true;
            }
        }

        return false;
    }

    /**
     * Заключительная сборка готовой формы
     *
     * @return string
     */
    public function display()
    {
        // Информационные сообщения об ошибке, или успехе
        $message = '';

        if ($this->infoMessages !== false && $this->isSubmitted || $this->requestObject->getQuery()->offsetExists('saved')) {
            if ($this->isValid || $this->requestObject->getQuery()->offsetExists('saved')) {
                // Сообщение об удачном сохранении данных
                $message = sprintf($this->infoMessages, 'alert-success', $this->successMessage);

                if ($this->confirmation) {
                    // Если задано отдельное окно подтверждения
                    $message .= '<a class="btn btn-primary" href="'.($this->continueLink === null ? \App::cfg()->sys->homeurl : $this->continueLink).'">'.__('continue').'</a>';

                    return $message;
                }
            } else {
                // Сообщение, что имеются ошибки
                $message = sprintf($this->infoMessages, 'alert-danger', $this->errorMessage);
            }
        }

        $out = [];

        foreach ($this->fields as &$element) {
            // Создаем элемент формы
            switch ($element['type']) {
                case 'html':
                    $out[] = $element['content'];
                    break;

                case 'captcha':
                    $captcha = new Captcha;
                    $code = $captcha->generateCode();
                    $_SESSION['captcha'] = $code;
                    $out[] = '<img alt="'.__('captcha_help').'" width="'.$captcha->width.'" height="'.$captcha->height.'" src="'.$captcha->generateImage($code).'"/>';
                    break;

                default:
                    if ($this->isSubmitted && isset($element['reset_value'])) {
                        $element['value'] = $element['reset_value'];
                    }
                    $out[] = (new Fields($element))->display();
            }
        }

        // Добавляем токен валидации
        if ($this->validationToken) {
            if (!isset($_SESSION['form_token'])) {
                $_SESSION['form_token'] = \Functions::generateToken();
            }

            $out[] = (new Fields(['type' => 'hidden', 'name' => 'form_token', 'value' => $_SESSION['form_token']]))->display();
        }

        return sprintf("\n".$message."\n".'<form role="form" name="%s" method="%s"%s%s%s>%s</form>'."\n",
            $this->form['name'],
            $this->form['method'],
            (isset($this->form['action']) ? ' action="'.$this->form['action'].'"' : ''),
            (isset($this->form['enctype']) ? ' enctype="multipart/form-data"' : ''),
            (isset($this->form['class']) ? ' class="'.$this->form['class'].'"' : ''),
            "\n".implode("\n", $out)."\n"
        );
    }

    /**
     * Присвоение полям формы значений value после Submit
     *
     * @param array $option
     */
    private function _setValues(array &$option)
    {
        switch ($option['type']) {
            case 'html':
                break;

            case 'text':
            case 'password':
            case 'hidden':
            case 'textarea':
                if ($this->input->offsetExists($option['name'])) {
                    $option['value'] = trim($this->input[$option['name']]);
                    $this->input->offsetUnset($option['name']);

                    // Применяем фильтры
                    if (isset($option['filter'])) {
                        $this->_filter($option);
                    }

                    if (isset($option['required']) && empty($option['value'])) {
                        // Проверка на обязательное поле
                        $option['error'] = __('error_empty_field');
                        $this->validate = false;
                        $this->isValid = false;
                    }

                    $this->output[$option['name']] = $option['value'];
                } else {
                    $this->isValid = false;
                }
                break;

            case 'radio':
                if (isset($this->input[$option['name']], $option['items'])) {
                    if (array_key_exists($this->input[$option['name']], $option['items'])) {
                        $option['checked'] = trim($this->input[$option['name']]);
                        $this->output[$option['name']] = $option['checked'];
                        $this->input->offsetUnset($option['name']);
                    } else {
                        $this->isValid = false;
                    }
                }
                break;

            case 'select':
                if (isset($this->input[$option['name']], $option['items'])) {
                    $allow = true;

                    if (isset($option['multiple']) && $option['multiple']) {
                        foreach ($this->input[$option['name']] as $val) {
                            if (!array_key_exists($val, $option['items'])) {
                                $allow = false;
                                break;
                            }
                        }
                    } else {
                        if (!array_key_exists($this->input[$option['name']], $option['items'])) {
                            $allow = false;
                        }
                    }

                    if ($allow) {
                        $option['selected'] = $this->input[$option['name']];
                        $this->output[$option['name']] = $option['selected'];
                        $this->input->offsetUnset($option['name']);
                    } else {
                        $this->isValid = false;
                    }
                }
                break;

            case 'checkbox':
                if ($this->input->offsetExists($option['name'])) {
                    $this->input->offsetUnset($option['name']);
                    $option['checked'] = 1;
                    $this->output[$option['name']] = 1;
                } else {
                    unset($option['checked']);
                    $this->output[$option['name']] = 0;
                }
                break;
        }
    }

    /**
     * Фильтрация значений полей формы после Submit
     *
     * @param $option
     */
    private function _filter(&$option)
    {
        $min = isset($option['filter']['min']) ? intval($option['filter']['min']) : false;
        $max = isset($option['filter']['max']) ? intval($option['filter']['max']) : false;

        switch ($option['filter']['type']) {
            case 'str':
            case 'string':
                if (isset($option['filter']['regexp_search'])) {
                    $replace = isset($option['filter']['regexp_replace']) ? $option['filter']['regexp_replace'] : '';
                    $option['value'] = preg_replace($option['filter']['regexp_search'], $replace, $option['value']);
                }

                if ($max !== false) {
                    $option['value'] = mb_substr($option['value'], 0, $max);
                }
                break;

            case 'int':
            case 'integer':
                $option['value'] = intval($option['value']);

                if ($min !== false && $option['value'] < $min) {
                    $option['value'] = $min;
                }

                if ($max !== false && $option['value'] > $max) {
                    $option['value'] = $max;
                }
                break;

            default:
                $option['error'] = 'Unknown filter: '.$option['filter']['type'];
        }
    }

    /**
     * Валидация полей формы
     *
     * @param array $validator
     * @param array $option
     * @uses Validate
     */
    private function _validateField(array $validator, array &$option)
    {
        if (isset($validator['valid']) && $validator['valid'] && !$this->isValid) {
            return;
        }

        if ($validator['type'] == 'compare') {
            if (isset($this->output[$validator['compare_field']])) {
                $validator['compare_value'] = $this->output[$validator['compare_field']];
            } else {
                $option['error'] = 'ERROR: compare field does not exist';
            }
        }

        $check = new Validate($validator['type'], $option['value'], $validator);

        if ($check->is !== true) {
            if (isset($option['error']) && !empty($option['error'])) {
                if (is_array($option['error'])) {
                    $option['error'] = array_merge($option['error'], $check->error);
                } else {
                    $option['error'] = $option['error'].'<br/>'.implode('<br/>', $check->error);
                }
            } else {
                $option['error'] = $check->error;
            }

            $this->isValid = false;

            if (isset($validator['continue']) && !$validator['continue']) {
                $this->validate = false;
            }
        }
        unset($check);
    }
}
