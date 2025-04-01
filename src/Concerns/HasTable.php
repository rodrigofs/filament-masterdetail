<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Concerns;

use Closure;
use Rodrigofs\FilamentMasterdetail\Components\DataColumn;
use stdClass;

trait HasTable
{
    protected string $breakPoint = 'md';

    /**
     * @var array<DataColumn>|Closure
     */
    protected Closure | array $tableFields = [];

    protected bool | Closure $withoutHeader = false;

    /**
     * @param  array<DataColumn> | Closure  $tableFields
     */
    public function table(array | Closure $tableFields): static
    {
        $this->tableFields = $tableFields;

        return $this;
    }

    /**
     * @return array<DataColumn>
     */
    public function getTableFields(): array
    {
        return $this->evaluate($this->tableFields);
    }

    public function breakPoint(string $breakPoint = 'md'): static
    {
        $this->breakPoint = $breakPoint;

        return $this;
    }

    public function getBreakPoint(): string
    {
        return $this->breakPoint;
    }

    public function withoutHeader(bool | Closure $condition = true): static
    {
        $this->withoutHeader = $condition;

        return $this;
    }

    public function shouldHideHeader(): bool
    {
        return $this->evaluate($this->withoutHeader);
    }

    /**
     * @return array<array<DataColumn>>
     */
    public function getData(): array
    {
        $containers = [];

        foreach ($this->getState() ?? [] as $uuid => $rowData) {
            $rowItem = [];
            /** @var DataColumn $tableField */
            foreach ($this->tableFields as $tableField) {
                $rowItem[] = $tableField
                    ->state(data_get($rowData, $tableField->getName()))
                    ->rowLoop($this->convertToObject($rowData))
                    ->getClone();
            }

            $containers[$uuid] = $rowItem;
        }

        return $containers;
    }

    /**
     * @return stdClass
     */
    private function convertToObject(mixed $data)
    {
        if (is_array($data)) {
            $object = new stdClass();

            foreach ($data as $key => $value) {
                $object->{$key} = $this->convertToObject($value);
            }

            return $object;
        }

        return $data;
    }
}
