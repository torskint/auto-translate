<?php

namespace Torskint\AutoTranslate\Helpers;

use Illuminate\Console\Command;

abstract class AbstractTranslateHelper
{

	protected Command $command;
	
	public function setCommand(Command $command)
	{
		$this->command = $command;
	}
}