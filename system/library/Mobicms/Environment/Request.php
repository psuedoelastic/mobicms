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

namespace Mobicms\Environment;

use Mobicms\Utility\Parameters;

/**
 * Class Request
 *
 * @package Mobicms\Environment
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 *          This class implements the ideas of the Zend Framework
 *          <https://github.com/zendframework/Component_ZendHttp>
 * @version v.1.0.0 2015-02-10
 */
class Request
{
    private $queryParams = null;
    private $postParams = null;
    private $fileParams = null;

    /**
     * Return the parameter container responsible for query parameters or a single query parameter
     *
     * @param string|null $name    Parameter name to retrieve, or null to get the whole container.
     * @param mixed|null  $default Default value to use when the parameter is missing.
     * @return \ArrayObject|mixed
     */
    public function getQuery($name = null, $default = null)
    {
        if ($this->queryParams === null) {
            $this->queryParams = new Parameters(filter_input_array(INPUT_GET));
        }

        if ($name === null) {
            return $this->queryParams;
        }

        return $this->queryParams->get($name, $default);
    }

    /**
     * Return the parameter container responsible for post parameters or a single post parameter.
     *
     * @param string|null $name    Parameter name to retrieve, or null to get the whole container.
     * @param mixed|null  $default Default value to use when the parameter is missing.
     * @return \ArrayObject|mixed
     */
    public function getPost($name = null, $default = null)
    {
        if ($this->postParams === null) {
            $this->postParams = new Parameters(filter_input_array(INPUT_POST));
        }

        if ($name === null) {
            return $this->postParams;
        }

        return $this->postParams->get($name, $default);
    }

    /**
     * Return the parameter container responsible for file parameters or a single file.
     *
     * @param string|null $name    Parameter name to retrieve, or null to get the whole container.
     * @param mixed|null  $default Default value to use when the parameter is missing.
     * @return \ArrayObject|mixed
     */
    public function getFiles($name = null, $default = null)
    {
        if ($this->fileParams === null) {
            $this->fileParams = new Parameters(isset($_FILES) ? $this->mapPhpFiles() : null);
        }

        if ($name === null) {
            return $this->fileParams;
        }

        return $this->fileParams->get($name, $default);
    }

    /**
     * Convert PHP superglobal $_FILES into more sane parameter=value structure
     * This handles form file input with brackets (name=files[])
     *
     * @return array
     */
    private function mapPhpFiles()
    {
        $files = [];

        foreach ($_FILES as $fileName => $fileParams) {
            $files[$fileName] = [];

            foreach ($fileParams as $param => $data) {
                if (!is_array($data)) {
                    $files[$fileName][$param] = $data;
                } else {
                    foreach ($data as $i => $v) {
                        $this->mapPhpFileParam($files[$fileName], $param, $i, $v);
                    }
                }
            }
        }

        return $files;
    }

    /**
     * @param array        $array
     * @param string       $paramName
     * @param int|string   $index
     * @param string|array $value
     */
    private function mapPhpFileParam(&$array, $paramName, $index, $value)
    {
        if (!is_array($value)) {
            $array[$index][$paramName] = $value;
        } else {
            foreach ($value as $i => $v) {
                $this->mapPhpFileParam($array[$index], $paramName, $i, $v);
            }
        }
    }
}