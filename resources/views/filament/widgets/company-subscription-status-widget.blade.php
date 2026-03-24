<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="space-y-3">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                    Subscription
                </h3>

                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Plan</span>
                    <x-filament::badge :color="$statusColor">
                        {{ $planCode }}
                    </x-filament::badge>
                     <x-filament::badge :color="$statusColor">
                        {{ $statusLabel }}
                    </x-filament::badge>
                </div> 

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Next renew date</p>
                    <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $nextRenewLabel }}</p>
                </div>
            </div>

            @if (filled($upgradeUrl))
                <x-filament::button
                    tag="a"
                    :href="$upgradeUrl"
                    size="sm"
                    color="primary"
                >
                    Upgrade plan
                </x-filament::button>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
