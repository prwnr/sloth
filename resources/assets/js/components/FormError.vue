<template>
    <span class="help-block text-danger p-1"
          v-html="text"
          v-show="show">
    </span>
</template>

<script>
    export default {
        props: ['text', 'show'],
    }
</script>