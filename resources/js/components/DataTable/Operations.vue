<template>
    <div class="btn-group">
        <button class="btn btn-sm"
                :class="[ isDisplayRowVisible ? 'btn-info' : 'btn-default' ]"
                @click="toggleNestedComp()">
            <i class="fa fa-list-ul"></i> Details
        </button>
    </div>
</template>

<script>
    export default {
        name: 'TableOperations',
        props: {
            row: {
                type: Object,
                required: true
            },
            nested: {
                type: Object,
                required: true,
            }
        },

        data() {
            return {
                nestedComponent: 'NestedRow'
            }
        },

        computed: {
            isDisplayRowVisible() {
                if (this.nested.comp !== this.nestedComponent) {
                    return;
                }

                return this.nested.visible;
            }
        },

        methods: {
            toggleNestedComp() {
                const {nested} = this;
                if (nested.comp === this.nestedComponent) {
                    return nested.$toggle();
                }

                nested.$toggle(this.nestedComponent, true);
            }
        }
    }
</script>

<style>

</style>