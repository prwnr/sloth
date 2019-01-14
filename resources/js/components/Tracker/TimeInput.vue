<template>
    <input type="text" class="form-control flat text-right" name="time"
           v-model="content"
           @input="update"
           placeholder="00:00"
    />
</template>

<script>
    export default {
        props: ['value'],

        data() {
            return {
                content: this.value ? this.value : ''
            }
        },

        watch: {
            value: function () {
                this.content = this.value ? this.value : ''
            }
        },

        methods: {
            update(e) {
                if ((this.content.match(/:/g) || []).length >= 2) {
                    this.content = this.content.substring(0, this.content.length - 1);
                }

                let input = this.content.split(':');
                input[0] = input[0].replace(/[^0-9]/g, '');
                if (input.length == 2) {
                    input[1] = input[1].replace(/[^0-9]/g, '');
                }
                this.content = input.join(':');
                this.$emit('input', this.content);
            },
        }
    }
</script>

<style scoped>

</style>