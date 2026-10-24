<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useTrans } from '@/Composables/useTrans';

const { trans } = useTrans();

const isOpen = ref(false);
const messages = ref([
    { role: 'assistant', text: trans('chat_welcome') }
]);
const newMessage = ref('');
const messageContainer = ref(null);

const isTyping = ref(false);

const sendMessage = async () => {
    if (!newMessage.value.trim() || isTyping.value) return;

    const userMessage = newMessage.value;
    messages.value.push({ role: 'user', text: userMessage });
    newMessage.value = '';
    scrollToBottom();

    isTyping.value = true;

    try {
        const response = await axios.post(route('ai.chat'), {
            messages: messages.value.map(m => ({ 
                role: m.role, 
                content: m.text 
            }))
        });

        const aiResponse = response.data.choices[0].message.content;

        messages.value.push({ role: 'assistant', text: aiResponse });
    } catch (error) {
        messages.value.push({ 
            role: 'assistant', 
            text: trans('chat_error') 
        });
    } finally {
        isTyping.value = false;
        scrollToBottom();
    }
};

const scrollToBottom = async () => {
    await nextTick();
    if (messageContainer.value) {
        messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
};

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        scrollToBottom();
    }
};
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 font-sans">
        <!-- Chat Bubble -->
        <button 
            @click="toggleChat"
            class="w-16 h-16 bg-indigo-600 text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-300 group"
        >
            <svg v-if="!isOpen" class="w-8 h-8 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <div v-if="!isOpen" class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white animate-pulse"></div>
        </button>

        <!-- Chat Window -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform scale-95 opacity-0 translate-y-10"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 translate-y-10"
        >
            <div v-if="isOpen" class="absolute bottom-20 right-0 w-[400px] max-w-[90vw] h-[600px] max-h-[80vh] bg-white rounded-[2rem] shadow-2xl border border-slate-100 overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="bg-indigo-600 p-6 text-white text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[radial-gradient(circle_at_center,_white_1px,_transparent_1px)] bg-[size:10px_10px]"></div>
                    <div class="relative z-10">
                        <h3 class="font-black text-lg tracking-tight">RAGHD TMS Assistant</h3>
                        <p class="text-indigo-100 text-xs font-medium">{{ trans('chat_status') }}</p>
                    </div>
                </div>

                <!-- Messages -->
                <div ref="messageContainer" class="flex-1 p-6 overflow-y-auto space-y-4 bg-slate-50/50 overscroll-contain">
                    <div v-for="(msg, idx) in messages" :key="idx" 
                        :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']"
                    >
                        <div :class="[
                            'max-w-[80%] p-3 px-4 rounded-2xl text-sm font-medium shadow-sm whitespace-pre-wrap',
                            msg.role === 'user' 
                                ? 'bg-indigo-600 text-white rounded-tr-none' 
                                : 'bg-white text-slate-800 rounded-tl-none border border-slate-100'
                        ]">
                            {{ msg.text }}
                        </div>
                    </div>
                    
                    <!-- Typing Indicator -->
                    <div v-if="isTyping" class="flex justify-start">
                        <div class="bg-white border border-slate-100 p-3 px-4 rounded-2xl rounded-tl-none shadow-sm flex space-x-1 items-center">
                            <div class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce"></div>
                            <div class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                            <div class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="p-4 bg-white border-t border-slate-100">
                    <form @submit.prevent="sendMessage" class="flex items-center space-x-2">
                        <input 
                            v-model="newMessage"
                            type="text" 
                            :placeholder="trans('chat_placeholder')"
                            class="flex-1 bg-slate-100 border-none rounded-xl text-sm p-3 focus:ring-2 focus:ring-indigo-500 transition-all"
                        />
                        <button 
                            type="submit"
                            class="bg-indigo-600 text-white p-3 rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/20"
                        >
                            <svg class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
