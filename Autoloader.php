<?php
/**
 * The Autoloader works out of the box as simple as possible. You have
 * nothing more to do than require this file. Don't bother the time it
 * consumes when it's called the first time. Let it build its index.
 * The second time it will run as fast as light.
 * 
 * The simplest and probably most common usecase shows this example:
 * 
 * ,--<index.php>--
 * | <?php require dirname(__FILE__) . "/autoloader/Autoloader.php";
 * | $myObject = new MyClass();
 * |
 * |--<classes/MyClass.php>--
 * | <?php class MyClass extends MyParentClass { .. }
 * |
 * |--<classes/MyParentClass.php>--
 * | <?php class MyParentClass { .. }
 * `--
 * 
 * As you can see it's only necessary to require this file once.
 * If this is done in the document root of your classes (index.php in
 * this case) the Autoloader is already configured. After requiring
 * this file you don't have to worry where your classes reside.
 * 
 * If you have the possibility to enable PHP's tokenizer you should do
 * this. Otherwise the Autoloader has to use a Parser based on PCRE
 * which is not as reliable as PHP's tokenizer.
 * 
 * The Autoloader assumes that a class name is unique. If you have classes with
 * equal names the behaviour is undefined.
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
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 */

require_once dirname(__FILE__) . "/classes/Autoloader.php";

Autoloader::getDefaultInstance()->addCallersPath();
Autoloader::getDefaultInstance()->register();

