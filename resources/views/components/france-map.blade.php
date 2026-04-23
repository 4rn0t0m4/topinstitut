@props(['departments'])

@php
    $deptMap = $departments->mapWithKeys(fn ($d) => [
        strtolower($d->code) => ['name' => $d->name, 'slug' => $d->slug, 'code' => $d->code],
    ]);
@endphp

<div x-data="franceMap()" x-ref="mapContainer" class="relative max-w-3xl mx-auto">
    {{-- Tooltip --}}
    <div x-show="tooltip" x-cloak
         :style="'left:' + tooltipX + 'px; top:' + tooltipY + 'px'"
         class="absolute z-10 bg-pink-600 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow-lg pointer-events-none -translate-x-1/2 -translate-y-full -mt-6 whitespace-nowrap">
        <span x-text="tooltipText"></span>
    </div>

    {{-- SVG Map --}}
    <div @click="goToDept($event)">
        @include('components.france-map-svg')
    </div>

    {{-- DOM-TOM --}}
    <div class="flex flex-wrap justify-center gap-2 mt-4">
        @foreach(['971' => 'Guadeloupe', '972' => 'Martinique', '973' => 'Guyane', '974' => 'La Réunion', '976' => 'Mayotte'] as $num => $name)
            @php $dept = $departments->firstWhere('code', $num); @endphp
            @if($dept)
                <a href="/{{ $dept->slug }}" class="px-3 py-2 bg-pink-100 hover:bg-pink-500 hover:text-white text-sm rounded transition">
                    {{ $num }} - {{ $dept->name }}
                </a>
            @endif
        @endforeach
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('franceMap', () => ({
        tooltip: false,
        tooltipText: '',
        tooltipX: 0,
        tooltipY: 0,
        depts: @js($deptMap),

        currentDept: null,

        init() {
            const self = this;
            this.$refs.mapContainer.querySelectorAll('path[id^="dep_"]').forEach(path => {
                let code = path.id.replace('dep_', '');
                if (code === '2a' || code === '2b') code = '20';
                const dept = this.depts[code];
                if (dept) {
                    path.dataset.dept = code;
                    path.dataset.name = dept.name;
                    path.dataset.slug = dept.slug;
                    path.dataset.code = dept.code;
                }
                path.classList.add('cursor-pointer', 'transition-colors');
                path.style.fill = '#fbcfe8'; // pink-200

                path.addEventListener('mouseenter', () => {
                    path.style.fill = '#ec4899';
                    if (!dept) return;
                    self.currentDept = dept.code;
                    self.tooltipText = dept.code + ' - ' + dept.name;
                    // Position tooltip at center of bounding box
                    const bbox = path.getBoundingClientRect();
                    const rect = self.$refs.mapContainer.getBoundingClientRect();
                    self.tooltipX = bbox.left - rect.left + bbox.width / 2;
                    self.tooltipY = bbox.top - rect.top;
                    self.tooltip = true;
                });
                path.addEventListener('mouseleave', () => {
                    path.style.fill = '#fbcfe8';
                    if (self.currentDept === (dept?.code)) {
                        self.tooltip = false;
                        self.currentDept = null;
                    }
                });
            });
        },

        showTooltip() {},
        hideTooltip() {},

        goToDept(e) {
            const el = e.target.closest('path[data-dept]');
            if (!el || !el.dataset.slug) return;
            window.location.href = '/' + el.dataset.slug;
        }
    }));
});
</script>
