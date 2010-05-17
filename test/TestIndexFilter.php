<?php
#########################################################################
# Copyright (C) 2010  Markus Malkusch <markus@malkusch.de>              #
#                                                                       #
# This program is free software: you can redistribute it and/or modify  #
# it under the terms of the GNU General Public License as published by  #
# the Free Software Foundation, either version 3 of the License, or     #
# (at your option) any later version.                                   #
#                                                                       #
# This program is distributed in the hope that it will be useful,       #
# but WITHOUT ANY WARRANTY; without even the implied warranty of        #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         #
# GNU General Public License for more details.                          #
#                                                                       #
# You should have received a copy of the GNU General Public License     #
# along with this program.                                              #
# If not, see <http://php-autoloader.malkusch.de/en/license/>.          #
#########################################################################


require_once dirname(__FILE__) . "/../Autoloader.php";


/**
 * AutoloaderIndexFilter cases.
 * 
 * Copyright (C) 2010  Markus Malkusch <markus@malkusch.de>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Autoloader
 * @subpackage test
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 */

class TestIndexFilter extends PHPUnit_Framework_TestCase {


    /**
     * @dataProvider provideTestRelativePathBasePath
     */
    public function testRelativePathBasePath($expectedBasePath, AutoloaderIndexFilter_RelativePath $filter) {
        $this->assertEquals(
            realpath($expectedBasePath),
            $filter->getBasePath()
        );
    }


    /**
     * @return array
     */
    public function provideTestRelativePathBasePath() {
        return array(
            array(
                dirname(__FILE__) . '/../',
                new AutoloaderIndexFilter_RelativePath()
            ),
            array(
                dirname(__FILE__),
                new AutoloaderIndexFilter_RelativePath(dirname(__FILE__))
            ),
        );
    }


    /**
     * @expectedException AutoloaderException_Index_Filter_RelativePath_InvalidBasePath
     * @dataProvider provideTestFailRelativePathBasePath
     */
    public function testFailRelativePathBasePath($basePath) {
        new AutoloaderIndexFilter_RelativePath($basePath);
    }


    /**
     * @return array
     */
    public function provideTestFailRelativePathBasePath() {
        return array(
            array(
                dirname(__FILE__) . '/' . uniqid(),
            )
        );
    }


    /**
     * @dataProvider provideTestRelativePath
     */
    public function testSetRelativePath($relativePath, $absolutePath) {
        $filter = new AutoloaderIndexFilter_RelativePath();
        $filteredPath = $filter->filterSetPath($absolutePath);
        $this->assertEquals($relativePath, $filteredPath);
    }


    /**
     * @dataProvider provideTestRelativePath
     */
    public function testGetRelativePath($relativePath, $absolutePath) {
        $filter = new AutoloaderIndexFilter_RelativePath();
        $filteredPath = $filter->filterGetPath($relativePath);

        $pathArray = explode(DIRECTORY_SEPARATOR, $filteredPath);
        while($parent = array_search('..', $pathArray)) {
            unset($pathArray[$parent], $pathArray[$parent - 1]);
            $pathArray = array_values($pathArray);

        }
        $filteredPath = implode(DIRECTORY_SEPARATOR, $pathArray);

        $this->assertEquals($absolutePath, $filteredPath);
    }


    public function provideTestRelativePath() {
        return array(
            array(
                '../../Foo',
                realpath(dirname(__FILE__) . "/../../../") . "/Foo"
            ),
            array(
                '../../Foo/Bar',
                realpath(dirname(__FILE__) . "/../../../") . "/Foo/Bar"
            ),
            array(
                '../Foo',
                realpath(dirname(__FILE__) . "/../../") . "/Foo"
            ),
            array(
                '../Foo/Bar',
                realpath(dirname(__FILE__) . "/../../") . "/Foo/Bar"
            ),
            array(
                'Foo',
                realpath(dirname(__FILE__) . "/..") . "/Foo"
            ),
            array(
                'Foo/Bar',
                realpath(dirname(__FILE__) . "/..") . "/Foo/Bar"
            ),
            array(
                'test/Foo',
                dirname(__FILE__) . "/Foo"
            ),
            array(
                'test/Foo/Bar',
                dirname(__FILE__) . "/Foo/Bar"
            )
        );
    }
	
	
}