<div class="image officers200 {{ $officer['image_class'] }}"></div>

<div id="content">
    <a class="close_details" href="javascript:void(0);" ref="{{ $officer['ref'] }}"></a>

    <h2>{{ $officer['title'] }}</h2>

    @if (!empty($officer['meta']))
        <span class="level" style="white-space: nowrap">
            @foreach ($officer['meta'] as $meta)
                <span class="{{ ($officer['ref'] === '1' || $officer['ref'] === '12' || ! $loop->last) ? 'undermark' : 'overmark' }}{{ $officer['ref'] === '1' ? ' js_darkMatterAmount' : '' }}">{{ $meta }}</span>@if (! $loop->last)&nbsp;|&nbsp;@endif
            @endforeach
        </span>
    @endif

    <br class="clearfloat">

    <div id="wrapper">
        <div id="features">
            <p>{{ $officer['description'] }}</p>

            @if (!empty($officer['show_payment_overlay']) || !empty($officer['purchasable']))
                <div class="build-it_wrap">
                    @if (!empty($officer['show_payment_overlay']))
                        <a href="{{ route('payment.overlay') }}"
                           class="overlay officer build-it"
                           data-overlay-title="{{ __('t_ingame.layout.res_purchase_dm') }}"
                           data-overlay-class="payment"
                           data-overlay-popup-width="800"
                           data-overlay-popup-height="620">
                            <span>{{ $officer['action_label'] ?? __('t_ingame.shop.btn_purchase_dark_matter') }}</span>
                        </a>
                    @elseif (!empty($officer['can_afford']))
                        <form id="premium-officer-purchase-{{ $officer['ref'] }}" action="{{ route('premium.purchase') }}" method="post">
                            @csrf
                            <input type="hidden" name="type" value="{{ $officer['ref'] }}">
                        </form>
                        <a href="javascript:void(0);"
                           class="officer build-it"
                           onclick="document.getElementById('premium-officer-purchase-{{ $officer['ref'] }}').submit(); return false;">
                            <span>{{ $officer['action_label'] }}</span>
                        </a>
                    @else
                        <a href="{{ route('payment.overlay') }}"
                           class="overlay officer build-it_disabled nodarkmatter"
                           data-overlay-title="{{ __('t_ingame.layout.res_purchase_dm') }}"
                           data-overlay-class="payment"
                           data-overlay-popup-width="800"
                           data-overlay-popup-height="620">
                            <span>{{ __('t_ingame.shop.btn_purchase_dark_matter') }}</span>
                        </a>
                    @endif
                </div>
            @endif

            <br class="clearfloat">
        </div>
    </div>
</div>

<br class="clearfloat">

<div id="description">
    @if (!empty($officer['benefits']))
        <div class="benefitlist">
            @foreach ($officer['benefits'] as $benefit)
                <span>{{ $benefit }}</span>
            @endforeach
        </div>
    @endif
</div>
