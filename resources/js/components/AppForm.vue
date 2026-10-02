<script setup lang="ts">
import { useTemplateRef } from 'vue';
import { useForm } from '@/composables/useForm';
import type { FormErrors } from '@/composables/useForm';
import type { HttpRoute } from '@/lib/http';

type FormValues = Record<string, FormDataEntryValue>;
type ResetOption = boolean | string[];
type FormControl = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;

const props = withDefaults(
    defineProps<{
        /** The Wayfinder route definition to submit to. */
        route: HttpRoute;
        /** Transform the serialised form values before they are sent. */
        transform?: (data: FormValues) => Record<string, unknown>;
        /** Reset all fields, or the named fields, after a successful submit. */
        resetOnSuccess?: ResetOption;
        /** Reset all fields, or the named fields, after a validation error. */
        resetOnError?: ResetOption;
    }>(),
    {
        transform: undefined,
        resetOnSuccess: false,
        resetOnError: false,
    },
);

const emit = defineEmits<{
    /** The JSON response body; narrow it with the helpers in `lib/responses`. */
    success: [data: unknown];
    error: [errors: FormErrors];
    finish: [];
}>();

const formRef = useTemplateRef<HTMLFormElement>('formRef');
const form = useForm();

const resetControl = (control: FormControl): void => {
    if (control instanceof HTMLSelectElement) {
        Array.from(control.options).forEach((option) => {
            option.selected = option.defaultSelected;
        });
    } else if (
        control instanceof HTMLInputElement &&
        (control.type === 'checkbox' || control.type === 'radio')
    ) {
        control.checked = control.defaultChecked;
    } else {
        control.value = control.defaultValue;
    }

    control.dispatchEvent(new Event('input', { bubbles: true }));
    control.dispatchEvent(new Event('change', { bubbles: true }));
};

const reset = (...fields: string[]): void => {
    const element = formRef.value;

    if (!element) {
        return;
    }

    const controls = Array.from(element.elements).filter(
        (control): control is FormControl =>
            (control instanceof HTMLInputElement ||
                control instanceof HTMLSelectElement ||
                control instanceof HTMLTextAreaElement) &&
            control.name !== '' &&
            (fields.length === 0 || fields.includes(control.name)),
    );

    controls.forEach(resetControl);
};

const applyReset = (option: ResetOption): void => {
    if (option === true) {
        reset();
    } else if (Array.isArray(option)) {
        reset(...option);
    }
};

const submit = async (): Promise<void> => {
    const element = formRef.value;

    if (!element) {
        return;
    }

    const values = Object.fromEntries(new FormData(element)) as FormValues;
    const data = props.transform ? props.transform(values) : values;

    try {
        await form.submit(props.route, data, {
            onSuccess: (result) => {
                applyReset(props.resetOnSuccess);
                emit('success', result);
            },
            onError: (errors) => {
                applyReset(props.resetOnError);
                emit('error', errors);
            },
            onFinish: () => emit('finish'),
        });
    } catch {
        // Non-validation failures are surfaced by the HTTP interceptors.
    }
};

defineExpose({ reset, clearErrors: form.clearErrors, submit });
</script>

<template>
    <form ref="formRef" @submit.prevent="submit">
        <slot
            :errors="form.errors.value"
            :processing="form.processing.value"
            :was-successful="form.wasSuccessful.value"
            :recently-successful="form.recentlySuccessful.value"
            :has-errors="form.hasErrors.value"
            :reset="reset"
            :clear-errors="form.clearErrors"
        />
    </form>
</template>
