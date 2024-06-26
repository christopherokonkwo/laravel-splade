<?php

namespace ProtoneMedia\Splade\Table;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasFilters
{
    protected Collection $filters;

    /**
     * Add a select filter to the query builder.
     *
     * @return $this
     */
    public function selectFilter(
        string $key,
        array $options,
        string $label = null,
        string $defaultValue = null,
        bool $noFilterOption = true,
        string $noFilterOptionLabel = null
    ): self {
        $this->filters = $this->filters->reject(function (Filter $filter) use ($key) {
            return $filter->key === $key;
        })->push(new Filter(
            key: $key,
            label: $label ?: Str::headline($key),
            options: $options,
            value: $defaultValue,
            noFilterOption: $noFilterOption,
            noFilterOptionLabel: $noFilterOptionLabel ?: '-',
            type: Filter::TYPE_SELECT,
            timezone: null
        ))->values();

        return $this;
    }

    /**
     * Add a date filter to the query builder.
     *
     * @param  string  $key
     * @param  string|null  $label
     * @param  string|null  $defaultValue
     * @return $this
     */
    public function dateRangeFilter(
        string $key,
        string $label = null,
        string $defaultValue = null,
        string $timezone = null
    ): self {
        $this->filters = $this->filters->reject(function (Filter $filter) use ($key) {
            return $filter->key === $key;
        })->push(new Filter(
            key: $key,
            label: $label ?: Str::headline($key),
            options: [],
            value: $defaultValue,
            noFilterOption: false,
            noFilterOptionLabel: '',
            type: Filter::TYPE_DATE_RANGE,
            timezone: $timezone
        ))->values();

        return $this;
    }

    /**
     * Returns a collection with all filters, and applies
     * the data from the request query on each of them.
     */
    public function filters(): Collection
    {
        $queryFilters = $this->query('filter', []);

        $filters = $this->filters->map->clone()->keyBy->key;

        if (!empty($filters)) {
            $filters->each(function (Filter $filter) use ($queryFilters) {
                if (array_key_exists($filter->key, $queryFilters)) {
                    $filter->value = $queryFilters[$filter->key];
                }
            });
        }

        return $filters;
    }

    /**
     * Returns a boolean whether this table has filters.
     */
    public function hasFilters(): bool
    {
        return $this->filters->isNotEmpty();
    }

    /**
     * Returns a boolean whether this table has filters enabled.
     */
    public function hasFiltersEnabled(): bool
    {
        return $this->filters()->reject(fn (Filter $filter) => is_null($filter->value))->isNotEmpty();
    }

    /**
    * Returns a key-value array of all filters.
    *
    * @return array
    */
    public function filterValues(): array
    {
        return $this->filters()
            ->keyBy->key
            ->map->value
            ->all();
    }
}
