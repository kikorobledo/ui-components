<?php

use App\Enums\Enums\AreaEnum;
use App\Enums\Enums\ColorEnum;
use App\Models\Group;
use App\Models\Phase;
use App\Models\Task;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kanban board')] #[Layout('layouts::base')]class extends Component
{

    public Group $group;
    public Task $task_selected;

    public string $phase_name = '';
    public int $phase_order = 0;
    public string $phase_color = '';

    public string $task_title = '';
    public string $task_description = '';

    public string $group_name = '';
    public string $group_area = '';

    public array $areas;
    public array $colors;
    public int $phase_id;
    public string $mode = '';

    public function mount(){

        $this->colors = ColorEnum::cases();

        $this->areas = AreaEnum::cases();

    }

    public function handleSort(int|string $id, string $position, int|string $phase_id):void
    {

        $task = Task::findOrFail($id);

        $old_phase_id = $task->phase_id;

        $task->update(['phase_id' => $phase_id]);

        /* Get all siblings in the same column (excluding the moved task) */
        $siblings = Task::where('phase_id', $phase_id)
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

        if($old_phase_id != $phase_id){

            $this->reorder($old_phase_id);

        }

        $this->group->refresh();

    }

    public function handleSortGroup(int|string $id, string $position, int|string $group_id): void
    {

        $phase = Phase::findOrFail($id);

        /* Get all siblings in the same column (excluding the moved task) */
        $siblings = Phase::where('id', '!=', $phase->id)
                            ->orderBy('order')
                            ->pluck('id')
                            ->all();

        /* Insert at the correct position */
        array_splice($siblings, $position, 0, [$phase->id]);

        /* Update all positions */
        foreach ($siblings as $index => $phaseId) {

            Phase::where('id', $phaseId)->update(['order' => $index]);

        }

        $this->group->refresh();

    }

    public function reorder(int $phase_id): void
    {

        Task::where('phase_id', $phase_id)
                ->orderBy('position')
                ->get()
                ->each(fn (Task $task, int $position) => $task->update(['postion' => $position]));

    }

    public function deleteTask(int $taskId): void
    {

        $task = Task::findOrFail($taskId);

        $task->delete();

        $this->reorder($task->phase_id);

        $this->group->refresh();

    }

    public function openCreateTaskModal(int $phase_id):void
    {

        $this->reset(['task_title', 'task_description']);

        $this->phase_id = $phase_id;

        $this->mode = 'create';

        $this->dispatch('open-task-modal');

    }

    public function openEditTaskModal(Task $task):void
    {

        $this->task_selected = $task;

        $this->task_title = $task->title;

        $this->task_description = $task->description;

        $this->mode = 'edit';

        $this->dispatch('open-task-modal');

    }

    public function savePhase():void
    {

        $this->validate([
            'phase_name' => 'required|string|min:3|max:255',
            'phase_order' => 'required|numeric|min:1',
            'phase_color' => ['required', 'string', Rule::in($this->colors)]
        ]);

        $phase_to_replace = $this->group->phases()->where('order', $this->phase_order)->first();

        if($phase_to_replace){

            $phases_to_reorder = $this->group->phases()->where('order', '>=', $this->phase_order)->get();

            foreach ($phases_to_reorder as $phase) {

                $phase->update(['order' => $phase->order + 1]);

            }

        }

        $this->group->phases()->create([
            'name' => trim($this->phase_name),
            'order' => $this->phase_order,
            'color' => $this->phase_color,
        ]);

        $this->group->refresh();

        $this->dispatch('close-phase-modal');

        $this->reset(['phase_name', 'phase_order', 'phase_color']);

    }

    public function saveTask():void
    {

        $this->validate([
            'task_title' => 'required|string|min:3',
            'task_description' => 'nullable|string',
            'phase_id' => 'required|int'
        ]);

        Task::create([
            'title' => trim($this->task_title),
            'description' => trim($this->task_description),
            'position' => Task::max('position') + 1,
            'phase_id' => $this->phase_id,
            'created_by' => auth()->id()
        ]);

        $this->group->refresh();

        $this->dispatch('close-task-modal');

        $this->reset(['task_title', 'task_description', 'phase_id']);

    }

    public function updateTask():void
    {

        $this->validate([
            'task_title' => 'required|string|min:3',
            'task_description' => 'nullable|string'
        ]);

        $this->task_selected->update([
            'title' => trim($this->task_title),
            'description' => trim($this->task_description),
            'updated_by' => auth()->id()
        ]);

        $this->group->refresh();

        $this->dispatch('close-task-modal');

        $this->reset(['task_title', 'task_description', 'phase_id']);

    }

    public function saveGroup()
    {

        $this->validate([
            'group_name' => 'required|string|min:3',
            'group_area' => ['required', 'string', Rule::in($this->areas)],
        ]);

        $group = Group::create([
            'name' => trim($this->group_name),
            'area' => trim($this->group_area),
            'status' => 'active',
            'created_by' => auth()->id()
        ]);

        return redirect()->route('kanban-board', $group);

    }

};
?>

<div
    x-data="{ open_phase: false, open_task: false, open_group: false, open_drop_down:false }"
    @keydown.escape.window="open_phase = false, open_task= false"
    @close-phase-modal.window="open_phase = false"
    @close-task-modal.window="open_task = false"
    @open-task-modal="open_task = true"
    class="min-h-screen bg-gray-100 py-8">

    <div class="max-w-6xl mx-auto px-4 mb-5 text-sm space-y-4">

        <div class="flex justify-between items-center">

            <div class="relative">

                <span x-on:click="open_drop_down=true" class="px-2 py-0.5 text-gray-500  hover:bg-gray-300 hover:text-gray-700 transition-colors cursor-pointer border border-gray-500 rounded-full">
                    Selecciona un grupo
                </select>

                <div x-cloak x-show="open_drop_down" x-on:click.away="open_drop_down=false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu">

                    @foreach (auth()->user()->groups as $group_item)

                        <div class="flex items-center justify-between">

                            <a href="{{ route('kanban-board', $group_item) }}" class="w-full rounded-lg mx-1 block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">{{ $group_item->name }}</a>

                            <button
                                @click="open_group = ! open_group"
                                class="px-2 py-0.5 text-gray-500  hover:bg-gray-300 hover:text-gray-700 transition-colors cursor-pointer border border-gray-500 rounded-full mr-2">

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>

                            </button>

                        </div>

                    @endforeach

                </div>

            </div>

            <div>

                <button
                    @click="open_group = ! open_group"
                    class="px-2 py-0.5 text-gray-500  hover:bg-gray-300 hover:text-gray-700 transition-colors cursor-pointer border border-gray-500 rounded-full">

                    Crear un nuevo grupo

                </button>

            </div>

        </div>

        <hr>

    </div>

    {{-- Header --}}
    <div class="max-w-6xl mx-auto px-4">

        <div class="flex justify-between items-center">

            <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $group->name }}</h1>

            <div>

                <button
                    @click="open_phase = ! open_phase"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors cursor-pointer">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>

                </button>

            </div>

        </div>

        <div
            wire:sort="handleSortGroup"
            wire:sort:group="phases"
            wire:sort:group-id="grup_id"
            class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 items-start gap-6">

            @foreach ($group->phases->sortBy('order') as $phase)

                <div
                    wire:sort:item="{{ $phase->id }}"
                    class="bg-gray-200/60 rounded-xl p-4 cursor-grab active:cursor-grabbing group-[status]:">

                    {{-- Column header --}}
                    <div class="flex items-center gap-2 mb-4">

                        <span class="w-2.5 h-2.5 rounded-full bg-{{ $phase->color }}-500"></span>

                        <h2 class="font-semibold text-gray-700">{{ $phase->name }}</h2>

                        <span class="ml-auto text-sm text-gray-500 bg-white/70 rounded-full px-2 py-0.5">{{ $phase->tasks->count() }}</span>

                    </div>

                    {{-- Task list --}}
                    <ul
                        wire:sort="handleSort"
                        wire:sort:group="tasks"
                        wire:sort:group-id="{{ $phase->id }}"
                        class="space-y-2 min-h-15">

                        @forelse ($phase->tasks->sortBy('position') as $task)

                            <li
                                wire:key="task-{{ $task->id }}"
                                wire:sort:item="{{ $task->id }}"
                                class="bg-white rounded-lg border border- p-3 shadow-sm cursor-grab active:cursor-grabbing group">

                                <div class="flex items-start justify-between gap-2">

                                    <span class="text-sm text-gray-800">{{ $task->title }}</span>

                                    <div class="flex items-center gap-2">

                                        <button
                                            wire:click="openEditTaskModal({{ $task->id }})"
                                            wire:sort:ignore
                                            class="cursor-pointer text-gray-400 hover:text-blue-500 transition-colors shrink-0 opacity-0 group-hover:opacity-100 in-[.sorting]:opacity-0!">

                                            <x-icon.pencil />

                                        </button>

                                        <button
                                            wire:click="deleteTask({{ $task->id }})"
                                            wire:sort:ignore
                                            wire:confirm="Are you sure you want to delete the task?"
                                            class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors shrink-0 opacity-0 group-hover:opacity-100 in-[.sorting]:opacity-0!">

                                            <x-icon.close />

                                        </button>

                                    </div>

                                </div>

                            </li>

                        @empty

                            <li class="text-sm text-gray-400 text-center py-4">No tasks</li>

                        @endforelse

                    </ul>

                    {{-- Add task --}}
                    <div class="mt-3">

                        <button
                            wire:click="openCreateTaskModal({{ $phase->id }})"
                            class="w-full text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 py-1.5 transition-colors cursor-pointer">

                            <x-icon.plus />

                            Add task

                        </button>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

    {{-- Group modal --}}
    <div
        x-cloak
        x-show="open_group"
        x-transition.opacity
        class="fixed inset-0 z-50">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50"></div>

        <!-- Modal Content -->
        <div class="fixed inset-0 overflow-y-auto">

            <div class="flex min-h-full items-center justify-center p-4">

                <div class="relative bg-white rounded-lg max-w-lg w-full max-h-[85vh] flex flex-col">

                    <!-- Header -->
                    <div class="px-5 py-4 border-b border-gray-200 shrink-0">

                        <button
                            @click="open_group = false"
                            class="absolute top-3.5 right-3.5 text-gray-400 hover:text-gray-600 cursor-pointer"
                        >
                            <x-icon.close />
                        </button>

                        <h2 class="text-sm font-semibold text-gray-900">Create Group</h2>

                    </div>

                    <!-- Content Area - Scrollable -->
                    <div class="flex-1 overflow-y-auto px-5 py-5">

                        <div wire:transition="form" class="space-y-4">

                            <div>

                                <label class="block text-xs font-medium text-gray-700 mb-1">Group Name</label>

                                <input
                                    type="text"
                                    wire:model.blur="group_name"
                                    class="w-full px-2.5 py-1.5 text-sm text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g., Tailwind UI Kit"
                                >

                                @error('group_name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror

                            </div>

                            <div>

                                <label class="block text-xs font-medium text-gray-700 mb-1">Group Area</label>

                                <select
                                    wire:model.change="group_area"
                                    class="w-full px-2.5 py-1.5 text-sm text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Select a category...</option>

                                    @foreach ($areas as $area)

                                        <option value="{{ $area->value }}" class="capitalize">{{ $area->label() }}</option>

                                    @endforeach

                                </select>

                                @error('group_area') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror

                            </div>

                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-5 py-3.5 border-t border-gray-200 shrink-0 flex justify-between">

                        <div class="flex gap-2 ml-auto">

                            <button
                                @click="open_group = false"
                                class="px-3 py-1.5 text-xs text-gray-700 hover:text-gray-900 cursor-pointer"
                            >
                                Cancel
                            </button>

                            <button
                                wire:click="saveGroup"
                                class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-md hover:bg-green-700 data-loading:opacity-50 data-loading:pointer-events-none"
                            >
                                Submit Group
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Phase modal --}}
    <div
        x-cloak
        x-show="open_phase"
        x-transition.opacity
        class="fixed inset-0 z-50">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50"></div>

        <!-- Modal Content -->
        <div class="fixed inset-0 overflow-y-auto">

            <div class="flex min-h-full items-center justify-center p-4">

                <div class="relative bg-white rounded-lg max-w-lg w-full max-h-[85vh] flex flex-col">

                    <!-- Header -->
                    <div class="px-5 py-4 border-b border-gray-200 shrink-0">

                        <button
                            @click="open_phase = false"
                            class="absolute top-3.5 right-3.5 text-gray-400 hover:text-gray-600 cursor-pointer"
                        >
                            <x-icon.close />
                        </button>

                        <h2 class="text-sm font-semibold text-gray-900">Create Phase</h2>

                    </div>

                    <!-- Content Area - Scrollable -->
                    <div class="flex-1 overflow-y-auto px-5 py-5">

                        <div wire:transition="form" class="space-y-4">

                            <div>

                                <label class="block text-xs font-medium text-gray-700 mb-1">Phase Name</label>

                                <input
                                    type="text"
                                    wire:model.blur="phase_name"
                                    class="w-full px-2.5 py-1.5 text-sm text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g., Tailwind UI Kit"
                                >

                                @error('phase_name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror

                            </div>

                            <div>

                                <label class="block text-xs font-medium text-gray-700 mb-1">Phase Order</label>

                                <input
                                    type="number"
                                    wire:model.blur="phase_order"
                                    class="w-full px-2.5 py-1.5 text-sm text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g., Tailwind UI Kit"
                                >

                                @error('phase_order') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror

                            </div>

                            <div>

                                <label class="block text-xs font-medium text-gray-700 mb-1">Phse Color</label>

                                <select
                                    wire:model.change="phase_color"
                                    class="w-full px-2.5 py-1.5 text-sm text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Select a category...</option>

                                    @foreach ($colors as $color)

                                        <option value="{{ $color->value }}" class="capitalize">{{ $color }}</option>

                                    @endforeach

                                </select>

                                @error('phase_color') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror

                            </div>

                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-5 py-3.5 border-t border-gray-200 shrink-0 flex justify-between">

                        <div class="flex gap-2 ml-auto">

                            <button
                                @click="open_phase = false"
                                class="px-3 py-1.5 text-xs text-gray-700 hover:text-gray-900 cursor-pointer"
                            >
                                Cancel
                            </button>

                            <button
                                wire:click="savePhase"
                                class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-md hover:bg-green-700 data-loading:opacity-50 data-loading:pointer-events-none"
                            >
                                Submit Phase
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Task modal --}}
    <div
        x-cloak
        x-show="open_task"
        x-transition.opacity
        class="fixed inset-0 z-50">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50"></div>

        <!-- Modal Content -->
        <div class="fixed inset-0 overflow-y-auto">

            <div class="flex min-h-full items-center justify-center p-4">

                <div class="relative bg-white rounded-lg max-w-lg w-full max-h-[85vh] flex flex-col">

                    <!-- Header -->
                    <div class="px-5 py-4 border-b border-gray-200 shrink-0">

                        <button
                            @click="open_task = false"
                            class="absolute top-3.5 right-3.5 text-gray-400 hover:text-gray-600 cursor-pointer"
                        >
                            <x-icon.close />
                        </button>

                        @if($mode === 'create')

                            <h2 class="text-sm font-semibold text-gray-900">Create Task</h2>

                        @elseif($mode === 'edit')

                            <h2 class="text-sm font-semibold text-gray-900">Update Task</h2>

                        @endif

                    </div>

                    <!-- Content Area - Scrollable -->
                    <div class="flex-1 overflow-y-auto px-5 py-5">

                        <div wire:transition="form" class="space-y-4">

                            <div>

                                <label class="block text-xs font-medium text-gray-700 mb-1">Task Name</label>

                                <input
                                    type="text"
                                    wire:model.blur="task_title"
                                    class="w-full px-2.5 py-1.5 text-sm text-gray-700 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g., Tailwind UI Kit"
                                >

                                @error('task_title') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror

                            </div>

                            <div>

                                <label class="block text-xs font-medium text-gray-700 mb-1">Task Description</label>

                                <textarea
                                    x-data="{
                                        resize(){
                                            this.$el.style.height = 'auto';
                                            this.$el.style.height = this.$el.scrollHeight + 'px';
                                        }
                                    }"
                                    x-init="resize()"
                                    @input="resize()"
                                    wire.ignore.self
                                    rows="10"
                                    class="border border-gray-200 rounded-lg mt-3 text-gray-700 leading-relaxed px-2.5 py-1.5 min-h-40 w-full focus:outline-none focus:ring-0 resize-none field-sizing-content"
                                    placeholder="Click here to add notes"
                                    wire:model="task_description">
                                </textarea>

                                @error('task_description') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror

                            </div>

                            <div class=" text-xs text-gray-700">

                                <div>
                                    <strong>Created by:</strong> {{ $task_selected?->createdBy?->name }}, {{ $task_selected?->created_at_formatted }}
                                </div>

                                <div>
                                    <strong>Updated by:</strong> {{ $task_selected?->updatedBy?->name }}, {{ $task_selected?->updated_at_formatted }}
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-5 py-3.5 border-t border-gray-200 shrink-0 flex justify-between">

                        <div class="flex gap-2 ml-auto">

                            <button
                                @click="open_task = false"
                                class="px-3 py-1.5 text-xs text-gray-700 hover:text-gray-900 cursor-pointer"
                            >
                                Cancel
                            </button>

                            @if($mode === 'create')

                                <button
                                    wire:click="saveTask"
                                    class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-md hover:bg-green-700 data-loading:opacity-50 data-loading:pointer-events-none"
                                >
                                    Submit Task
                                </button>

                            @elseif($mode === 'edit')

                                <button
                                    wire:click="updateTask"
                                    class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-md hover:bg-green-700 data-loading:opacity-50 data-loading:pointer-events-none"
                                >
                                    update Task
                                </button>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

