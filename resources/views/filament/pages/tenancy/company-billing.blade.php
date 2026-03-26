<x-filament-panels::page>
    <div class="company-billing-page">        <div class="company-billing-shell">
            <div class="company-billing-header">
                <div class="company-billing-title-wrap">
                     <div class="company-billing-icon" aria-hidden="true">
                        @if (filled($companyAvatarUrl))
                            <img
                                src="{{ $companyAvatarUrl }}"
                                alt="{{ $companyName }} avatar"
                                class="company-billing-avatar"
                            />
                        @else
                            <span class="company-billing-avatar-fallback">{{ $companyInitials }}</span>
                        @endif
                    </div>
                   <div>
                        <h2 class="company-billing-title">Upgrade Plan</h2>

                        <p class="company-billing-subtitle">Scale your booking system with the right tools for your team.</p>
                                            </div>
                </div>
            </div>

            <div class="company-billing-cycle">
                <button
                    type="button"
                    class="company-billing-cycle-btn {{ $billingCycle === 'monthly' ? 'is-active' : '' }}"
                    wire:click="selectCycle('monthly')"
                >
                    Monthly
                </button>
                <button
                    type="button"
                    class="company-billing-cycle-btn {{ $billingCycle === 'annual' ? 'is-active' : '' }}"
                    wire:click="selectCycle('annual')"
                >
                    Annual <span class="company-billing-cycle-chip">Save 50%</span>
                </button>
            </div>

            <div class="company-billing-grid">
                @foreach ($cards as $card)
                    <article class="company-billing-card {{ $card['highlighted'] ? 'is-featured' : '' }}">
                        @if (! empty($card['badge']))
                            <div class="company-billing-badge">{{ $card['badge'] }}</div>
                        @endif

                        <h3 class="company-billing-plan">{{ $card['name'] }}</h3>
                        <p class="company-billing-desc">{{ $card['description'] }}</p>

                        <div class="company-billing-price-block">
                            <p class="company-billing-price">{{ $card['price_label'] }}</p>
                            <p class="company-billing-billed">{{ $card['billing_label'] }}</p>
                        </div>

                        @if ($card['action_state'] === 'current')
                            <button type="button" class="company-billing-btn is-current" disabled>
                                {{ $card['action_label'] }}
                            </button>
                        @elseif ($card['action_state'] === 'upgrade')
                            <button
                                type="button"
                                class="company-billing-btn is-upgrade"
                                wire:click="upgrade('{{ $card['tier'] }}')"
                            >
                                {{ $card['action_label'] }}
                            </button>
                        @else
                            <button
                                type="button"
                                class="company-billing-btn is-contact"
                                wire:click="upgrade('{{ $card['tier'] }}')"
                            >
                                {{ $card['action_label'] }}
                            </button>
                        @endif

                        <ul class="company-billing-features">
                            @foreach ($card['features'] as $feature)
                                <li>
                                    <span class="company-billing-check">&#10003;</span>
                <span class="company-billing-check">?</span>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
