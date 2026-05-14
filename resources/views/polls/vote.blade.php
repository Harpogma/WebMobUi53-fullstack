<x-vue-app-layout>
    <x-slot:scripts>
        @vite(['resources/js/poll-vote.js'])
    </x-slot>

    <x-slot:title>
        Sondage
    </x-slot>

    @php
        $appProps = ['token' => $token, 'loginUrl' => route('login'), 'authUserId' => $authUserId];
    @endphp
    <div id="app" data-props='@json($appProps)'></div>
</x-vue-app-layout>
