<x-layouts.app
    title="Trouvez votre institut idéal - Quiz TopInstitut"
    description="5 questions pour découvrir les instituts de beauté qui correspondent à vos envies. Quiz gratuit et instantané."
>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Trouvez votre institut idéal</h1>
            <p class="text-gray-500 mt-2">5 questions, 30 secondes, et on vous propose 3 instituts faits pour vous.</p>
        </div>

        <form method="POST" action="{{ route('quiz.submit') }}" x-data="{ step: 1, type: '', city: '', features: [], min_rating: '', radius: 15 }">
            @csrf

            {{-- Progress bar --}}
            <div class="mb-6">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Question <span x-text="step"></span>/5</span>
                    <span x-text="Math.round(step / 5 * 100) + '%'"></span>
                </div>
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-pink-600 transition-all" :style="'width:' + (step / 5 * 100) + '%'"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6 min-h-[280px]">

                {{-- Q1 : Type --}}
                <div x-show="step === 1">
                    <h2 class="text-lg font-semibold mb-4">Quelle prestation cherchez-vous ?</h2>
                    <div class="space-y-2">
                        @foreach([['', 'Peu importe / mixte'], ['0', 'Institut de beauté'], ['2', 'Spa / bien-être'], ['1', 'Esthéticienne à domicile']] as [$val, $label])
                            <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-pink-50 hover:border-pink-300 transition" :class="type === '{{ $val }}' ? 'bg-pink-50 border-pink-500' : ''">
                                <input type="radio" name="type" value="{{ $val }}" x-model="type" class="text-pink-600">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Q2 : Ville --}}
                <div x-show="step === 2" x-cloak>
                    <h2 class="text-lg font-semibold mb-4">Où ?</h2>
                    <input type="text" name="city" x-model="city" placeholder="Votre ville (ex: Paris, Lyon, Caen…)" class="w-full border rounded-lg px-4 py-3 text-sm">
                    <p class="text-xs text-gray-400 mt-2">Laissez vide si vous n'avez pas de préférence géographique.</p>
                </div>

                {{-- Q3 : Features --}}
                <div x-show="step === 3" x-cloak>
                    <h2 class="text-lg font-semibold mb-4">Critères importants pour vous ?</h2>
                    <p class="text-xs text-gray-400 mb-4">Cochez ce qui compte. Plus l'institut a de critères, mieux il match.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach(\App\Models\Establishment::FEATURES as $key => $label)
                            <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer hover:bg-pink-50 hover:border-pink-300 transition" :class="features.includes('{{ $key }}') ? 'bg-pink-50 border-pink-500' : ''">
                                <input type="checkbox" name="features[]" value="{{ $key }}" x-model="features" class="rounded text-pink-600">
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Q4 : Note min --}}
                <div x-show="step === 4" x-cloak>
                    <h2 class="text-lg font-semibold mb-4">Note minimum souhaitée ?</h2>
                    <div class="space-y-2">
                        @foreach([['', 'Peu importe'], ['3', '3/5 ou plus'], ['4', '4/5 ou plus'], ['4.5', '4,5/5 ou plus (excellent)']] as [$val, $label])
                            <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-pink-50 hover:border-pink-300 transition" :class="min_rating === '{{ $val }}' ? 'bg-pink-50 border-pink-500' : ''">
                                <input type="radio" name="min_rating" value="{{ $val }}" x-model="min_rating" class="text-pink-600">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Q5 : Radius --}}
                <div x-show="step === 5" x-cloak>
                    <h2 class="text-lg font-semibold mb-4">Distance maximum ?</h2>
                    <div class="space-y-2">
                        @foreach([[5, 'Très proche (5 km)'], [15, 'Dans ma zone (15 km)'], [30, 'Élargi (30 km)'], [50, 'Peu importe (50 km)']] as [$val, $label])
                            <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-pink-50 hover:border-pink-300 transition" :class="radius === {{ $val }} ? 'bg-pink-50 border-pink-500' : ''">
                                <input type="radio" name="radius" value="{{ $val }}" x-model.number="radius" class="text-pink-600">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Nav buttons --}}
            <div class="flex justify-between mt-6">
                <button type="button" x-show="step > 1" @click="step--" class="text-gray-500 hover:text-gray-900 px-4 py-2">← Précédent</button>
                <span x-show="step === 1"></span>

                <button type="button" x-show="step < 5" @click="step++" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 ml-auto">Suivant →</button>
                <button type="submit" x-show="step === 5" x-cloak class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 ml-auto">Voir mes résultats</button>
            </div>
        </form>
    </div>
</x-layouts.app>
