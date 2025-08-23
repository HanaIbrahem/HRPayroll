@props(['title'=>'','description'=>''])
<div class="space-y-6">


    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight">{{$title}}</h2>

            @if (!empty($description))
            <p class="text-sm text-base-content/60">{{$description}}</p>
            @endif
        </div>
    </div>


    <div class="card bg-base-100 border border-base-300/60">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{ $slot }}
            </div>
        </div>
    </div>

</div>