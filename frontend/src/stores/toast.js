import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
    const show = ref(false);
    const message = ref('');
    const type = ref('info');
    let timeout = null;

    const trigger = (msg, msgType = 'info', duration = 3000) => {
        message.value = msg;
        type.value = msgType;
        show.value = true;

        if (timeout) clearTimeout(timeout);

        timeout = setTimeout(() => {
            show.value = false;
        }, duration);
    };

    const close = () => {
        show.value = false;
    };

    return { show, message, type, trigger, close };
});
