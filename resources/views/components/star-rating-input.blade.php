@props(['name', 'label', 'value' => 0])

<div x-data="{ rating: {{ (int) $value ?: 0 }}, hover: 0 }">
    <label class="block text-sm font-medium mb-1">{{ $label }}</label>
    <input type="hidden" name="{{ $name }}" :value="rating" required>
    <div class="flex items-center gap-0.5">
        @for($i = 1; $i <= 5; $i++)
            <button type="button"
                @click="rating = {{ $i }}"
                @mouseenter="hover = {{ $i }}"
                @mouseleave="hover = 0"
                class="focus:outline-none transition-colors"
            >
                <svg class="w-7 h-7 cursor-pointer text-gray-300"
                    :class="(hover || rating) >= {{ $i }} ? '!text-yellow-400' : '!text-gray-300'"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </button>
        @endfor
        <span class="text-sm text-gray-500 ml-1" x-show="rating > 0" x-text="rating + '/5'"></span>
    </div>
</div>
