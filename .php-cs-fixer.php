<?php

use PreemStudio\PhpCsFixer\ConfigurationFactory;
use PreemStudio\PhpCsFixer\Presets\Standard;

$config = ConfigurationFactory::fromPreset(new Standard());
$config->getFinder()->in(__DIR__);

return $config;
