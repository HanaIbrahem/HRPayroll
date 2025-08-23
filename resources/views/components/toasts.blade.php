@props([
  // Position classes from DaisyUI: toast-top|toast-middle|toast-bottom + toast-start|toast-center|toast-end
  'position' => 'toast-top toast-end',
  'duration' => 3000, // ms
])

<div
  x-data="{
    toasts: [],
    add(type, message) {
      if (!message) return;
      const id = Date.now() + Math.random();
      this.toasts.push({ id, type, message });
      setTimeout(() => this.remove(id), {{ (int) $duration }});
    },
    remove(id) {
      this.toasts = this.toasts.filter(t => t.id !== id);
    }
  }"
  x-on:toast.window="add($event.detail.type ?? 'info', $event.detail.message ?? '')"
  class="toast {{ $position }} z-50 pointer-events-none"
>
  <template x-for="t in toasts" :key="t.id">
    <div
      class="alert pointer-events-auto"
      :class="{
        'alert-success': t.type === 'success',
        'alert-error':   t.type === 'error',
        'alert-warning': t.type === 'warning',
        'alert-info':    !['success','error','warning'].includes(t.type)
      }"
    >
      <span x-text="t.message"></span>
      <button class="btn btn-ghost btn-xs ml-2" @click="remove(t.id)">✕</button>
    </div>
  </template>
</div>

{{-- Optional: fire toast if a flash message exists --}}
@if (session('ok'))
  <script>
    window.addEventListener('load', () => {
      window.dispatchEvent(new CustomEvent('toast', {
        detail: { type: 'success', message: @json(session('ok')) }
      }));
    });
  </script>
@endif
