@props(['title' => '', 'for' => null, 'class'=>'', 'required' => false, 'full' => false])
<div class="form-control group {{ $class}}">
  <x-form.lable :title="$title" :for="$for" :required="$required" />
  
  <div class="rounded-xl p-2 transition-colors">
   {{ $slot }}
      
  </div>
  <x-form.errors :for="$for" />

</div>