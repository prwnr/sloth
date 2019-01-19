<template>
    <small title="Task priority"
           class="badge"
           :class="badge">
        <slot></slot>
    </small>
</template>

<script>
    export default {
        name: "PriorityBadge",

        props: {
            priority: {
                type: String|Number,
                required: true
            }
        },

        data() {
            return {
                badges: {
                    1: 'badge-danger',
                    2: 'badge-warning',
                    3: 'badge-secondary'
                }
            }
        },

        computed: {
            badge() {
                return this.badges[this.priority];
            }
        }
    }
</script>

<style scoped>

</style>