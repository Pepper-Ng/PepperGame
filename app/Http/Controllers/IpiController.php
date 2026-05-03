<?php

namespace OGame\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class IpiController extends OGameController
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private const TASKS = [
        5001 => [
            'title' => 'Establish your economy',
            'description' => 'Upgrade your mines and keep enough energy production online to maintain stable resource income.',
            'progress' => 2,
            'total' => 3,
            'completed' => false,
        ],
        5002 => [
            'title' => 'Visit the auctioneer',
            'description' => 'Open the auctioneer and place at least one bid to learn how item bidding works.',
            'progress' => 1,
            'total' => 1,
            'completed' => true,
        ],
        5003 => [
            'title' => 'Review your inbox',
            'description' => 'Open your messages and review new economy notifications so nothing remains unread.',
            'progress' => 1,
            'total' => 1,
            'completed' => true,
        ],
    ];

    public function menuContent(Request $request): View
    {
        $state = $this->getState($request);
        $trackedAction = $this->buildTrackedAction($state['trackedTaskId'] ?? null, $state['collectedTaskIds']);

        return view('ingame.ipi.menu-content', [
            'trackedActionTitle' => $trackedAction['title'],
            'unclaimedRewards' => $this->getUnclaimedRewardsCount($state['collectedTaskIds']),
        ]);
    }

    public function overviewLayer(Request $request): View
    {
        $state = $this->getState($request);

        return view('ingame.ipi.overview-layer', [
            'tasks' => $this->buildTaskViewModels($state['trackedTaskId'] ?? null, $state['collectedTaskIds']),
            'unclaimedRewards' => $this->getUnclaimedRewardsCount($state['collectedTaskIds']),
            'chapterTitle' => 'Getting started',
        ]);
    }

    public function trackTask(Request $request): Response
    {
        $taskId = (int)$request->query('taskId', 0);
        if (!isset(self::TASKS[$taskId])) {
            return $this->plainJson([
                'success' => false,
                'error' => 'Unknown directive task.',
                'newAjaxToken' => csrf_token(),
            ]);
        }

        $state = $this->getState($request);
        if (in_array($taskId, $state['collectedTaskIds'], true)) {
            $state['trackedTaskId'] = null;
        } else {
            $state['trackedTaskId'] = ($state['trackedTaskId'] ?? null) === $taskId ? null : $taskId;
        }

        $this->storeState($request, $state);

        return $this->plainJson([
            'success' => true,
            'trackedAction' => $this->buildTrackedAction($state['trackedTaskId'] ?? null, $state['collectedTaskIds']),
            'newAjaxToken' => csrf_token(),
        ]);
    }

    public function collectTask(Request $request): Response
    {
        $taskId = (int)$request->query('taskId', 0);
        if (!isset(self::TASKS[$taskId])) {
            return $this->plainJson([
                'success' => false,
                'error' => 'Unknown directive task.',
                'newAjaxToken' => csrf_token(),
            ]);
        }

        $state = $this->getState($request);
        if (!self::TASKS[$taskId]['completed']) {
            return $this->plainJson([
                'success' => false,
                'error' => 'This task is not completed yet.',
                'newAjaxToken' => csrf_token(),
            ]);
        }

        if (!in_array($taskId, $state['collectedTaskIds'], true)) {
            $state['collectedTaskIds'][] = $taskId;
        }

        if (($state['trackedTaskId'] ?? null) === $taskId) {
            $state['trackedTaskId'] = null;
        }

        $this->storeState($request, $state);

        return $this->plainJson([
            'success' => true,
            'unclaimedRewards' => $this->getUnclaimedRewardsCount($state['collectedTaskIds']),
            'claimedRewardsRendered' => 'Reward collected.',
            'newAjaxToken' => csrf_token(),
        ]);
    }

    public function collectChapter(Request $request): Response
    {
        $state = $this->getState($request);
        foreach (self::TASKS as $taskId => $task) {
            if ($task['completed'] && !in_array($taskId, $state['collectedTaskIds'], true)) {
                $state['collectedTaskIds'][] = $taskId;
            }
        }

        $state['trackedTaskId'] = null;
        $this->storeState($request, $state);

        return $this->plainJson([
            'success' => true,
            'claimedRewardsRendered' => 'All chapter rewards collected.',
            'newAjaxToken' => csrf_token(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getState(Request $request): array
    {
        return [
            'trackedTaskId' => $request->session()->get('ipi.tracked_task_id'),
            'collectedTaskIds' => array_values(array_unique(array_map('intval', $request->session()->get('ipi.collected_task_ids', [])))),
        ];
    }

    /**
     * @param array<string, mixed> $state
     */
    private function storeState(Request $request, array $state): void
    {
        $request->session()->put('ipi.tracked_task_id', $state['trackedTaskId']);
        $request->session()->put('ipi.collected_task_ids', $state['collectedTaskIds']);
    }

    /**
     * @param array<int> $collectedTaskIds
     * @return array<int, array<string, mixed>>
     */
    private function buildTaskViewModels(?int $trackedTaskId, array $collectedTaskIds): array
    {
        $tasks = [];
        foreach (self::TASKS as $taskId => $task) {
            $state = 'none';
            if (in_array($taskId, $collectedTaskIds, true)) {
                $state = 'collected';
            } elseif (!empty($task['completed'])) {
                $state = 'completed';
            } elseif ($trackedTaskId === $taskId) {
                $state = 'tracked';
            }

            $tasks[] = [
                'id' => $taskId,
                'title' => $task['title'],
                'description' => $task['description'],
                'progress' => (int)$task['progress'],
                'total' => (int)$task['total'],
                'state' => $state,
            ];
        }

        return $tasks;
    }

    /**
     * @param array<int> $collectedTaskIds
     * @return array<string, mixed>
     */
    private function buildTrackedAction(?int $trackedTaskId, array $collectedTaskIds): array
    {
        if ($trackedTaskId === null || !isset(self::TASKS[$trackedTaskId]) || in_array($trackedTaskId, $collectedTaskIds, true)) {
            return [
                'title' => '',
                'highlights' => [],
            ];
        }

        return [
            'title' => self::TASKS[$trackedTaskId]['title'],
            'highlights' => [],
        ];
    }

    /**
     * @param array<int> $collectedTaskIds
     */
    private function getUnclaimedRewardsCount(array $collectedTaskIds): int
    {
        $count = 0;
        foreach (self::TASKS as $taskId => $task) {
            if ($task['completed'] && !in_array($taskId, $collectedTaskIds, true)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Return text-based JSON because the existing IPI frontend explicitly calls JSON.parse() on the raw response.
     *
     * @param array<string, mixed> $payload
     */
    private function plainJson(array $payload): Response
    {
        return response(json_encode($payload), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
