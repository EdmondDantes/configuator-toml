<?php

declare(strict_types=1);

namespace IfCastle\Configurator\Toml;

use Devium\Toml\Toml;
use Devium\Toml\TomlError;
use IfCastle\Application\Bootloader\BootManager\MainConfigAppenderInterface;
use IfCastle\DI\Exceptions\ConfigException;
use IfCastle\Exceptions\RuntimeException;
use IfCastle\OsUtilities\FileSystem\Exceptions\FileIsNotExistException;
use IfCastle\OsUtilities\Safe;

final class ConfigMainAppender extends ConfigTomlMutable implements MainConfigAppenderInterface
{
    public function __construct(string $appDir)
    {
        parent::__construct($appDir . '/main.toml');
    }

    /**
     * @throws FileIsNotExistException
     * @throws ConfigException
     * @throws RuntimeException
     * @throws TomlError
     * @throws \ErrorException
     */
    #[\Override]
    public function appendSectionIfNotExists(string $section, array $data): void
    {
        $this->load();

        $node                       = $this->find(...\explode('.', $section));

        if ($node !== null) {
            return;
        }

        $path                       = \explode('.', $section);
        $config                     = [];
        $pointer                    = &$config;

        while (\count($path) > 0) {
            $section                = \array_shift($path);
            $pointer[$section]      = [];
            $pointer                = &$pointer[$section];
        }

        $pointer                    = $data;
        unset($pointer);

        $content                    = Toml::encode($config);

        Safe::execute(fn() => \file_put_contents($this->file, PHP_EOL . $content, \FILE_APPEND));
        $this->reset();
    }
}
