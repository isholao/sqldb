<?php

namespace Isholao\SqlDb;

/**
 *
 * @author ishola.o<ishola.tolu@outlook.com>
 */
interface ProfilerInterface
{

    public function activate(): void;

    public function deactivate(): void;

    public function isActive(): bool;

    public function getProfiles(): array;

    public function resetProfiles(): void;
}
