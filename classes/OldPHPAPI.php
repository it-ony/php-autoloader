<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file defines the class OldPHPAPI.
 *
 * PHP version 5
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 * If not, see <http://php-autoloader.malkusch.de/en/license/>.
 *
 * @category  Autoloader
 * @package   Base
 * @author    Markus Malkusch <markus@malkusch.de>
 * @copyright 2009 - 2010 Markus Malkusch
 * @license   http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version   SVN: $Id$
 * @link      http://php-autoloader.malkusch.de/en/
 */

/**
 * OldPHPAPI implements missing PHP5 functions and constants.
 *
 * A missing function is implemented by a static method of this class with
 * a @define annotation.
 *
 * @category  Autoloader
 * @package   Base
 * @author    Markus Malkusch <markus@malkusch.de>
 * @copyright 2009 - 2010 Markus Malkusch
 * @license   http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version   Release: 1.8
 * @link      http://php-autoloader.malkusch.de/en/
 */
class OldPHPAPI
{

    /**
     * checkAPI() will define all required functions and constants.
     *
     * @return void
     */
    public function checkAPI()
    {
        // The constants are defined.
        $this->_define('T_NAMESPACE');
        $this->_define('T_NS_SEPARATOR');
        $this->_define('E_USER_DEPRECATED', E_USER_WARNING);

        // Every static public method with a @define annotation defines a function.
        $reflectionObject = new ReflectionObject($this);
        $methods = $reflectionObject->getMethods(
            ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC
        );
        foreach ($methods as $method) {
            // The method comment is parsed for the @define annotation
            $isAnnotated = preg_match(
                '/\s*\*\s*@define\s+(\S+)/',
                $method->getDocComment(),
                $matches
            );
            if (! $isAnnotated) {
                continue;

            }

            $function = $matches[1];

            // A function might already exist.
            if (function_exists($function)) {
                continue;

            }

            // The parameters are build.
            $parametersArray = array();
            for ($i = 0; $i < $method->getNumberOfParameters(); $i++) {
                $parametersArray[] = '$parameter' . $i;

            }
            $parameters = implode(', ', $parametersArray);

            // The function is defined.
            $definition = "function $function($parameters)
                {
                    \$parameters = func_get_args();
                    return call_user_func_array(
                        array('OldPHPAPI', '{$method->getName()}'),
                        \$parameters
                    );
                }
            ";
            eval ($definition);

        }
    }

    /**
     * _define() defines a global constant if it is not defines yet.
     *
     * $value is optional and would be name of the constant it self if omitted.
     *
     * @param String $const The constant name
     * @param String $value The optional constant value
     *
     * @return void
     */
    private function _define($const, $value = null)
    {
        if (defined($const)) {
            return;

        }
        define($const, is_null($value) ? $const : $value);
    }

    /**
     * errorGetLast() defines error_get_last().
     *
     * @define error_get_last
     * @see error_get_last()
     * @return array
     */
    public static function errorGetLast()
    {
        $message = 'Getting the last error message is not supported'
            . 'by your old PHP version.';
        return array(
            'type'      => 0,
            'message'   => $message,
            'file'      => '/dev/null',
            'line'      => 0
        );
    }

    /**
     * sysGetTempDir() defines sys_get_temp_dir().
     *
     * @define sys_get_temp_dir
     * @see sys_get_temp_dir()
     * @throws LogicException It's not expected to fail
     * @return String
     */
    public static function sysGetTempDir()
    {
        $envVars = array('TMP', 'TEMP', 'TMPDIR');
        foreach ($envVars as $envVar) {
            $temp = getenv($envVar);
            if (! empty($temp)) {
                return $temp;

            }

        }

        $temp = tempnam(__FILE__, '');
        if (file_exists($temp)) {
            unlink($temp);
            return dirname($temp);

        }
        throw new LogicException("sys_get_temp_dir() failed.");
    }

    /**
     * parseIniString() defines parse_ini_string().
     *
     * @param String $data The parsable ini string
     *
     * @define parse_ini_string
     * @see parse_ini_string()
     * @see AutoloaderIndex_IniFile
     * @return Array
     */
    public static function parseIniString($data)
    {
        $file = tempnam(sys_get_temp_dir(), 'parse_ini_string');
        file_put_contents($file, $data);
        $iniData = parse_ini_file($file);
        unlink($file);
        return $iniData;
    }

    /**
     * strGetcsv() defines str_getcsv().
     *
     * @param String $data The parsable csv string
     *
     * @define str_getcsv
     * @see str_getcsv()
     * @see AutoloaderIndex_CSV
     * @return Array
     */
    public static function strGetcsv($data)
    {
        $fp = tmpfile();
        fwrite($fp, $data);
        fseek($fp, 0);
        $csv = fgetcsv($fp);
        fclose($fp);
        return $csv;
    }

    /**
     * testFunctionNoParameters() defines test_function_no_parameters().
     *
     * test_function_no_parameters() is needed for testing this class.
     *
     * @define test_function_no_parameters
     * @see TestOldPHPAPI
     * @return bool
     */
    public static function testFunctionNoParameters()
    {
        return true;
    }

    /**
     * testFunctionWithParameters() defines test_function_with_parameters().
     *
     * test_function_with_parameters() is needed for testing this class.
     *
     * @param int $a an integer
     * @param int $b an integer
     *
     * @define test_function_with_parameters
     * @see TestOldPHPAPI
     * @return int The sum of $a plus $b
     */
    public static function testFunctionWithParameters($a, $b)
    {
        return $a + $b;
    }

}