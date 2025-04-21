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
    $isEditable = $isEditable();
    $editAction = $getAction($getEditActionName());
    $statePath = $getStatePath();
    $hasActions =  $deleteAction->isVisible();
    $headerActions = $getHeaderActions();

    $componentId = Str::of($field::class)->replace('\\','-', $field::class)->slug();
@endphp


<x-filament::section
    class="filament-masterdetail"
    :headerActions="$headerActions"
    :description="$getDescription()"
    :heading="$getHeading()"
    :icon="$getIcon()"
    :icon-color="$getIconColor()"
>
    <table class="w-full p-0 m-0">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
        <tr class="bg-gray-50 dark:bg-white/5">
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
                        'px-6' => $loop->first,
                        'px-3' => !$loop->first,
                        'py-3.5 text-sm font-bold',
                        'text-left' => $alignment === Alignment::Start || $alignment === Alignment::Left || $alignment === null,
                        'text-center' => $alignment === Alignment::Center,
                        'text-right' => $alignment === Alignment::End || $alignment === Alignment::Right,
                    ])
                >
                    {{ $tableField->getLabel() }}
                </th>
            @endforeach
            <th class="px-3 py-3.5 text-sm font-bold text-center"><!-- Action Delete Space --></th>
        </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
        @forelse ($containers as $uuid => $row)
            <tr wire:key="{{ $this->getId() }}.{{ $statePath }}.{{ $componentId }}.item">
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
                            'px-6' => $loop->first,
                            'px-3' => !$loop->first,
                            'py-4 whitespace-nowrap text-sm',
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
                    <div class="flex items-center justify-center gap-3">
                        @if ($isEditable)
                            {{ $editAction(['item' => $uuid]) }}
                        @endif

                        @if ($isDeletable)
                            {{ $deleteAction(['item' => $uuid]) }}
                        @endif
                    </div>
                </td>

                {{--                <td class="px-3 py-2 whitespace-nowrap text-sm">--}}
                {{--                    @if ($isEditable)--}}
                {{--                        {{ $editAction(['item' => $uuid]) }}--}}
                {{--                    @endif--}}
                {{--                </td>--}}

            </tr>
        @empty
            <tr>
                <td colspan="{{ count($tableFields) }}"
                    class="px-4 py-4 text-center italic text-sm">
                    {{ $emptyLabel ?? __('filament-masterdetail::masterdetail.empty') }}
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</x-filament::section>

