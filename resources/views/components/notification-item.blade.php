@props(['notification'])

<div
    x-data="{ read: @js((bool) $notification->read_at) }"
    {{ $attributes->merge(['class' => 'flex gap-3 px-4 py-3 hover:bg-blue-100 cursor-pointer transition-colors']) }}
    @click="if (!read) { read = true; $wire.markAsRead({{ $notification->id }}) }"
    @mark-all-read.window="read = true"
    :class="!read && 'bg-blue-50'"
>

    <img
        src="{{ $notification->actor_avatar }}"
        alt="{{ $notification->actor_name }}"
        class="w-10 h-10 rounded-full shrink-0"
    >

    <div class="flex-1 min-w-0">

        <div class="flex items-start justify-between gap-2">

            <p class="text-sm text-gray-900">
                <span class="font-medium">{{ $notification->actor_name }}</span>
                <span class="text-gray-600">{{ $notification->message }}</span>
            </p>

            <span class="shrink-0">

                @switch($notification->type)
                    @case('comment')
                        <x-icon.comment class="text-blue-500" />
                        @break
                    @case('like')
                        <x-icon.heart class="text-red-500" />
                        @break
                    @case('follow')
                        <x-icon.user-plus class="text-green-500" />
                        @break
                    @case('mention')
                        <x-icon.at-sign class="text-purple-500" />
                        @break
                @endswitch

            </span>

        </div>

    </div>

    <span x-show="!read" class="w-2 h-2 bg-blue-500 rounded-full shrink-0 mt-2"></span>

</div>
