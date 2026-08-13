<?php

use App\Models\Task;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kanban board')] #[Layout('layouts::base')]class extends Component
{

    public string $new_task_title = '';

    #[Computed]
    public function columns(): Collection
    {

        return Task::orderBy('position')
                    ->get()
                    ->groupBy('status')
                    ->union(['todo' => collect(), 'in-progress' => collect(), 'done' => collect()]);

    }

    public function handleSort(int|string $id, string $position, string $status):void
    {

        $task = Task::findOrFail($id);

        $old_satatus = $task->status;

        $task->update(['status' => $status]);

        /* Get all siblings in the same column (excluding the moved task) */
        $siblings = Task::where('status', $status)
                            ->where('id', '!=', $id)
                            ->orderBy('position')
                            ->pluck('id')
                            ->all();

        /* Insert at the correct position */
        array_splice($siblings, $position, 0, [$task->id]);

        /* Update all positions */
        foreach ($siblings as $index => $taskId) {

            Task::where('id', $taskId)->update(['position' => $index]);

        }

        if($old_satatus != $status){

            $this->reorder($old_satatus);

        }

        unset($this->columns);

    }

    public function reorder(string $status): void
    {

        Task::where('status', $status)
                ->orderBy('position')
                ->get()
                ->each(fn (Task $task, int $position) => $task->update(['postion' => $position]));

    }

    public function addTask(string $status): void
    {

        $title = trim($this->new_task_title);

        if($title === '') return;

        Task::create([
            'title' => $this->new_task_title,
            'position' => Task::where('status', $status)->max('position') + 1,
            'status' => $status
        ]);

        $this->new_task_title = '';

        unset($this->columns);

    }

    public function deleteTask(int $taskId): void
    {

        $task = Task::findOrFail($taskId);

        $status = $task->status;

        $task->delete();

        $this->reorder($status);

        unset($this->columns);

    }

    public function handleSortGroup(): void
    {}

};
?>

<div class="min-h-screen bg-gray-100 py-8">

    <div class="max-w-6xl mx-auto px-4">

        <h1 class="text-2xl font-bold text-gray-900 mb-6">Kanban board</h1>

        <div
            wire:sort="handleSortGroup"
            wire:sort:group="status"
            wire:sort:group-id="statusess"
            class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 items-start gap-6">

            @php
                $column_config = [
                    'todo' => ['label' => 'To do', 'color' => 'bg-blue-500'],
                    'in-progress' => ['label' => 'In progress', 'color' => 'bg-amber-500'],
                    'done' => ['label' => 'Done', 'color' => 'bg-green-500'],
                ];

            @endphp

            @foreach ($column_config as $status => $config)

                <div
                    wire:sort:item="{{ $status }}"
                    class="bg-gray-200/60 rounded-xl p-4 cursor-grab active:cursor-grabbing group-[status]:">

                    {{-- Column header --}}
                    <div class="flex items-center gap-2 mb-4">

                        <span class="w-2.5 h-2.5 rounded-full {{ $config['color'] }}"></span>

                        <h2 class="font-semibold text-gray-700">{{ $config['label'] }}</h2>

                        <span class="ml-auto text-sm text-gray-500 bg-white/70 rounded-full px-2 py-0.5">{{ $this->columns[$status]->count() }}</span>

                    </div>

                    {{-- Task list --}}
                    <ul
                        wire:sort="handleSort"
                        wire:sort:group="tasks"
                        wire:sort:group-id="{{ $status }}"
                        class="space-y-2 min-h-15">

                        @forelse ($this->columns[$status] as $task)

                            <li
                                wire:key="task-{{ $task->id }}"
                                wire:sort:item="{{ $task->id }}"
                                class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm cursor-grab active:cursor-grabbing group">

                                <div class="flex items-start justify-between gap-2">

                                    <span class="text-sm text-gray-800">{{ $task->title }}</span>

                                    <button
                                        wire:click="deleteTask({{ $task->id }})"
                                        wire:sort:ignore
                                        wire:confirm="Are you sure you want to delete the task?"
                                        class="text-gray-400 hover:text-red-500 transition-colors shrink-0 opacity-0 group-hover:opacity-100 in-[.sorting]:opacity-0!">

                                        <x-icon.close />

                                    </button>

                                </div>

                            </li>

                        @empty

                            <li class="text-sm text-gray-400 text-center py-4">No tasks</li>

                        @endforelse

                    </ul>

                    <div
                        x-data="{ adding: false}"
                        class="mt-3">

                        <button
                            x-show="!adding"
                            @click="adding = true; $nextTick(() => $refs.input.focus())"
                            class="w-full text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 py-1.5 transition-colors">

                            <x-icon.plus />

                            Add task

                        </button>

                        <form
                            x-cloak
                            @keydown.escape="adding = false"
                            @click.outside="adding = false"
                            x-show="adding"
                            wire:submit="addTask('{{ $status }}')"
                            class="space-y-2">

                            <input
                                type="text"
                                x-ref="input"
                                placeholder="Task title..."
                                wire:model="new_task_title"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                            <div class="flex gap-2">

                                <button
                                    type="submit"
                                    class="text-sm bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition-colors">

                                    Add

                                </button>

                                <button
                                    type="button"
                                    @click="adding = false"
                                    class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5 transition-colors">

                                    Cancel

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>
