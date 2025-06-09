<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/index.php',
		__DIR__ . '/lib',
	])
	->withSkip([
		__DIR__ . '/tests/kirby',
	])
	->withPhpSets()
	->withTypeCoverageLevel(2)
	->withDeadCodeLevel(2)
	->withCodeQualityLevel(2);
