@props(['title' => '', 'for' => null, 'required' => false, 'full' => false])
<div {{ $attributes->class(['form-control group mb-2', 'md:col-span-2' => $full]) }}>
  <x-form.lable :title="$title" :for="$for" :required="$required" />

  <div class="rounded-xl p-2 transition-colors">
    {{ $slot }}
  </div>

  <x-form.errors :for="$for" />
</div>
