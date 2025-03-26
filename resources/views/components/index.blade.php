@php
    use Filament\Support\Enums\Alignment;

    $canGrow = $canGrow();
    $containers =  $getData();
    $hasHiddenHeader = $shouldHideHeader();
    $label = $getLabel();
    $tableFields = $getTableFields();
    $breakPoint = $getBreakPoint();
    $hasContainers = count($containers) > 0;
    $addAction = $getAction($getAddActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $isAddable = $isAddable();
    $isDeletable = $isDeletable();
    $statePath = $getStatePath();
    $hasActions =  $deleteAction->isVisible();
@endphp

<div
    x-data="{}"
    {{ $attributes->merge($getExtraAttributes())->class([
        'filament-masterdetail-component space-y-6 relative',
        match ($breakPoint) {
            'sm' => 'break-point-sm',
            'lg' => 'break-point-lg',
            'xl' => 'break-point-xl',
            '2xl' => 'break-point-2xl',
            default => 'break-point-md',
        }
    ]) }}
>
    <div @class([
        'filament-masterdetail-container rounded-xl relative ring-1 ring-gray-950/5 dark:ring-white/20 overflow-x-auto',
        'sm:ring-gray-950/5 dark:sm:ring-white/20' => $breakPoint !== 'sm',
        'md:ring-gray-950/5 dark:md:ring-white/20' => $breakPoint !== 'md',
        'lg:ring-gray-950/5 dark:lg:ring-white/20' => $breakPoint !== 'lg',
        'xl:ring-gray-950/5 dark:xl:ring-white/20' => $breakPoint !== 'xl',
        '2xl:ring-gray-950/5 dark:2xl:ring-white/20' => $breakPoint !== '2xl',
    ])>
        <table class="w-full divide-y divide-gray-200 dark:divide-white/5">
            <thead @class([
                'sr-only' => $hasHiddenHeader,
                'filament-masterdetail-header' => !$hasHiddenHeader,
            ])>
            @if($label)
                <tr class="text-sm font-bold bg-gray-50 dark:bg-white/10">
                    <th scope="col" class="py-2 px-4" colspan="{{ count($tableFields) + ($hasActions ? 1 : 0) }}">
                        <span class="flex justify-center">{{ $label }}</span>
                    </th>
                </tr>
            @endif
            <tr class="text-xs bg-gray-50 dark:bg-white/5">
                @foreach ($tableFields as $tableField)
                    @php
                        $alignment = $tableField->alignAjust($tableField->getAlignment());
                        $isHidden = $tableField->isHidden();
                    @endphp

                    @if($isHidden)
                        @continue
                    @endif


                    <th
                        @class([
                            'px-3 py-3.5 text-sm font-bold',
                            'text-left' => $alignment === Alignment::Start || $alignment === Alignment::Left || $alignment === null,
                            'text-center' => $alignment === Alignment::Center,
                            'text-right' => $alignment === Alignment::End || $alignment === Alignment::Right,
                        ])
                    >
                        {{ $tableField->getLabel() }}
                    </th>
                @endforeach

                @if($hasActions)
                    <th class="fi-ta-actions-header-cell w-1 px-3">
                        @if ($isAddable)
                            {{ $addAction }}
                        @endif
                    </th>
                @endif
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
            @forelse ($containers as $uuid => $row)
                <tr wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $field::class }}.item">
                    @foreach($row as $cell)
                        @php
                            $alignment = $cell->alignAjust($cell->getAlignment());
                            $colWidth = $cell->getColumnWidth();
                            $isHidden = $cell->isHidden();
                        @endphp
                        @if($isHidden)
                            @continue
                        @endif
                        <td
                            @class([
                                'px-3 py-2 whitespace-nowrap text-sm',
                                'text-left' => $alignment === Alignment::Start || $alignment === Alignment::Left || $alignment === null,
                                'text-center' => $alignment === Alignment::Center,
                                'text-right' => $alignment === Alignment::End || $alignment === Alignment::Right,
                            ])
                            @if($colWidth)
                                style="width: {{ $colWidth }}"
                            @endif
                        >
                            {{ $cell->formatState($cell->getState()) }}
                        </td>
                    @endforeach

                    <td class="px-3 py-2 whitespace-nowrap text-sm">
                        @if ($isDeletable)
                            {{ $deleteAction(['item' => $uuid]) }}
                        @endif
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($tableFields) + ($hasActions ? 1 : 0) }}"
                        class="px-4 py-4 text-center italic text-sm">
                        {{ $emptyLabel ?? 'Nenhum registro encontrado.' }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
