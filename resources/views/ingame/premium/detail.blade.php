<div class="image officers200 {{ $officer['image_class'] }}"></div>

<div id="content">
    <a class="close_details" href="javascript:void(0);" ref="{{ $officer['ref'] }}"></a>

    <h2>{{ $officer['title'] }}</h2>
</div>

<div id="description">
    <div id="wrapper">
        <p>{{ $officer['description'] }}</p>
    </div>

    @if (!empty($officer['benefits']))
        <div class="benefitlist">
            @foreach ($officer['benefits'] as $benefit)
                <span>{{ $benefit }}</span>
            @endforeach
        </div>
    @endif

    @if (!empty($officer['meta']))
        <div class="benefitlist">
            @foreach ($officer['meta'] as $meta)
                <span>{{ $meta }}</span>
            @endforeach
        </div>
    @endif

    <div id="wrapper">
        @if (!empty($officer['show_payment_overlay']))
            <a href="{{ route('payment.overlay') }}"
               class="overlay btn btn_confirm"
               data-overlay-title="{{ __('t_ingame.layout.res_purchase_dm') }}"
               data-overlay-class="payment"
               data-overlay-popup-width="800"
               data-overlay-popup-height="620">
                {{ __('t_ingame.layout.res_purchase_dm') }}
            </a>
        @elseif (!empty($officer['purchasable']))
            @if (!empty($officer['can_afford']))
                <form action="{{ route('premium.purchase') }}" method="post">
                    @csrf
                    <input type="hidden" name="type" value="{{ $officer['ref'] }}">
                    <button type="submit" class="btn btn_confirm">{{ $officer['action_label'] }}</button>
                </form>
            @else
                <a href="{{ route('payment.overlay') }}"
                   class="overlay btn btn_confirm"
                   data-overlay-title="{{ __('t_ingame.layout.res_purchase_dm') }}"
                   data-overlay-class="payment"
                   data-overlay-popup-width="800"
                   data-overlay-popup-height="620">
                    {{ __('t_ingame.layout.res_purchase_dm') }}
                </a>
            @endif
        @endif
    </div>
</div>
