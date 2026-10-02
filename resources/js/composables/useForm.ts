import { AxiosError } from 'axios';
import { computed, ref } from 'vue';
import type { ComputedRef, Ref } from 'vue';
import { request } from '@/lib/http';
import type { HttpRoute } from '@/lib/http';

export type FormErrors = Record<string, string>;

export type SubmitOptions<T> = {
    onSuccess?: (data: T) => void | Promise<void>;
    onError?: (errors: FormErrors) => void;
    onFinish?: () => void;
};

export type UseFormReturn = {
    errors: Ref<FormErrors>;
    processing: Ref<boolean>;
    wasSuccessful: Ref<boolean>;
    recentlySuccessful: Ref<boolean>;
    hasErrors: ComputedRef<boolean>;
    submit: <T = unknown>(
        route: HttpRoute,
        data?: unknown,
        options?: SubmitOptions<T>,
    ) => Promise<T | undefined>;
    clearErrors: (...fields: string[]) => void;
    setError: (field: string, message: string) => void;
};

const isRecord = (value: unknown): value is Record<string, unknown> =>
    typeof value === 'object' && value !== null;

/**
 * Map Laravel's `{ errors: { field: [messages] } }` payload to one message
 * per field. Returns null when the error is not a validation failure.
 */
export function extractValidationErrors(error: unknown): FormErrors | null {
    if (!(error instanceof AxiosError) || !isRecord(error.response?.data)) {
        return null;
    }

    const { errors } = error.response.data;

    if (!isRecord(errors)) {
        return null;
    }

    return Object.fromEntries(
        Object.entries(errors).map(([field, messages]) => [
            field,
            Array.isArray(messages)
                ? String(messages[0] ?? '')
                : String(messages),
        ]),
    );
}

/**
 * Submission state for a form posting JSON to the backend: validation
 * errors, processing flag, and the transient "recently successful" flag.
 */
export function useForm(): UseFormReturn {
    const errors = ref<FormErrors>({});
    const processing = ref(false);
    const wasSuccessful = ref(false);
    const recentlySuccessful = ref(false);
    const hasErrors = computed(() => Object.keys(errors.value).length > 0);

    let successTimer: ReturnType<typeof setTimeout> | undefined;

    function clearErrors(...fields: string[]): void {
        if (fields.length === 0) {
            errors.value = {};

            return;
        }

        const next = { ...errors.value };

        fields.forEach((field) => delete next[field]);

        errors.value = next;
    }

    function setError(field: string, message: string): void {
        errors.value = { ...errors.value, [field]: message };
    }

    async function submit<T = unknown>(
        route: HttpRoute,
        data?: unknown,
        options: SubmitOptions<T> = {},
    ): Promise<T | undefined> {
        processing.value = true;
        wasSuccessful.value = false;
        recentlySuccessful.value = false;
        clearTimeout(successTimer);

        try {
            const response = await request<T>(route, data);

            errors.value = {};
            wasSuccessful.value = true;
            recentlySuccessful.value = true;
            successTimer = setTimeout(() => {
                recentlySuccessful.value = false;
            }, 2000);

            await options.onSuccess?.(response.data);

            return response.data;
        } catch (error) {
            const validationErrors = extractValidationErrors(error);

            if (!validationErrors) {
                throw error;
            }

            errors.value = validationErrors;
            options.onError?.(validationErrors);

            return undefined;
        } finally {
            processing.value = false;
            options.onFinish?.();
        }
    }

    return {
        errors,
        processing,
        wasSuccessful,
        recentlySuccessful,
        hasErrors,
        submit,
        clearErrors,
        setError,
    };
}
