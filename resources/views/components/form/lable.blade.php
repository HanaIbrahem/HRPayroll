@props(['title' => '', 'for' => null, 'required' => false])
<label class="label">
  <span class="label-text  {{ $errors->has($for) ? 'text-error' : '' }}">
    {{ $title }} @if($required)<span class="text-error">*</span>@endif
  </span>
</label>
