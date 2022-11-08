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

    @push('scripts')
        <script>
            $(document).ready(function() {
                const x = document.getElementById("demo");

                setInterval(() => {
                    fetchCoords();
                }, 5000)

                function fetchCoords() {
                    if (!navigator.geolocation) {
                        x.innerHTML = "Geolocation is not supported by this browser.";

                        return;
                    }

                    navigator.geolocation.getCurrentPosition(fetchPosition);
                }

                function fetchPosition(position) {
                    const coords = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    }

                    pushCoordsToDatabase(coords);

                    showLocation(coords);

                    return coords;
                }

                function showLocation(users) {
                    x.innerHTML = '';

                    users.forEach((user) => {
                        if (user.name === '{{ auth()->user()->name }}') {
                            user.thisIsMe = true;
                            x.innerHTML += `Latitude: ${user.latitude} <br> Longitude: ${user.longitude} <br><br>`
                        }

                        setTimeout(() => {
                            x.innerHTML += JSON.stringify(user) + "<br>"
                        }, 100);
                    });
                }

                function pushCoordsToDatabase(coords) {
                    $.ajax({
                        url: '{{ route('location.store') }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: coords,
                        success: function(response) {
                            showLocation(Object.values(response));
                        },
                        error: function(response) {
                            console.log(response)
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>