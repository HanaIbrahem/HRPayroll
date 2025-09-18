@props(['filePath' => $filePath, 'sheet' => $sheet, 'rows' => $rows, 'error' => $error])

{{-- resources/views/components/excel/preview.blade.php --}}
<div class="rounded-xl border border-base-300 bg-base-100 overflow-auto">
   

    {{-- Body --}}
    @if ($error)
        <div class="p-4 text-error">{{ $error }}</div>

    @elseif (empty($rows))
        <div class="p-4 text-base-content/70">No rows to display.</div>

    @else
        <div class="p-2 overflow-x-auto">
            <table class="table table-sm w-full border-collapse">
                <thead>
                    <tr class="bg-base-200">
                        @foreach(($rows[0] ?? []) as $i => $head)
                            <th class="px-3 py-2 text-left border-b border-base-300 text-xs sm:text-sm font-semibold">
                                {{ $head !== '' ? $head : 'Col '.($i+1) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-xs sm:text-sm">
                    @foreach(array_slice($rows, 1) as $r)
                        <tr class="hover:bg-base-200/50">
                            @foreach($r as $cell)
                                <td class="whitespace-nowrap align-top px-3 py-2 border-b border-base-300">
                                    {{-- empty cells render as empty --}}
                                    {{ $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>


