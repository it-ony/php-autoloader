<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file defines the AutoloaderIndexFilter.
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
 * @category   Autoloader
 * @package    Index
 * @subpackage Filter
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id$
 * @link       http://php-autoloader.malkusch.de/en/
 */

/**
 * These classes must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndexGetFilter',
    dirname(__FILE__) . '/AutoloaderIndexGetFilter.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderIndexSetFilter',
    dirname(__FILE__) . '/AutoloaderIndexSetFilter.php'
);

/**
 * When a path is read from an AutoloaderIndex or set, this filter is applied on
 * that path.
 *
 * @category   Autoloader
 * @package    Index
 * @subpackage Filter
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.8
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderIndex::addFilter()
 * @see        AutoloaderIndex::getPath()
 * @see        AutoloaderIndex::setPath()
 */
interface AutoloaderIndexFilter
    extends AutoloaderIndexGetFilter, AutoloaderIndexSetFilter
{

}