<divsi
    @scroll-bottom.window="$nextTick(() => {
        let conversationElement = document.getElementById('conversation');
        if (conversationElement) {
            conversationElement.scrollTop = conversationElement.scrollHeight;
        }
    })"
    class="w-full overflow-hidden"
>
    <div class="flex flex-col h-full overflow-y-scroll border-b grow">

        {{-- header --}}
        <header class="w-full sticky inset-x-0 flex pb-[5px] pt-[5px] top-0 z-10 bg-white border-b ">
            <div class="flex items-center w-full gap-2 px-2 lg:px-4 md:gap-5">

                <a href="shrink-0 hidden md:visible" href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" />
                    </svg>
                </a>

                {{-- avatar --}}
                <div class="shrink-0">
                    <a href="" class="shrink-0">
                        <x-avatar src="https://mighty.tools/mockmind-api/content/human/{{ $selectedConversation->getReceiver()->id}}.jpg" alt="image"/>
                    </a>
                </div>

                <h6 class="font-bold truncate"> {{ $selectedConversation->getReceiver()->name }} </h6>

            </div>
        </header>

        {{-- body --}}
        <main id="conversation" class="flex flex-col gap-3 p-2.5 overflow-y-auto  flex-grow overscroll-contain overflow-x-hidden w-full my-auto">

            @if($loadedMessages)
                @foreach($loadedMessages as $message)
                    <div
                        @class([
                            'max-w-[85%] md:max-w-[78%] flex w-auto gap-2 relative mt-2',
                            'ml-auto'=> $message->sender_id === auth()->id()
                        ])>

                        <div @class(['shrink-0'])>

                            <x-avatar />

                        </div>

                        {{-- message body --}}
                        <div @class(['flex flex-wrap text-[15px]  rounded-xl p-2.5 flex flex-col text-black bg-[#f6f6f8fb]',
                                        'rounded-bl-none border  border-gray-200/40 '=> !($message->sender_id === auth()->id()),
                                        'rounded-br-none bg-blue-500/80 text-white'=> $message->sender_id === auth()->id()
                        ])>

                            <p class="text-sm tracking-wide truncate whitespace-normal md:text-base lg:tracking-normal">
                                {{ $message->body }}
                            </p>

                            <div class="flex gap-2 ml-auto">

                                <p @class([
                                    'text-xs ',
                                    'text-gray-500'=>!($message->sender_id === auth()->id()),
                                    'text-white'=>$message->sender_id === auth()->id()
                                    ])>

                                    {{-- format time to am/pm 12hr --}}
                                    {{ $message->created_at->format('g:i a') }}

                                </p>

                                {{-- message status , only show if message belongs auth --}}
                                @if ($message->sender_id === auth()->id())
                                    <div>
                                        {{-- double ticks --}}

                                        @if ($message->isRead())
                                            <span @class('text-gray-200')>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                                    <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/>
                                                    <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/>
                                                </svg>
                                            </span>
                                        @else
                                            {{-- single ticks --}}
                                            <span @class('text-gray-200')>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                    <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                @endif

                            </div>

                        </div>
                    </div>
                @endforeach
            @endif

        </main>

        {{-- footer (send message) --}}
        <footer class="inset-x-0 z-10 bg-white shrink-0">

            <div class="p-2 border-t ">

                <form
                 wire:submit.prevent="sendMessage"
                 autocapitalize="off">

                    <input type="hidden" autocomplete="false" style="display:none">

                    <div class="grid grid-cols-12">
                         <input
                                wire:model.defer="body"
                                id="send{{ $iteration }}"
                                type="text"
                                autocomplete="off"
                                autofocus
                                placeholder="write your message here"
                                maxlength="1700"
                                class="col-span-10 bg-gray-100 border-0 rounded-lg outline-0 focus:border-0 focus:ring-0 hover:ring-0 focus:outline-none"
                         >

                         <button class="col-span-2" type='submit'>Send</button>

                    </div>

                </form>
            </div>





        </footer>

    </div>

</div>

