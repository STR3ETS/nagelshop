@extends('layouts.pages')
@section('content')
<div class="w-full h-auto relative">
    <div class="max-w-[1100px] mx-auto py-[5rem] grid grid-cols-4 gap-[1rem]">
        @foreach($producten as $product)
            <div class="bg-white p-[1rem] rounded-lg">
                <div class="w-full aspect-square rounded overflow-hidden">
                    <img src="{{ asset('storage/producten/' . $product->foto) }}" alt="{{ $product->naam }}" class="w-full h-full object-cover">
                </div>
                <div class="w-full flex flex-col gap-[0.5rem]">
                    <h2 class="text-[18px] font-medium mt-4">{{ $product->naam }}</h2>
                    <p class="text-[#191919] opacity-80 text-[15px] mb-4">{{ $product->beschrijving }}</p>
                    <p class="text-[#191919] opacity-80 text-[15px]">{{ number_format($product->prijs, 2, ',', '.') }}</p>
                    <form action="{{ route('winkelwagen.toevoegen', $product) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-[0.4rem] bg-[#ff64ba] hover:bg-[#96366c] transition rounded-md text-white text-[15px] font-medium flex items-center justify-center">
                            <lord-icon
                                src="https://cdn.lordicon.com/pbrgppbb.json"
                                trigger="hover"
                                colors="primary:#ffffff"
                                style="width:20px;height:20px">
                            </lord-icon>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    @if(session('toegevoegd'))
        @php $cart = session('cart', []); @endphp
        <div id="overlay" class="fixed inset-0 z-50 flex items-center justify-center bg-[#00000050]">
            <div class="bg-white p-8 rounded-lg max-w-[500px] w-full">
                <h2 class="text-[#191919] text-[28px] font-semibold leading-[1.15]"><span class="text-green-500">Succesvol</span> toegevoegd<br>aan <i class="instrument-serif-font text-[#ff64ba]">jouw winkelwagen</i></h2>
                <hr class="border-[#eeeeee] my-4">
                <div class="mb-8">
                    <h2 class="text-[#191919] text-[18px] font-semibold leading-[1.15] mb-4">Jouw <i class="instrument-serif-font text-[#ff64ba]">winkelwagen</i></h2>
                    <ul class="space-y-2">
                        @foreach($cart as $item)
                            <li class="flex justify-between items-center">
                                <span>{{ $item['naam'] }} × {{ $item['aantal'] }}</span>
                                <span class="font-medium">&euro;{{ number_format($item['prijs'] * $item['aantal'], 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="flex justify-between gap-4">
                    <a href="/producten" class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-center rounded text-sm">Verder winkelen</a>
                    <a href="{{ route('winkelwagen.index') }}" class="flex-1 py-2 bg-[#ff64ba] hover:bg-[#e652a7] text-white text-center rounded text-sm">Afrekenen</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection