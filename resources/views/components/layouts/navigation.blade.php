<x-nav-link :href="route('workout.planner')" :active="request()->routeIs('workout.planner')">
    {{ __('Workout Planner') }}
</x-nav-link>

<x-nav-link :href="route('exercises')" :active="request()->routeIs('exercises')">
    {{ __('Exercise Library') }}
</x-nav-link> 