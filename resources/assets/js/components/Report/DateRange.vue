<template>
    <div class="mb-2">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            {{ range | capitalize }}
        </button>
        <ul class="dropdown-menu dropdown-menu-right" x-placement="top-start">
            <a href="#" class="dropdown-item text-right" v-for="(option, index) in options" :key="index"
               @click="changeRange(option)">{{ option | capitalize }}</a>
        </ul>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                range: 'week',
                options: [
                    'week', 'month', 'year'
                ]
            }
        },

        methods: {
            changeRange(range) {
                this.range = range;
                this.$emit('rangeChange', range);
            }
        },

        filters: {
            capitalize: function (value) {
                if (!value) {
                    return '';
                }
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1);
            }
        }
    }
</script>

<style scoped>
    .dropdown-menu {
        min-width: 7em !important;
    }
</style>