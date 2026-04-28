<div id="content">
    <a class="close_details" href="javascript:void(0);" ref="{{ $officer['ref'] }}"></a>

    <h2>{{ $officer['title'] }}</h2>

    <div class="image officers200 {{ $officer['image_class'] }}"></div>

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

        <div id="wrapper">
            <a href="{{ route('payment.overlay') }}"
               class="overlay btn btn_confirm"
               data-overlay-title="{{ __('t_ingame.layout.res_purchase_dm') }}"
               data-overlay-class="payment"
               data-overlay-popup-width="800"
               data-overlay-popup-height="620">
                {{ __('t_ingame.layout.res_purchase_dm') }}
            </a>
        </div>
    </div>
</div>