<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Concerns;

use Closure;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Arr;

trait CanBeHidden
{
    protected bool | Closure $isHidden = false;

    protected bool | Closure $isVisible = true;

    public function hidden(bool | Closure $condition = true): static
    {
        $this->isHidden = $condition;

        return $this;
    }

    /**
     * @param  string | array<string>  $livewireComponents
     */
    public function hiddenOn(string | array $livewireComponents): static
    {
        $this->hidden(static function (HasTable $livewire) use ($livewireComponents): bool {
            return array_any(Arr::wrap($livewireComponents), fn ($livewireComponent) => $livewire instanceof $livewireComponent);
        });

        return $this;
    }

    public function visible(bool | Closure $condition = true): static
    {
        $this->isVisible = $condition;

        return $this;
    }

    /**
     * @param  string | array<string>  $livewireComponents
     */
    public function visibleOn(string | array $livewireComponents): static
    {
        $this->visible(static function (HasTable $livewire) use ($livewireComponents): bool {
            return array_any(Arr::wrap($livewireComponents), fn ($livewireComponent) => $livewire instanceof $livewireComponent);

        });

        return $this;
    }

    public function isHidden(): bool
    {
        if ($this->evaluate($this->isHidden)) {
            return true;
        }

        return ! $this->evaluate($this->isVisible);
    }

    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }
}
