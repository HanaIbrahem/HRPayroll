@props(['filePath' => $filePath, 'sheet' => $sheet, 'rows' => $rows, 'error' => $error])

<div class="rounded-xl border border-base-300 bg-base-100 overflow-auto">
    {{-- Top bar --}}
    <div class="px-4 py-3 border-b border-base-300 flex items-center justify-between">
        <div class="font-semibold">Excel Preview</div>
        <div class="text-sm text-base-content/60">
            <span class="opacity-70">File:</span> {{ basename($filePath) }}
            <span class="mx-2">·</span>
            <span class="opacity-70">Sheet:</span> {{ $sheet }}
        </div>
    </div>

    {{-- Body --}}
    @if($error)
        <div class="p-4 text-error">{{ $error }}</div>
    @elseif(empty($rows))
        <div class="p-4 text-base-content/70">No rows to display.</div>
    @else
        <div class="p-2">
            <table class="table table-sm w-full border-collapse">
                <thead>
                    <tr class="bg-base-200">
                        @foreach($rows[0] as $head)
                            <th class="px-3 py-2 text-left border-b border-base-300 text-sm font-semibold">
                                {{ $head === '' ? '—' : $head }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($rows, 1) as $r)
                        <tr class="hover:bg-base-200/50">
                            @foreach($r as $cell)
                                <td class="whitespace-nowrap align-top px-3 py-2 border-b border-base-300 text-sm">
                                    {{ $cell === '' ? '—' : $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
