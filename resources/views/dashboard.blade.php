<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="p-6 bg-white border-b border-gray-200 mt-4">
                My current latitude & longitude:
                <p id="currentLocation"></p>

                <canvas id="locationCanvas" class="bg-yellow-500"></canvas>

                <input type="range" id="scaleCanvas" class="w-full mt-4">
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Canvas --}}
        <script>
            $(document).ready(function() {
                fetchCoords(); // Used to fetch the coords first time around

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
                    const x = position.coords.latitude;
                    const y = position.coords.longitude;

                    const el = document.getElementById('currentLocation');
                    el.innerHTML = `Latitude: ${x} <br> Longitude: ${y}`;

                    pushCoordsToDatabase({
                        latitude: x,
                        longitude: y,
                    });
                }

                // Get the canvas element
                const c = document.getElementById("locationCanvas");

                // Set the canvas size
                c.width = 1170
                c.height = 600

                const canvas = c.getContext("2d");
                canvas.beginPath();

                async function setCanvasEntries() {
                    // Clear the canvas
                    canvas.clearRect(0, 0, c.width, c.height);

                    // Fetch users
                    const users = await fetchUsers();

                    users.forEach((user) => {
                        console.log(user)

                        const offsets = latLonToOffsets(user.latitude, user.longitude, c.width, c.height);

                        let posX = offsets.x
                        let posY = offsets.y

                        if (user.name === '{{ auth()->user()->name }}') {
                            posX = c.width / 2
                            posY = c.height / 2
                        }

                        canvas.beginPath()

                        // Draw circle
                        canvas.arc(posX, posY, 10, 0, 2 * Math.PI);
                        canvas.stroke();

                        // Draw name
                        canvas.fillText(user.name, posX, posY);
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
                        success: function() {
                            setCanvasEntries()
                        },
                        error: function(response) {
                            console.log(response)
                        }
                    });
                }

                // Helper methods
                function fetchUsers() {
                    return fetch('/fetch/users')
                        .then((response) => response.json())
                        .then(data => data)
                }

                /**
                 * @param {number} latitude in degrees
                 * @param {number} longitude in degrees
                 * @param {number} mapWidth in pixels
                 * @param {number} mapHeight in pixels
                 */
                function latLonToOffsets(latitude, longitude, mapWidth, mapHeight) {
                    const radius = mapWidth / (2 * Math.PI);
                    const FE = 180; // false easting

                    const lonRad = degreesToRadians(longitude + FE);
                    const x = lonRad * radius;

                    const latRad = degreesToRadians(latitude);
                    const verticalOffsetFromEquator =
                        radius * Math.log(Math.tan(Math.PI / 4 + latRad / 2));
                    const y = mapHeight / 2 - verticalOffsetFromEquator;

                    return { x, y };
                }

                /**
                 * @param {number} degrees
                 */
                function degreesToRadians(degrees) {
                    return (degrees * Math.PI) / 180;
                }

                // Websocket stuff
                const userAuth = Echo.channel('userAuthenticated');
                const userLogout = Echo.channel('userLogout');

                userAuth.subscribed(() => {
                    console.log("Listening for auth requests");
                }).listen('.user.authenticated', (event) => {
                    fetchCoords();
                })

                userLogout.subscribed(() => {
                    console.log("Listening for logout requests");
                }).listen('.user.logout', (event) => {
                    fetchCoords();
                })
            });
        </script>
    @endpush


</x-app-layout>