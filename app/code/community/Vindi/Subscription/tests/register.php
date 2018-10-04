<?php

\Magium\Cli\CommandLoader::addCommandDir('Vindi\Cli\Command', realpath(__DIR__ . '/Vindi/Cli/Command'));
\Magium\Cli\Command\ListElements::addDirectory(realpath(__DIR__ . '/Vindi'), 'Vindi');