<template>
    <div class="btn-group btn-group-sm">
        <button class="btn btn-default"
                :class="{ '-nested-comp-open-btn': isDisplayRowVisible }"
                @click="toggleNestedComp('NestedRow')">
            <i class="fa fa-list-ul"></i> Details
        </button>
    </div>
</template>

<script>
    export default {
        props: ['row', 'nested'],
        mounted() {
            $(this.$el).find('button[title]').tooltip()
        },
        computed: {
            isDisplayRowVisible() {
                if (this.nested.comp !== 'NestedRow') return
                return this.nested.visible
            }
        },
        methods: {
            toggleNestedComp(comp) {
                const {nested} = this
                if (nested.comp === comp) return nested.$toggle()
                nested.$toggle(comp, true)
            }
        }
    }
</script>

<style>
    .-nested-comp-open-btn {
        color: #fff !important;
        background-color: #337ab7 !important;
    }
</style>