<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Components;

use Closure;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\{CanGrow, HasAlignment};
use Filament\Tables\Columns\Concerns\{CanFormatState, CanSpanColumns, HasLabel, HasName, HasRowLoopObject};
use Filament\Support\Enums\Alignment;
use Rodrigofs\FilamentMasterdetail\Concerns\CanBeHidden;

final class DataColumn extends Component
{
    use CanBeHidden;
    use CanFormatState;
    use CanGrow;
    use CanSpanColumns;
    use HasAlignment;
    use HasLabel;
    use HasName;
    use HasRowLoopObject;

    protected mixed $state = null;

    protected string | Closure | null $columnWidth = null;

    protected string | Closure | null $relationship = null;

    protected mixed $getStateUsing = null;

    final public function __construct(string $name)
    {
        $this->name($name);
    }

    public static function make(string $name): static
    {
        $static = app(self::class, ['name' => $name]);

        $static->configure();

        return $static;
    }

    public function relationship(string | Closure | null $name = null): static
    {
        $this->relationship = $name;

        return $this;
    }

    public function columnWidth(string | Closure $width): static
    {
        $this->columnWidth = $width;

        return $this;
    }

    public function state(mixed $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getRelationship(): ?string
    {
        return $this->evaluate($this->relationship);
    }

    public function getState(): mixed
    {
        return ($this->getStateUsing !== null) ?
            $this->evaluate($this->getStateUsing) :
            $this->state;
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'state' => [$this->getState()],
            'rowLoop' => [$this->getRowLoop()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    public function getColumnWidth(): ?string
    {
        return $this->evaluate($this->columnWidth);
    }

    public function getClone(): DataColumn
    {
        return clone $this;
    }

    public function alignAjust(?Alignment $alignment): ?Alignment
    {
        return $alignment;
    }
}
