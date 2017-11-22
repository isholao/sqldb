<?php

namespace Isholao\SqlDb;

/**
 * @author Ishola O <ishola.tolu@outlook.com>
 */
class Profiler implements ProfilerInterface
{

    /**
     *
     * Is the profiler active?
     *
     * @var bool
     *
     */
    protected $active = FALSE;

    /**
     *
     * Retained profiles.
     *
     * @var array
     *
     */
    protected $profiles = [];

    public function activate(): void
    {
        $this->active = TRUE;
    }

    public function deactivate(): void
    {
        $this->active = FALSE;
    }

    /**
     *
     * Is the profiler active?
     *
     * @return bool
     *
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     *
     * Adds a profile entry.
     *
     * @param float $duration The query duration.
     *
     * @param string $function The PDO method that made the entry.
     *
     * @param string $statement The SQL query statement.
     *
     * @param array $bind_values The values bound to the statement.
     *
     * @return null
     *
     */
    public function addProfile(float $duration, ?string $function = NULL,
                               ?string $statement = NULL,
                               array $bind_values = [])
    {
        if (!$this->isActive())
        {
            return;
        }

        $this->profiles[] = [
            'duration' => \round($duration, 3),
            'function' => $function,
            'statement' => $statement,
            'bind_values' => $bind_values
        ];
    }

    /**
     *
     * Returns all the profile entries.
     *
     * @return array
     *
     */
    public function getProfiles(): array
    {
        return $this->profiles;
    }

    /**
     *
     * Reset all the profiles
     *
     * @return null
     *
     */
    public function resetProfiles(): void
    {
        $this->profiles = [];
    }

}
