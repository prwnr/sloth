<template>
    <span class="help-block text-danger p-1"
          v-html="text"
          v-show="show">
    </span>
</template>

<script>
    export default {
        name: 'FormError',
        props: {
            text: {
                type: String,
                required: false,
            },
            show: {
                type: Boolean,
                required: false,
                default: false
            }
        },
    }
</script>