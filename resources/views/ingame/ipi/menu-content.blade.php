<div id="ipimenucontent">
    <a href="{{ route('ipi.overview.layer') }}"
       class="overlay textBeefy"
       data-overlay-title="{{ __('t_ingame.layout.menu_directives') }}"
       id="ipiInnerMenuContentHolder">
        <div class="ipiMenuHead">
            {{ __('t_ingame.layout.menu_directives') }}
            @if ($unclaimedRewards > 0)
                <span class="ipiHintCollect">{{ $unclaimedRewards }}</span>
            @endif
        </div>

        <div class="ipiMenuBody {{ $trackedActionTitle === '' ? 'hidden' : '' }}">{{ $trackedActionTitle }}</div>
        <div class="ipiMenuFooter {{ $trackedActionTitle === '' ? 'hidden' : '' }}"></div>
    </a>
</div>
