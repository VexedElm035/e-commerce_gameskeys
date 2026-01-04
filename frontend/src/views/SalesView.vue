<template>
    <section class="bg-gray-900 text-white flex flex-col min-h-screen">
        <section class="bg-red-600 text-white text-center py-12">
            <div class="container mx-auto">
                <h2 class="text-4xl font-bold mb-4 animate-pulse">OFERTAS RELÁMPAGO</h2>
                <p class="text-xl">¡Descuentos exclusivos por tiempo limitado!</p>
                <div class="mt-4 flex justify-center gap-2">
                    <span class="bg-black bg-opacity-50 px-3 py-1 rounded-lg">Termina en: 12:34:56</span>
                </div>
            </div>
        </section>

        <section class="container mx-auto p-6">
            <input type="text" placeholder="Buscar en ofertas..." class="w-full p-2 rounded-lg text-gray-900">
        </section>

        <section class="container mx-auto px-6 pb-2">
            <div class="flex flex-wrap gap-2">
                <button class="bg-yellow-500 text-gray-900 px-3 py-1 rounded-lg hover:bg-yellow-400">Todos</button>
                <button class="bg-gray-700 px-3 py-1 rounded-lg hover:bg-gray-600">-50% o más</button>
                <button class="bg-gray-700 px-3 py-1 rounded-lg hover:bg-gray-600">-30% a -49%</button>
                <button class="bg-gray-700 px-3 py-1 rounded-lg hover:bg-gray-600">Últimas 24h</button>
                <button class="bg-gray-700 px-3 py-1 rounded-lg hover:bg-gray-600">Próximas a terminar</button>
            </div>
        </section>

        <section class="container mx-auto p-6 flex-grow">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-gray-800 p-4 rounded-lg relative">
                    <div class="absolute -top-2 -right-2 bg-red-600 text-white px-3 py-1 rounded-lg font-bold text-sm">
                        -60%
                    </div>
                    <img src="https://i0.wp.com/gamepidia.com/wp-content/uploads/2024/02/ezgif-5-2b496b81f9.jpg?fit=500%2C750&ssl=1"
                        alt="Cyberpunk 2077" class="w-full h-48 object-cover rounded-lg mb-3">
                    <h3 class="text-xl font-bold">Cyberpunk 2077</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-yellow-400 text-lg">$320.00</span>
                        <span class="text-gray-400 line-through text-sm">$800.00</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button
                            class="flex-1 bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400">Añadir
                            al Carrito</button>
                        <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">Detalles</button>
                    </div>
                </div>

                <div class="bg-gray-800 p-4 rounded-lg relative">
                    <div class="absolute -top-2 -right-2 bg-red-600 text-white px-3 py-1 rounded-lg font-bold text-sm">
                        -45%
                    </div>
                    <img src="https://storage.googleapis.com/pod_public/750/216712.jpg" alt="Elden Ring"
                        class="w-full h-48 object-cover rounded-lg mb-3">
                    <h3 class="text-xl font-bold">Elden Ring</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-yellow-400 text-lg">$550.00</span>
                        <span class="text-gray-400 line-through text-sm">$1000.00</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button
                            class="flex-1 bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400">Añadir
                            al Carrito</button>
                        <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">Detalles</button>
                    </div>
                </div>

                <div class="bg-gray-800 p-4 rounded-lg relative">
                    <div class="absolute -top-2 -right-2 bg-red-600 text-white px-3 py-1 rounded-lg font-bold text-sm">
                        -70%
                    </div>
                    <img src="https://cdn.dekudeals.com/images/4a371469bfb7c32f1bc8a62779dd17f8063f281f/w500.jpg"
                        alt="The Witcher 3" class="w-full h-48 object-cover rounded-lg mb-3">
                    <h3 class="text-xl font-bold">The Witcher 3</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-yellow-400 text-lg">$150.00</span>
                        <span class="text-gray-400 line-through text-sm">$500.00</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button
                            class="flex-1 bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg hover:bg-yellow-400">Añadir
                            al Carrito</button>
                        <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-400">Detalles</button>
                    </div>
                </div>
            </div>
        </section>
    </section>
</template>