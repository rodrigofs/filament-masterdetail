<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentMasterdetail\Concerns;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Illuminate\Support\Str;

trait HasFormModal
{
    /**
     * @var array<Component>|Closure
     */
    protected array | Closure $schema = [];

    /**
     * @var array<string,mixed>|Closure
     */
    protected array | Closure $data = [];

    private string | Closure | null $unique = null;

    private ?Closure $beforeAddActionExecute = null;

    /**
     * @var list<string>
     */
    private array $formExceptClear = [];

    private bool $modalPersistent = false;

    protected bool | Closure $isAddable = true;

    protected string | Closure | null $addActionLabel = null;

    public function schema(Closure | array $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    public function beforeAddActionExecute(Closure $beforeAddActionExecute): static
    {
        $this->beforeAddActionExecute = $beforeAddActionExecute;

        return $this;
    }

    public function modalPersistent(bool $persistent = true): static
    {
        $this->modalPersistent = $persistent;

        return $this;
    }

    /**
     * @param  list<string>  $formExceptClear
     * @return $this
     */
    public function formExceptClear(array $formExceptClear = []): static
    {
        $this->formExceptClear = $formExceptClear;

        return $this;
    }

    public function unique(string | Closure | null $unique): static
    {
        $this->unique = $unique;

        return $this;
    }

    public function isAddable(): bool
    {
        if ($this->isDisabled()) {
            return false;
        }

        return (bool) $this->evaluate($this->isAddable);
    }

    public function getAddActionName(): string
    {
        return 'add';
    }

    public function addable(bool | Closure $condition = true): static
    {
        $this->isAddable = $condition;

        return $this;
    }

    public function addActionLabel(string | Closure | null $label): static
    {
        $this->addActionLabel = $label;

        return $this;
    }

    /**
     * @param  array<string,mixed> | Closure  $data
     * @return $this
     */
    public function fillForm(array | Closure $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getAddAction(): Action
    {
        $action = Action::make($this->getAddActionName())
            ->modalSubmitActionLabel(function (self $component) {
                return $component->modalPersistent ? __('filament-masterdetail::masterdetail.modal.add') : __('filament-masterdetail::masterdetail.modal.done');
            })
            ->modalCancelActionLabel(function (self $component) {
                return $component->modalPersistent ? __('filament-masterdetail::masterdetail.modal.done') : __('filament-masterdetail::masterdetail.modal.cancel');
            })
            ->closeModalByClickingAway(fn (self $component) => !$component->isModalClosedByClickingAway())
            ->slideOver(fn (self $component) => $component->isModalSlideOver())
            ->modalWidth(fn (self $component) => $component->getModalWidth())
            ->modalAlignment(fn (self $component) => $component->getModalAlignment())
            ->modalAutofocus(fn (self $component) => $component->isModalAutofocused())
            ->modalDescription(fn (self $component) => $component->getModalDescription())
            ->stickyModalHeader(fn (self $component) => $component->isModalHeaderSticky())
            ->stickyModalFooter(fn (self $component) => $component->isModalFooterSticky())
            ->modalIcon(fn (self $component) => $component->getModalIcon())
            ->modalHeading(fn (self $component) => $component->getModalHeading())
            ->label(fn (self $component): string => $component->getLabel() ?? $component->getAddActionLabel())
            ->visible(fn (self $component): bool => $component->isAddable())
            ->form($this->getSchema())
            ->fillForm($this->data)
            ->action(function (Action $action, Form $form, self $component, $data): void {
                $uuid = $component->generateUuid();

                $item = $component->getState();

                if ($this->beforeAddActionExecute) {
                    $data = $this->evaluate($this->beforeAddActionExecute, [
                        'data' => $data,
                    ]);
                }

                /** @var list<string> $item */
                $item[$uuid] = $data;

                $item = collect($item)->unique($this->evaluate($this->unique))->toArray();

                $component->state($item);

                $component->callAfterStateUpdated();

                $exceptClear = collect($this->formExceptClear)->mapWithKeys(fn ($item) => [$item => $data[$item]])->toArray();

                if ($component->modalPersistent) {
                    $form->fill([
                        ...$exceptClear,
                    ]);

                    $action->halt();
                }

            })
            ->button();

        if ($this->modalPersistent) {
            $action->modalCancelActionLabel(__('filament-masterdetail::masterdetail.modal.done'));
        }

        return $action;
    }

    public function getAddActionLabel(): string
    {
        return $this->evaluate($this->addActionLabel) ?? __('filament-masterdetail::masterdetail.add', [
            'label' => Str::lcfirst($this->getLabel()),
        ]);
    }

    /**
     * @return array<Component> | Closure
     */
    private function getSchema(): Closure | array
    {
        return fn () => $this->evaluate($this->schema);
    }
}
