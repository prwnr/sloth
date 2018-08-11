<template>
    <input type="text" class="form-control flat text-right" name="time"
           v-bind:value="value"
           v-on:input="$emit('input', $event.target.value)"
           v-mask="'##:##'"
           placeholder="00:00"
           @keyup="correctTime"/>
</template>

<script>
    export default {
        props: ['value'],

        methods: {
            /**
             * Makes sure that MM in HH:MM won't go over 60 minutes
             */
            correctTime() {
                let time = this.value.split(':');
                if (time.length != 2) {
                    return;
                }

                if (time[1] > 60) {
                    this.value = time[0] + ':' + 60;
                    return;
                }
            },
        }
    }
</script>

<style scoped>

</style>