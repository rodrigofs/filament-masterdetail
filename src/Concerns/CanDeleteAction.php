<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Concerns;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Enums\{ActionSize, IconSize};
use Filament\Support\Facades\FilamentIcon;
use Rodrigofs\FilamentMasterdetail\Components\Masterdetail;

trait CanDeleteAction
{
    protected bool | Closure $isDeletable = true;

    protected ?Closure $modifyDeleteActionUsing = null;

    public function getDeleteAction(): Action
    {
        $action = Action::make($this->getDeleteActionName())
            ->label(__('filament-masterdetail::masterdetail.delete'))
            ->icon(FilamentIcon::resolve('forms::components.repeater.actions.delete') ?? 'heroicon-m-trash')
            ->iconSize(IconSize::Small)
            ->color('danger')
            ->action(function (array $arguments, Masterdetail $component, $state): void {

                unset($state[$arguments['item']]);

                $component->state($state);

                $component->callAfterStateUpdated();
            })
            ->iconButton()
            ->requiresConfirmation()
            ->size(ActionSize::Small)
            ->visible(fn (Masterdetail $component): bool => $component->isDeletable());

        if ($this->modifyDeleteActionUsing) {
            $action = $this->evaluate($this->modifyDeleteActionUsing, [
                'action' => $action,
            ]) ?? $action;
        }

        return $action;
    }

    public function deleteAction(?Closure $callback): static
    {
        $this->modifyDeleteActionUsing = $callback;

        return $this;
    }

    public function getDeleteActionName(): string
    {
        return 'delete';
    }

    public function isDeletable(): bool
    {
        if ($this->isDisabled()) {
            return false;
        }

        return (bool) $this->evaluate($this->isDeletable);
    }

    public function deletable(bool | Closure $condition = true): static
    {
        $this->isDeletable = $condition;

        return $this;
    }
}
