<template>
    <div class="status-bar" :class="statusClass">
        {{ statusText }}
    </div>
</template>

<script>
    export default {
        props: {
            row: {
                type: Object,
                required: true
            }
        },
        
        computed: {
            statusText: function () {
                return this.row.in_progress ? 'In progress' : 'Finished'
            },

            statusClass: function () {
                return this.row.in_progress ? 'in-progress' : 'finished'
            }
        }
    }
</script>

<style scoped>
    .status-bar {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }

    .in-progress {
        color: #212529;
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .finished {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }
</style>