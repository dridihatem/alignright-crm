<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(auth()->user()->isAdmin())
                        <h2 class="text-2xl font-bold mb-4">Admin Dashboard</h2>
                        <p>Welcome to the admin dashboard. You have full access to all features.</p>
                    @elseif(auth()->user()->isDoctor())
                        <h2 class="text-2xl font-bold mb-4">Doctor Dashboard</h2>
                        <p>Welcome to the doctor dashboard. You can manage patient records and appointments.</p>
                    @elseif(auth()->user()->isTechnicien())
                        <h2 class="text-2xl font-bold mb-4">Technician Dashboard</h2>
                        <p>Welcome to the technician dashboard. You can manage equipment and maintenance.</p>
                    @elseif(auth()->user()->isLaboratoire())
                        <h2 class="text-2xl font-bold mb-4">Laboratory Dashboard</h2>
                        <p>Welcome to the laboratory dashboard. You can manage test results and samples.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
