<div
    x-data="{ toasts: [] }"
    @toast.window="
        const id = Date.now()
        toasts.push({
            id: id,
            show: true,
            message: $event.detail.message,
            type: $event.detail.type
        })
        setTimeout(() => {

            const index = toasts.findIndex(t => t.id == id)

            if(index != -1){

                toasts[index].show = false

                setTimeout( () => {

                    toasts = toasts.filter(t => t.id !== id)

                }, 500)

            }
        }, 6000)
    "
    class="fixed top-5 right-5 z-50 fldex flex-col-reverse space-y-3"
    >

    <template x-for="toast in toasts" :key="toast.id">

        <div
            class="w-80 rounded-lg p-4"
            :class="{
                'bg-green-50 border border-green-200 text-green-900' : toast.type === 'success',
                'bg-red-50 border border-red-200 text-red-900' : toast.type === 'error',
                'bg-blue-50 border border-blue-200 text-blue-900' : toast.type === 'info',
                'bg-yellow-50 border border-yellow-200 text-yellow-900' : toast.type === 'warning',
                'animate-fade-in-down' : toast.show,
                'animate-fade-out-up' : !toast.show
            }">

            <p class="text-sm font-medium" x-text="toast.message"></p>

        </div>

    </template>

</div>
