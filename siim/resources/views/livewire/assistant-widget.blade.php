<div
    class="fixed bottom-6 right-6 z-50"
    x-data="{
        scrollBottom() { this.$nextTick(() => { const m = this.$refs.messages; if (m) m.scrollTop = m.scrollHeight; }); }
    }"
    x-init="
        $watch('$wire.history', () => scrollBottom());
        $watch('$wire.isOpen', v => v && scrollBottom());
        $watch('$wire.waiting', () => scrollBottom());
    "
    @assistant:fetch-reply.window="setTimeout(() => $wire.fetchReply(), 80)"
>

    {{-- Floating Action Button --}}
    @if(! $isOpen)
        <button
            wire:click="toggle"
            type="button"
            class="group flex items-center gap-3 pl-3 pr-5 py-3 rounded-full bg-brand-canopy text-white shadow-brand-lg hover:bg-brand-canopy/95 hover:scale-105 transition focus:outline-none focus:ring-2 focus:ring-brand-gold"
            aria-label="Abrir asistente"
        >
            <span class="relative flex h-9 w-9 items-center justify-center rounded-full bg-white/15">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span class="absolute -top-0.5 -right-0.5 h-3 w-3 rounded-full bg-brand-gold ring-2 ring-brand-canopy"></span>
            </span>
            <span class="text-sm font-medium">Asistente SIIM</span>
        </button>
    @endif

    {{-- Chat Panel --}}
    @if($isOpen)
        <div class="w-[380px] max-w-[calc(100vw-3rem)] h-[560px] max-h-[calc(100vh-3rem)] bg-white rounded-2xl shadow-brand-lg border border-brand-canopy/10 flex flex-col overflow-hidden">

            {{-- Header --}}
            <header class="bg-brand-canopy text-white px-4 py-3 flex items-center gap-3">
                <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-white/15">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2z"/>
                        <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-serif font-semibold leading-tight">Asistente SIIM</p>
                    <p class="text-[11px] opacity-80 flex items-center gap-1.5">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        En línea · Llama 3.1 vía NVIDIA
                    </p>
                </div>
                <button wire:click="reset_chat" type="button" class="text-white/70 hover:text-white p-1.5 rounded-md hover:bg-white/10" title="Reiniciar conversación">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                    </svg>
                </button>
                <button wire:click="toggle" type="button" class="text-white/70 hover:text-white p-1.5 rounded-md hover:bg-white/10" title="Minimizar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                </button>
            </header>

            {{-- Messages --}}
            <div x-ref="messages" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-brand-mist/40">
                @foreach($history as $msg)
                    @if($msg['role'] === 'user')
                        <div class="flex justify-end">
                            <div class="max-w-[80%]">
                                <div class="bg-brand-canopy text-white rounded-2xl rounded-tr-sm px-4 py-2.5 text-sm leading-relaxed shadow-sm">
                                    {{ $msg['content'] }}
                                </div>
                                <p class="text-[10px] text-ink-soft mt-1 text-right pr-2">{{ $msg['at'] ?? '' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex justify-start gap-2">
                            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-brand-canopy text-white flex items-center justify-center font-serif font-bold text-xs">A</div>
                            <div class="max-w-[80%]">
                                <div class="bg-white text-ink-deep rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm leading-relaxed shadow-sm border border-brand-canopy/5 whitespace-pre-wrap">{{ $msg['content'] }}</div>
                                <p class="text-[10px] text-ink-soft mt-1 pl-2">{{ $msg['at'] ?? '' }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach

                @if($waiting)
                    <div class="flex justify-start gap-2">
                        <div class="flex-shrink-0 w-7 h-7 rounded-full bg-brand-canopy text-white flex items-center justify-center font-serif font-bold text-xs">A</div>
                        <div class="bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm border border-brand-canopy/5">
                            <div class="flex items-center gap-1">
                                <span class="w-2 h-2 bg-brand-canopy/60 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                <span class="w-2 h-2 bg-brand-canopy/60 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                <span class="w-2 h-2 bg-brand-canopy/60 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errorMessage)
                    <div class="flex justify-center">
                        <div class="bg-brand-clay/10 border border-brand-clay/20 text-brand-clay text-xs px-3 py-2 rounded-lg">{{ $errorMessage }}</div>
                    </div>
                @endif
            </div>

            {{-- Input --}}
            <form wire:submit="send" class="border-t border-brand-canopy/10 bg-white p-3 flex items-end gap-2">
                <textarea
                    wire:model="input"
                    @keydown.enter.prevent="if (! $event.shiftKey) $wire.send()"
                    placeholder="Pregúntame algo sobre SIIM..."
                    rows="1"
                    class="flex-1 resize-none rounded-xl border border-brand-canopy/10 focus:border-brand-canopy focus:ring-1 focus:ring-brand-canopy/20 px-3 py-2 text-sm bg-brand-mist/40 placeholder:text-ink-soft/70 max-h-32"
                    {{ $waiting ? 'disabled' : '' }}
                ></textarea>
                <button
                    type="submit"
                    class="flex-shrink-0 h-10 w-10 rounded-xl bg-brand-canopy text-white flex items-center justify-center hover:bg-brand-canopy/90 disabled:opacity-40 disabled:cursor-not-allowed shadow-brand"
                    {{ $waiting ? 'disabled' : '' }}
                    title="Enviar (Enter)"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </form>
        </div>
    @endif
</div>
