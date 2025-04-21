<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Concerns;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\{Arr, Str};
use Rodrigofs\FilamentMasterdetail\Components\Masterdetail;

trait CanEditAction
{
    protected bool | Closure $isEditable = true;

    protected ?Closure $modifyEditActionUsing = null;

    protected string | Closure | null $modalSubmitEditActionLabel = null;

    protected string | Closure | null $editActionLabel = null;

    public function getEditAction(): Action
    {
        $action = $this->getAddAction()->name($this->getEditActionName())
            ->component($this)
            ->icon('heroicon-o-pencil-square')
            ->iconSize(IconSize::Small)
            ->modalHeading(__('filament-masterdetail::masterdetail.modal.heading.edit', [
                'label' => $this->getModalHeading()
            ]))
            ->modalSubmitActionLabel(fn (self $component) => __('filament-masterdetail::masterdetail.modal.actions.edit', [
                'label' => Str::lcfirst($component->getModalSubmitEditActionLabel()),
            ]))
            ->label(fn (self $component): string => __('filament-masterdetail::masterdetail.actions.edit', [
                'label' => Str::lcfirst($this->getEditActionLabel()),
            ]))
            ->fillForm(function (array $arguments) {
                $itemKey = data_get($arguments, 'item');

                if (!is_string($itemKey)) {
                    return [];
                }

                $state = $this->getState();

                $itemState = data_get($state, $itemKey);

                if (!is_array($itemState)) {
                    return [];
                }

                return $itemState;
            })
            ->action(function (array $arguments, Action $action, Form $form, Masterdetail $component, $data): void {
                $itemKey = data_get($arguments, 'item');

                if (!is_string($itemKey)) {
                    return;
                }

                $state = $this->getState();

                $currentItemState = Arr::get($state, $itemKey, []);

                if (!is_array($currentItemState)) {
                    return;
                }

                $newItemState = array_merge($currentItemState, $data);

                Arr::set($state, $itemKey, $newItemState);

                $state = $this->refreshRelationship($data, $state, $itemKey);

                $component->state($state);
            })
            ->iconButton();

        if ($this->modalPersistent) {
            $action->modalCancelActionLabel(__('filament-masterdetail::masterdetail.modal.actions.cancel'));
        }

        return $action;
    }

    public function editAction(?Closure $callback): static
    {
        $this->modifyEditActionUsing = $callback;

        return $this;
    }

    public function editActionLabel(string | Closure | null $label): static
    {
        $this->editActionLabel = $label;

        return $this;
    }

    public function getEditActionName(): string
    {
        return 'edit';
    }

    public function getEditActionLabel(): ?string
    {
        return $this->evaluate($this->editActionLabel);
    }

    public function isEditable(): bool
    {
        if ($this->isDisabled()) {
            return false;
        }

        return (bool)$this->evaluate($this->isEditable);
    }

    public function editable(bool | Closure $condition = true): static
    {
        $this->isEditable = $condition;

        return $this;
    }

    public function modalSubmitEditActionLabel(string | Closure | null $label = null): static
    {
        $this->modalSubmitEditActionLabel = $label;

        return $this;
    }

    public function getModalSubmitEditActionLabel(): ?string
    {
        return $this->evaluate($this->modalSubmitEditActionLabel);
    }
}
