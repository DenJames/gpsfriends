<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p id="demo"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        $.post('/dashboard',
            {
                '_token': $('meta[name=csrf-token]').attr('content'),
            })
            .error(() => {
                console.log('error')
            })
            .success(() => {
                console.log('success')
            });
        }

        document.addEventListener("DOMContentLoaded", function (event) {
            const x = document.getElementById("demo");

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                x.innerHTML = "Geolocation is not supported by this browser.";
            }

            function showPosition(position) {
                x.innerHTML = "Latitude: " + position.coords.latitude +
                    "<br>Longitude: " + position.coords.longitude;
            }
        });
    </script>
</x-app-layout>
