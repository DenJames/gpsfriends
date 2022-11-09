<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="p-6 bg-white border-b border-gray-200">
                <canvas id="locationCanvas" class="bg-yellow-500"></canvas>

                <input type="range" id="scaleCanvas" class="w-full mt-4">
            </div>

            <div class="p-6 bg-white border-b border-gray-200">
                <p id="demo"></p>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Canvas --}}
        <script>
            function randomIntFromInterval(min, max) { // min and max included
                return Math.floor(Math.random() * (max - min + 1) + min)
            }

            // Create the canvas element
            const c = document.getElementById("locationCanvas");

            c.width = 1170
            c.height = 600

            const canvas = c.getContext("2d");
            canvas.beginPath();



            // generateCanvasEntries(canvas);
            const rndX = randomIntFromInterval(30, c.width)
            const rndY = randomIntFromInterval(30, c.height)

            canvas.lineWidth+=1;

            function generateCanvasEntries(users) {
                console.log(users, "hello");
                canvas.clearRect(0, 0, c.width, c.height);

                users.forEach((user) => {
                    let posX = randomIntFromInterval(30, c.width)
                    let posY = randomIntFromInterval(30, c.height)

                    if (user.name === '{{ auth()->user()->name }}') {
                        posX = c.width / 2
                        posY = c.height / 2
                    }

                    canvas.beginPath()

                    canvas.arc(posX, posY, 10, 0, 2 * Math.PI);
                    canvas.stroke();
                    // Draw name
                    canvas.font = "11px Arial";
                    canvas.fillText(user.name, posX, posY);

                    canvas.lineWidth += 0;

                })

            }


            // const authUserX = c.width / 2;
            // const authUserY = c.height / 2;


        </script>


        <script>
            $(document).ready(function() {
                fetchCoords(); // Used to fetch the coords first time around

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
                    pushCoordsToDatabase({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    });
                }

                function showLocation(users) {
                    x.innerHTML = '';

                    generateCanvasEntries(users);

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