<div id="itemDetails" data-uuid="{{ $item['ref'] }}">
    <div id="content">
        <a class="close_details" href="javascript:void(0);" ref="{{ $item['ref'] }}"></a>

        <h2>{{ $item['title'] }}</h2>

        <div id="pic" style="background: url('{{ $item['image_path'] }}') center center / 180px 180px no-repeat;"></div>

        <div id="features">
            <span class="level">{{ $item['price_label'] }} {{ __('t_ingame.shop.dm_abbreviation') }}</span>

            <p class="curr_planet">{!! $item['description'] !!}</p>
            <p><span class="more_info"><strong>{{ __('t_ingame.shop.item_duration') }}:</strong> {{ $item['duration'] }}</span></p>
            <p><span class="more_info"><strong>{{ __('t_ingame.shop.item_price') }}:</strong> {{ number_format($item['price'], 0, '', '.') }} {{ __('t_ingame.shop.dark_matter') }}</span></p>
            <p><span class="more_info"><strong>{{ __('t_ingame.shop.item_in_inventory') }}:</strong> 0</span></p>

            <a href="{{ route('payment.overlay') }}"
               class="overlay btn btn_confirm"
               data-overlay-title="{{ __('t_ingame.layout.res_purchase_dm') }}"
               data-overlay-class="payment"
               data-overlay-popup-width="800"
               data-overlay-popup-height="620">
                <span class="textlabel">{{ __('t_ingame.layout.res_purchase_dm') }}</span>
            </a>
        </div>
    </div>
</div>
