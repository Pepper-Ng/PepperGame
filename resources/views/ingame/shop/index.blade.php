@extends('ingame.layouts.main')

@section('content')

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

<div id="eventboxContent" style="display: none">
        <img height="16" width="16" src="/img/icons/3f9884806436537bdec305aa26fc60.gif">
    </div>


    <div id="inhalt">
        <div id="planet">
            <div id="header_text">
                <h2>
                    {{ __('t_ingame.shop.page_title') }}            </h2>
            </div>

            <div id="detail" class="detail_screen small">
                <div id="techDetailLoading"></div>
            </div>

        </div>
        <div class="c-left"></div>
        <div class="c-right"></div>

        <div id="buttonz">
            <div class="header">
                <h2>{{ __('t_ingame.shop.page_title') }}</h2>
            </div>
            <div class="content">
                <button class="to_shop active tooltip js_hideTipOnMobile" title="{{ __('t_ingame.shop.tooltip_shop') }}">
                    <span class="to_shop_icon">{{ __('t_ingame.shop.btn_shop') }}</span>
                </button>
                <button class="to_inventory tooltip js_hideTipOnMobile" title="{{ __('t_ingame.shop.tooltip_inventory') }}">
        <span class="to_inventory_icon">
            {{ __('t_ingame.shop.btn_inventory') }}            </span>
                </button>

                <div id="itemBox" class="border5px">
                    <div class="aside">
                        <ul class="listfilter border5px categoryFilter">
                            <li class="border5px inShop active">
                                <a href="javascript:void(0);" rel="c18170d3125b9941ef3a86bd28dded7bf2066a6a" class="active">
                            <span>
                                {{ __('t_ingame.shop.category_special_offers') }} (<span class="amount">0</span>)
                            </span>
                                </a>
                            </li>
                            <li class="border5px inShop inInventory">
                                <a href="javascript:void(0);" rel="d8d49c315fa620d9c7f1f19963970dea59a0e3be">
                            <span>
                                {{ __('t_ingame.shop.category_all') }} (<span class="amount">0</span>)
                            </span>
                                </a>
                            </li>
                            <li class="border5px inShop inInventory">
                                <a href="javascript:void(0);" rel="e71139e15ee5b6f472e2c68a97aa4bae9c80e9da">
                            <span>
                                {{ __('t_ingame.shop.category_resources') }} (<span class="amount">0</span>)
                            </span>
                                </a>
                            </li>
                            <li class="border5px inShop inInventory">
                                <a href="javascript:void(0);" rel="cccaafe693a53e8d1e791f06327974539da5978f">
                            <span>
                                {{ __('t_ingame.shop.category_buddy_items') }} (<span class="amount">0</span>)
                            </span>
                                </a>
                            </li>
                            <li class="border5px inShop inInventory">
                                <a href="javascript:void(0);" rel="dc9ec90e5a2163cc063b8bb3e9fe392782f565c8">
                            <span>
                                {{ __('t_ingame.shop.category_construction') }} (<span class="amount">0</span>)
                            </span>
                                </a>
                            </li>
                        </ul>
                        <div class="btn_wrap">
                            <a href="#" tabindex="1" class="btn btn_confirm buyResourcesLink">
                                {{ __('t_ingame.shop.btn_get_more_resources') }}                    </a>
                        </div>
                        <div class="btn_wrap">
                            <a href="{{ route('payment.overlay') }}"
                               tabindex="2"
                               class="btn btn_confirm overlay"
                               data-overlay-title="{{ __('t_ingame.layout.res_purchase_dm') }}"
                               data-overlay-class="payment"
                               data-overlay-popup-width="800"
                               data-overlay-popup-height="620">
                                {{ __('t_ingame.shop.btn_purchase_dark_matter') }}
                            </a>
                        </div>
                    </div>


                    <div id="js_shopSliderBox" class="shop_slider"><div class="anythingSlider anythingSlider-default activeSlider" style="width: 335px; height: 332px;"><div class="anythingWindow" style="width: 335px; height: 332px;"><ul id="js_shopSlider" class="anythingBase horizontal" style="width: 335px; left: 0px;">
<li class="slide_0 panel activePage" style="width: 335px; height: 332px;">
    @foreach($shopItems as $item)
        @php
            $tooltip = $item['title'] . '|' . $item['description'] . '<br /><br />' .
                       __('t_ingame.shop.item_duration') . ': ' . __('t_ingame.shop.now') . '<br /><br />' .
                       __('t_ingame.shop.item_price') . ': ' . number_format($item['price'], 0, '', '.') . ' ' . __('t_ingame.shop.dark_matter') . '<br />' .
                       __('t_ingame.shop.item_in_inventory') . ': 0';
        @endphp
        <div class="item_img r_{{ $item['rarity'] }}" data-categories="{{ implode(',', $item['category_refs']) }}" data-ref="{{ $item['ref'] }}" style="background-image: url({{ $item['image_path'] }});">
            <div class="item_img_box">
                <div class="activation disabled"></div>
                <a href="javascript:void(0);" tabindex="1" title="{{ $tooltip }}" class="detail_button tooltipHTML js_hideTipOnMobile slideIn shop_item_trigger" ref="{{ $item['ref'] }}">
                    <div class="sale_badge disabled"></div>
                    <span class="ecke"><span class="level price">{{ $item['price_label'] }} DM</span></span>
                </a>
            </div>
        </div>
    @endforeach
</li>
</ul></div><div class="anythingControls" style="display: none;"><ul class="thumbNav" style="display: none;"></ul></div><span class="arrow back disabled" style="display: none;"><a href="#"><span>┬½</span></a></span><span class="arrow forward disabled" style="display: none;"><a href="#"><span>┬╗</span></a></span></div></div>
                    <div id="js_shopEmptyState" class="border5px" style="display:none; padding:20px; margin:2px; color:#848484; text-align:left;">
                        {{ __('t_ingame.shop.feature_coming_soon') }}
                    </div>

                    <div id="js_inventorySliderBox" class="inventory_slider" style="display:none; padding:20px; margin:2px; color:#848484; text-align:left;">
                        {{ __('t_ingame.shop.feature_coming_soon') }}
                    </div>
                </div>        <div class="footer"></div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        detailUrl = {!! json_encode(route('shop.detail')) !!};

        $(function () {
            var defaultCategory = $('.categoryFilter a:first').attr('rel');
            var $shopBox = $('#js_shopSliderBox');
            var $inventoryBox = $('#js_inventorySliderBox');
            var $shopItems = $shopBox.find('.item_img');
            var $categoryLinks = $('.categoryFilter li a');
            var $shopButton = $('button.to_shop');
            var $inventoryButton = $('button.to_inventory');
            var currentPage = null;
            var currentCategory = null;
            var currentItem = null;
            var suppressStateSync = false;

            function updateCategoryCounts() {
                var counts = {};

                $categoryLinks.each(function () {
                    counts[$(this).attr('rel')] = 0;
                });

                $shopItems.each(function () {
                    var categories = String($(this).data('categories') || '').split(',');

                    categories.forEach(function (category) {
                        if (category.length) {
                            counts[category] = (counts[category] || 0) + 1;
                        }
                    });
                });

                $categoryLinks.each(function () {
                    var rel = $(this).attr('rel');
                    $(this).find('.amount').text(counts[rel] || 0);
                });
            }

            function closeOpenDetail() {
                var $active = $('.shop_item_trigger.active').first();

                if ($active.length) {
                    $active.trigger('click');
                } else if (window.gfSlider !== undefined) {
                    gfSlider.hide(getElementByIdWithCache('detail'));
                }

                $('.shop_item_trigger.active').removeClass('active');
                currentItem = null;
            }

            function setPage(page) {
                currentPage = page === 'inventory' ? 'inventory' : 'shop';

                $shopButton.toggleClass('active', currentPage === 'shop');
                $inventoryButton.toggleClass('active', currentPage === 'inventory');
                $shopBox.toggle(currentPage === 'shop');
                $inventoryBox.toggle(currentPage === 'inventory');

                $('#buttonz h2').text(currentPage === 'shop'
                    ? {!! json_encode(__('t_ingame.shop.page_title')) !!}
                    : {!! json_encode(__('t_ingame.shop.btn_inventory')) !!});
            }

            function setCategory(category) {
                var visibleCount = 0;

                currentCategory = $categoryLinks.filter('[rel="' + category + '"]').length ? category : defaultCategory;
                $categoryLinks.removeClass('active').parent().removeClass('active');
                $categoryLinks.filter('[rel="' + currentCategory + '"]').addClass('active').parent().addClass('active');

                if (currentPage !== 'shop') {
                    $('#js_shopEmptyState').hide();
                    return;
                }

                $shopItems.each(function () {
                    var categories = String($(this).data('categories') || '').split(',');
                    var isVisible = categories.indexOf(currentCategory) !== -1;

                    $(this).toggle(isVisible);
                    visibleCount += isVisible ? 1 : 0;
                });

                $('#js_shopEmptyState').toggle(visibleCount === 0);
            }

            function pushState(state) {
                if (!suppressStateSync) {
                    $.bbq.pushState(state);
                }
            }

            function applyState(fragment) {
                var page = fragment.page === 'inventory' ? 'inventory' : 'shop';
                var category = typeof fragment.category === 'string' && fragment.category.length ? fragment.category : defaultCategory;
                var item = typeof fragment.item === 'string' ? fragment.item : '';

                if (typeof fragment.page === 'undefined') {
                    $.bbq.pushState({
                        page: page,
                        category: category,
                        item: item
                    });
                    return;
                }

                setPage(page);
                setCategory(category);

                if (currentPage !== 'shop') {
                    if (currentItem !== null) {
                        suppressStateSync = true;
                        closeOpenDetail();
                        suppressStateSync = false;
                    }
                    return;
                }

                if (item && item !== currentItem) {
                    var $target = $('.shop_item_trigger[ref="' + item + '"]').first();

                    if ($target.length) {
                        var itemCategories = String($target.closest('.item_img').data('categories') || '').split(',').filter(Boolean);

                        if (itemCategories.length && itemCategories.indexOf(currentCategory) === -1) {
                            $.bbq.pushState({
                                page: 'shop',
                                category: itemCategories[0],
                                item: item
                            });
                            return;
                        }

                        suppressStateSync = true;
                        currentItem = item;
                        $target.trigger('click');
                        suppressStateSync = false;
                    }
                } else if (!item && currentItem !== null) {
                    suppressStateSync = true;
                    closeOpenDetail();
                    suppressStateSync = false;
                }
            }

            updateCategoryCounts();

            $categoryLinks.on('click.shopPage', function (event) {
                event.preventDefault();
                pushState({
                    page: currentPage || 'shop',
                    category: $(this).attr('rel'),
                    item: ''
                });
            });

            $shopButton.on('click.shopPage', function (event) {
                event.preventDefault();
                pushState({
                    page: 'shop',
                    category: currentCategory || defaultCategory,
                    item: ''
                });
            });

            $inventoryButton.on('click.shopPage', function (event) {
                event.preventDefault();
                pushState({
                    page: 'inventory',
                    category: currentCategory || defaultCategory,
                    item: ''
                });
            });

            $(document).on('click.shopPage', '.shop_item_trigger', function () {
                var ref = $(this).attr('ref');
                var nextItem;

                if (suppressStateSync) {
                    return;
                }

                nextItem = currentItem === ref ? '' : ref;
                currentItem = nextItem || null;

                pushState({
                    page: 'shop',
                    category: currentCategory || defaultCategory,
                    item: nextItem
                });
            });

            $(document).on('click.shopPage', '#itemDetails a.close_details', function () {
                if (suppressStateSync) {
                    return;
                }

                $('.shop_item_trigger.active').removeClass('active');
                currentItem = null;

                pushState({
                    page: currentPage || 'shop',
                    category: currentCategory || defaultCategory,
                    item: ''
                });
            });

            $(window).off('hashchange.shopPage').on('hashchange.shopPage', function (event) {
                applyState($.deparam.fragment(event.fragment));
            });

            applyState($.deparam.fragment());
        });
    </script>

@endsection
