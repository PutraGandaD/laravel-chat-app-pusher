<div
    x-data="{
        height: 0, // Declare height variable
        container: $refs.conversation, // Declare container variable,
        markAsRead: null
    }"
    x-init="
        // Set height to the scrollHeight of the container
        height = container.scrollHeight;

        // Scroll to the bottom on initialization
        $nextTick(() => {
            container.scrollTop = height;
        });

        // handle read status from broadcast
        {{-- Echo.private('users.{{Auth()->User()->id}}')
        .notification((notification)=>{
            if(notification['type']== 'App\\Notifications\\MessageRead' && notification['conversation_id']== {{$this->selectedConversation->id}})
            {
                markAsRead=true;
            }
        }); --}}
    "
    @scroll-bottom.window="
        // Update height to the new scrollHeight
        height = container.scrollHeight;

        // Scroll to the bottom when the event is triggered
        $nextTick(() => {
            container.scrollTop = height;
        });
    "
    class="w-full overflow-hidden"
>
    <div class="flex flex-col h-full overflow-y-scroll border-b grow">

        {{-- Header --}}
        <header class="w-full sticky inset-x-0 flex pb-[5px] pt-[5px] top-0 z-10 bg-white border-b">
            <div class="flex items-center w-full gap-2 px-2 lg:px-4 md:gap-5">
                <a class="shrink-0 lg:hidden" href="{{ route('chat.index') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" />
                    </svg>
                </a>

                {{-- Avatar --}}
                <div class="shrink-0">
                    <a href="" class="shrink-0">
                        <x-avatar src="https://mighty.tools/mockmind-api/content/human/{{ $selectedConversation->getReceiver()->id }}.jpg" alt="image"/>
                    </a>
                </div>

                <h6 class="font-bold truncate"> {{ $selectedConversation->getReceiver()->name }} </h6>
            </div>
        </header>

        {{-- Body --}}
        <main
        @scroll="
            scropTop = $el.scrollTop;
            if(scropTop <= 0){
                window.livewire.emit('loadMoreMessages');

            }
        "

        @update-chat-height.window="
            newHeight= $el.scrollHeight;
            oldHeight= height;

            $el.scrollTop= newHeight- oldHeight;

            height=newHeight;
        "
        id="conversation" x-ref="conversation" class="flex flex-col gap-3 p-2.5 overflow-y-auto flex-grow overscroll-contain overflow-x-hidden w-full my-auto">
            @if($loadedMessages)
                @php
                    $previousMessage = null;
                @endphp

                @foreach($loadedMessages as $key => $message)
                    @if ($key > 0)
                        @php
                            $previousMessage = $loadedMessages->get($key - 1);
                        @endphp
                    @endif

                    <div
                    wire:key="{{ time().$key }}"
                    @class([
                        'max-w-[85%] md:max-w-[78%] flex w-auto gap-2 relative mt-2',
                        'ml-auto' => $message->sender_id === auth()->id()
                    ])>
                        <div @class([
                            'shrink-0',
                            'invisible' => $previousMessage?->sender_id == $message->sender_id,
                            'hidden' => $message->sender_id === auth()->id()
                        ])>
                            <x-avatar />
                        </div>

                        {{-- Message Body --}}
                        <div @class([
                            'flex flex-wrap text-[15px] rounded-xl p-2.5 flex flex-col text-black bg-[#f6f6f8fb]',
                            'rounded-bl-none border border-gray-200/40' => !($message->sender_id === auth()->id()),
                            'rounded-br-none bg-blue-500/80 text-white' => $message->sender_id === auth()->id()
                        ])>
                            <p class="text-sm tracking-wide truncate whitespace-normal md:text-base lg:tracking-normal">
                                {{ $message->body }}
                            </p>

                            <div class="flex gap-2 ml-auto">
                                <p @class([
                                    'text-xs',
                                    'text-gray-500' => !($message->sender_id === auth()->id()),
                                    'text-white' => $message->sender_id === auth()->id()
                                ])>
                                    {{ $message->created_at->format('g:i a') }}
                                </p>

                                {{-- Message Status --}}
                                @if ($message->sender_id === auth()->id())
                                    <div>
                                        {{-- Double tick (Always Visible) --}}
                                        <span class="text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                                <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/>
                                                <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/>
                                            </svg>
                                        </span>
                                    </div>
                                @endif


                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </main>

        {{-- Footer (Send Message) --}}
        <footer class="inset-x-0 z-10 bg-white shrink-0">
            <div class="p-2 border-t">
                <form wire:submit.prevent="sendMessage" autocapitalize="off">
                    <input type="hidden" autocomplete="false" style="display:none">
                    <div class="grid grid-cols-12">
                        <input
                            wire:model.defer="body"
                            type="text"
                            autocomplete="off"
                            autofocus
                            placeholder="Write your message here"
                            maxlength="1700"
                            class="col-span-10 bg-gray-100 border-0 rounded-lg outline-0 focus:border-0 focus:ring-0 hover:ring-0 focus:outline-none"
                        >
                        <button class="col-span-2" type="submit">Send</button>
                    </div>
                </form>
            </div>
        </footer>
    </div>
</div>
