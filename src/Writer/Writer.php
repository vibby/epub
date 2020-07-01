<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vibby\EPub\Writer;

use Vibby\EPub\Definition\Package;
use Vibby\EPub\Exception\DuplicateItemException;
use Vibby\EPub\Exception\InvalidArgumentException;

class Writer
{
	private $package;
	
	public function __construct(Package $package)
	{
		$this->package = $package;
	}
	
	
}