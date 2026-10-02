import { defineStore } from 'pinia';
import { ref } from 'vue';

/**
 * One-shot status messages carried between pages, such as "password reset"
 * shown on the login screen after resetting a password.
 */
export const useFlashStore = defineStore('flash', () => {
    const status = ref<string | null>(null);

    function set(message: string | null): void {
        status.value = message;
    }

    function consume(): string | null {
        const message = status.value;

        status.value = null;

        return message;
    }

    return { status, set, consume };
});
