<div id="ipioverviewlayer" data-title="{{ __('t_ingame.layout.menu_directives') }}">
    <div id="ipiOverviewHeadbar">{{ __('t_ingame.layout.menu_directives') }}</div>

    <div id="ipiOverviewChapters">
        <div class="ipiChapterItem active">
            <a class="ipiOverviewSelectChapter" href="{{ route('ipi.overview.layer') }}">
                {{ $chapterTitle }}
                @if ($unclaimedRewards > 0)
                    <span class="ipiHintCollect">{{ $unclaimedRewards }}</span>
                @endif
            </a>
        </div>
    </div>

    <div id="ipiOverviewChapterTitle">{{ $chapterTitle }}</div>

    <div id="ipiOverviewContent">
        <div id="ipiOverviewTasklist">
            @foreach ($tasks as $task)
                <div class="ipiTaskItem"
                     data-taskid="{{ $task['id'] }}"
                     data-state="{{ $task['state'] }}">
                    <div class="ipiTaskItemHeader">
                        <div class="ipiTaskItemTitle">
                            {{ $task['title'] }}
                            <span class="ipiTaskCompletedMark">&#10003;</span>
                        </div>
                        <a href="javascript:void(0);" class="ipiTaskItemTrack">
                            @if ($task['state'] === 'tracked')
                                Untrack task
                            @elseif ($task['state'] === 'collected')
                                Task collected
                            @elseif ($task['state'] === 'completed')
                                Collect reward
                            @else
                                Track task
                            @endif
                        </a>
                    </div>

                    <div class="ipiTaskItemProgress"
                         data-progress="{{ $task['progress'] }}"
                         data-total="{{ $task['total'] }}">
                        {{ $task['progress'] }}/{{ $task['total'] }}
                    </div>

                    <div class="ipiTaskItemContent" @if ($task['state'] !== 'tracked') style="display:none" @endif>
                        <div class="ipiTaskItemDescriptionInner">{{ $task['description'] }}</div>
                        <div class="ipiTaskItemContentCollect">
                            <a href="javascript:void(0);"
                               class="ipiOverviewCollectRewards claimTaskRewards {{ in_array($task['state'], ['completed', 'collected']) ? '' : 'disabled' }}">
                                @if ($task['state'] === 'collected')
                                    Task collected
                                @else
                                    Collect reward
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="ipiOverviewChapterRewards">
            <a href="javascript:void(0);"
               data-target="1"
               class="ipiOverviewCollectRewards {{ $unclaimedRewards > 0 ? '' : 'disabled' }}">
                {{ $unclaimedRewards > 0 ? 'Collect all chapter rewards' : 'Chapter collected' }}
            </a>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        IPI.initIpiLayer({
            trackTaskUrl: "{{ route('ipi.track-task') }}?",
            collectTaskUrl: "{{ route('ipi.collect-task') }}?",
            collectChapterUrl: "{{ route('ipi.collect-chapter') }}?",
            loca: {
                LOCA_IPI_UNTRACK_TASK: 'Untrack task',
                LOCA_IPI_TRACK_TASK: 'Track task',
                LOCA_IPI_CHAPTER_COLLECTED: 'Chapter collected',
                LOCA_IPI_TASK_COLLECTED: 'Task collected'
            }
        });
    });
</script>
