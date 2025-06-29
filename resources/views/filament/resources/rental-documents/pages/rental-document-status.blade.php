<x-filament-panels::page>
    <div class="space-y-6">
        <div class="fi-header">
            <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                Status Wydań i Zwrotów
            </h1>
            <p class="fi-header-subheading text-sm text-gray-600 dark:text-gray-400">
                Przegląd statusu wydań i zwrotów dla wszystkich aktywnych umów wynajmu
            </p>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
